<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Transformable;
use App\Http\Requests\AuthRequest;
use App\UPCont\Transformer\UserTransformer;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends ApiController
{

    use Transformable;

    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->middleware('jwt.auth', ['only' => 'isAuthorized']);
    }

    /**
     * Authenticate the user using the email and password,
     * if user is granted an access token will be retrived.
     *
     * @param AuthRequest $request
     * @return mixed
     */
    public function authenticate(AuthRequest $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            $user = User::where('email', $credentials['email'])->first();
            
            if (! $user) {
                Log::warning('Tentativa de conexão com usuário inexistente', $credentials);

                return $this->respondNotFound(null, 'E-mail ou senha incorretos, tente novamente.');
            }

            if (! $user->isActive()) {
                Log::warning('Algum usuário não ativo tentou se conectar', $credentials);

                return $this->respondBadRequest(null, 'Você não tem permissão para acessar a ferramenta.');
            }

            if (! $token = JWTAuth::attempt($credentials)) {
                Log::warning('Não foi possível autenticar o usuário', $credentials);

                return $this->respondNotFound(null, 'E-mail ou senha incorretos, tente novamente.');
            }
        } catch (JWTException $e) {
            Log::critical(logMessage($e, 'Não foi possível criar o token.'), $credentials);

            return $this->respondInternalError($e, 'Não foi possível autenticar o usuário devido a problemas internos, tente novamente mais tarde.');
        }

        return $this->respond(['token' => $token]);
    }

    /**
     * Check if have an authenticated user and retrieve it.
     *
     * @return mixed
     */
    public function getAuthenticatedUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                Log::warning("Não existe usuário autenticado.", logUser());

                return $this->respondNotFound(null, 'Usuário não encontrado');
            }

            $user = User::with('roles')->findOrFail($user->id);
            $token = JWTAuth::refresh(JWTAuth::getToken());

            return $this->respond([
                'user' => $this->transformItem($user, new UserTransformer(), ['roles', 'permissions', 'notifications']),
                'token' => $token
            ]);
        } catch (TokenExpiredException $e) {
            Log::warning(logMessage($e, 'Tentando buscar usuário com token expirado.'), logUser());

            return $this->respondNotFound($e, 'O token foi expirado.');
        } catch (TokenInvalidException $e) {
            Log::warning(logMessage($e, 'Tentando buscar usuário com token inválido.'), logUser());

            return $this->respondNotFound($e, 'O token não é válido.');
        } catch (JWTException $e) {
            Log::warning(logMessage($e, 'Tentando buscar usuário com token inexistente.'), logUser());

            return $this->respondNotFound($e, 'O token não existe.');
        }
    }

    /**
     * Refresh the access token.
     *
     * @return mixed
     */
    public function refreshToken()
    {
        $token = JWTAuth::getToken();

        if (! $token) {
            Log::warning('Não encontrou o token em tentativa de atualizar o token.');

            return $this->respondBadRequest(null, 'Você foi desconectado por inatividade.');
        }

        try {
            $token = JWTAuth::refresh($token);
            Log::info("Token atualizado: ({$token})");
        } catch (TokenInvalidException $e) {
            Log::warning(logMessage($e, 'Não encontrou o token em tentativa de atualizar o token.'));

            return $this->respondNotFound('Sessão expirada, conecte novamente.');
        }

        if (request('redirect')) {
            return redirect(sprintf('%s?token=%s', request('redirect'), $token));
        }

        return $this->respond(['token' => $token]);
    }

    /**
     * Check if the authenticated user have the permission.
     *
     * @return mixed
     */
    public function isAuthorized()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $permission = request('permission');

        return $this->respond(['authorization' => $user->can($permission)]);
    }

}
