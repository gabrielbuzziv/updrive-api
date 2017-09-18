<?php

namespace App\Http\Controllers;

use App\Notifications\PasswordResetLink;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PasswordResetController extends ApiController
{

    /**
     * Check if the email is valid and then send a email with a reset link confirmation.
     *
     * @param Request $request
     * @return mixed
     */
    public function sendResetLinkEmail(Request $request)
    {
        $data = $request->all();

        try {
            $user = User::where('email', $data['email'])->first();

            if (! $user) {
                return $this->respondNotFound('Não encontramos nenhum usuário com este e-mail.');
            }

            $token = md5(uniqid(rand(), true));
            DB::table('password_resets')->insert([
                'email'      => $user->email,
                'token'      => $token,
                'created_at' => Carbon::now(),
            ]);

            $user->notify(new PasswordResetLink($user, $token));

            return $this->respond([
                'message' => $this->messageSuccess('Enviamos um e-mail para confirmar sua solicitação.'),
            ]);
        } catch (Exception $e) {
            Log::error($e, logMessage('Não foi possível enviar o e-mail para resetar a senha'), $data);

            return $this->respondInternalError();
        }
    }

    /**
     * Reset the password
     *
     * @param Request $request
     * @return mixed
     */
    public function resetPassword(Request $request)
    {
        $data = $request->all();

        try {
            $validation = DB::table('password_resets')->where('email', $data['email'])
                ->where('token', $data['token'])
                ->first();

            if (! $validation) {
                return $this->respondBadRequest(null, 'Esta solicitação expirou, caso deseje trocar a senha, solicite novamente.');
            }

            if (Carbon::createFromFormat('Y-m-d H:i:s', $validation->created_at)->diffInHours(Carbon::now()) > 2) {
                return $this->respondBadRequest(null, 'O token não é mais válido, solicite uma nova senha novamente.');
            }

            $user = User::where('email', $data['email'])->first();
            $user->password = $data['password'];
            $user->save();

            DB::table('password_resets')->where('email', $data['email'])
                ->where('token', $data['token'])
                ->delete();

            return $this->respond([
                'message' => $this->messageSuccess('A senha foi alterada com sucesso, você será logado em instantes.'),
            ]);
        } catch (Exception $e) {
            Log::error($e, logMessage('Não foi possível enviar o e-mail para resetar a senha'), $data);

            return $this->respondInternalError($e);
        }
    }

}
