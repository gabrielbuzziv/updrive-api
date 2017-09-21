<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Filterable;
use App\Http\Controllers\Traits\Sortable;
use App\Http\Controllers\Traits\Transformable;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Notifications\CollaboratorInviteNotification;
use App\Permission;
use App\UPCont\Transformer\UserTransformer;
use App\User;
use App\UserRegistration;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Log;

class UserController extends ApiController
{

    use Transformable;

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        $this->middleware('permission:manage-users', ['except' => [
            'validateInvite', 'register',
        ]]);
    }

    /**
     * Show all regular users based in the request.
     *
     * @return mixed
     */
    public function index()
    {
        $limit = request('limit') ?: 25;
        $users = User::regular()
            ->search(request('filter'), null, true, true)
            ->orderBy('name')
            ->paginate($limit);

        return $this->respond([
            'total' => $users->total(),
            'items' => $this->transformCollection($users, new UserTransformer(), ['permissions']),
        ]);
    }

    /**
     * @param UserRequest $request
     */
    public function add(UserRequest $request)
    {
        foreach ($request->input('email') as $email) {
            $attributes = ['email' => $email, 'password' => str_random(8), 'is_active' => false, 'is_user' => true];
            $user = User::where(['email' => $email])->withTrashed()->first() ?: User::create($attributes);

            if (! $user->wasRecentlyCreated) {
                $user->is_user = true;
                $user->save();
            }

            if ($user->trashed()) {
                $user->restore();
            }

            UserRegistration::create(['email' => $user->email, 'token' => md5($user->email)]);

            $permissions = Permission::whereNotIn('name', ['manage-users'])->pluck('id')->all();
            $user->perms()->sync($permissions);
            $user->notify(new CollaboratorInviteNotification($user));
        }
    }

    /**
     * Walk in an array of ids and try to instanciate each one as a User,
     * if exists delete from database.
     *
     * @param Request $request
     * @return mixed
     */
    public function destroy(Request $request)
    {
        $items = $request->input('items');

        try {
            $deleted = 0;

            foreach ($items as $item) {
                if ($user = User::find($item)) {
                    if (! $user->can('manage-users')) {
                        if ($user->is_contact) {
                            $user->is_user = false;
                            $user->perms()->sync([]);
                            $user->save();
                        } else {
                            $user->delete();
                        }

                        $deleted ++;
                    }
                }
            }

            return $this->respond(['total' => $deleted]);
        } catch (\Exception $e) {
            Log::error(logMessage($e, 'Ocorreu um erro ao remover colaborador.'), logUser());

            return $this->respondInternalError($e);
        }
    }

    /**
     * Register user.
     *
     * @param User $user
     * @param RegisterUserRequest $request
     * @return mixed
     */
    public function register(User $user, RegisterUserRequest $request)
    {
        $user->name = $request->get('name');
        $user->password = $request->get('password');
        $user->is_active = true;
        $user->save();

        $registration = (new UserRegistration())
            ->where('email', $user->email)
            ->where('token', md5($user->email))
            ->first();
        $registration->delete();

        return $this->respond($this->transformItem($user, new UserTransformer()));
    }
}
