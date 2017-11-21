<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Filterable;
use App\Http\Controllers\Traits\Sortable;
use App\Http\Controllers\Traits\Transformable;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UserPasswordRequest;
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
        $this->middleware('permission:manage-core', ['except' => ['register', 'updateProfile', 'updatePassword']]);
        $this->middleware('permission:manage-users', ['except' => ['index', 'register', 'updateProfile', 'updatePassword']]);
    }

    /**
     * Show all regular users based in the request.
     *
     * @return mixed
     */
    public function index()
    {
        $users = User::regular()
            ->search(request('filter'))
            ->orderBy('id', 'desc')
            ->get();

        return $this->respond([
            'items' => $this->transformCollection($users, new UserTransformer(), ['permissions']),
        ]);
    }

    /**
     * Add user as pending and sent invitation mail to register his data.
     *
     * @param UserRequest $request
     */
    public function add(UserRequest $request)
    {
        foreach ($request->input('email') as $email) {
            $attributes = ['name' => $email, 'email' => $email, 'password' => str_random(8), 'is_active' => false, 'is_user' => true];
            $user = User::where(['email' => $email])->withTrashed()->first() ?: User::create($attributes);

            $this->setAsUser($user);
            $this->restoreTrashed($user);
            $this->createRegistrationToken($user);
            $this->addDefaultUserPermissions($user);

            $user->notify(new CollaboratorInviteNotification($user));
        }
    }

    /**
     * Walk in an array of ids and try to instanciate each one as a User,
     * if exists delete from database.
     *
     * @param User $user
     * @return mixed
     */
    public function revoke(User $user)
    {
        try {
            if ($user->can('manage-users') || $user->can('manage-account')) {
                return $this->respondBadRequest(null);
            }

            if ($registration = UserRegistration::where('email', $user->email)->where('token', md5($user->email))->first()) {
                $registration->delete();
            }

            if ($user->is_contact) {
                $user->is_user = false;
                $user->save();

                $user->perms()->sync([]);
            } else {
                $user->delete();
            }

            return $this->respond(['revoked' => true]);
        } catch (Exception $e) {
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

        $user->notificationsSettings()->create(['notification' => 'document_opened']);
        $user->notificationsSettings()->create(['notification' => 'document_expired']);

        return $this->respond($this->transformItem($user, new UserTransformer()));
    }

    /**
     * Toggle user permission.
     *
     * @param User $user
     * @return mixed
     */
    public function togglePermission(User $user)
    {
        if (request('permission')) {
            if (auth()->user()->id == $user->id) {
                return $this->respondBadRequest(null, 'Você não pode gerenciar suas permissões.');
            }

            $permission = Permission::where('name', request('permission'))->first();

            if ($permission->users->count() == 1 && $permission->users->contains($user)) {
                return $this->respondBadRequest(null, 'Pelo menos um membro precisa ter esta permissão.');
            }

            $user->perms()->toggle($permission);
            return $this->respond($user->perms->pluck('name')->all());
        }

        return $this->respondNotFound(null);
    }

    /**
     * Update User profile.
     *
     * @return mixed
     */
    public function updateProfile()
    {
        try {
            $user = auth()->user();
            $user->update(request()->all());

            return $this->respond($this->transformItem($user, new UserTransformer()));
        } catch (Exception $e) {
            Log::error(logMessage($e, 'Ocorreu um erro ao atualizar dados do usuário.'), logUser());

            return $this->respondInternalError($e);
        }
    }

    /**
     * Update User profile.
     *
     * @param UserPasswordRequest $request
     * @return mixed
     */
    public function updatePassword(UserPasswordRequest $request)
    {
        try {
            $user = auth()->user();

            $user->update($request->all());

            return $this->respond($this->transformItem($user, new UserTransformer()));
        } catch (Exception $e) {
            Log::error(logMessage($e, 'Ocorreu um erro ao atualizar dados do usuário.'), logUser());

            return $this->respondInternalError($e);
        }
    }

    /**
     * If user was not recently created, update "is_user" to true.
     *
     * @param User $user
     */
    private function setAsUser(User $user)
    {
        if (! $user->wasRecentlyCreated) {
            $user->is_user = true;
            $user->save();
        }
    }

    /**
     * Restore user if were trashed.
     *
     * @param User $user
     */
    private function restoreTrashed(User $user)
    {
        if ($user->trashed()) {
            $user->deleted_at = null;
            $user->save();
        }
    }

    /**
     * Create a registation token.
     *
     * @param User $user
     */
    private function createRegistrationToken(User $user)
    {
        UserRegistration::create(['email' => $user->email, 'token' => md5($user->email)]);
    }

    /**
     * Sync user with the default permissions.
     *
     * @param User $user
     */
    private function addDefaultUserPermissions(User $user)
    {
        $permissions = Permission::whereNotIn('name', ['manage-users', 'manage-account'])->pluck('id')->all();
        $user->perms()->sync($permissions);
    }
}
