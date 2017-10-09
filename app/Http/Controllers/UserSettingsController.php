<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserNotificationRequest;
use Illuminate\Http\Request;

class UserSettingsController extends ApiController
{

    /**
     * Get user notifications.
     *
     * @return mixed
     */
    public function notifications()
    {
        try {
            $user = auth()->user();

            return $this->respond($user->notificationsSettings->toArray());

        } catch (Exception $e) {
            Log::error(logMessage($e, 'Não foi possível buscar as notificações.'), logUser());

            return $this->respondInternalError($e);
        }
    }

    /**
     * Toggle the notification setting of user.
     *
     * @param UserNotificationRequest $request
     * @return mixed
     */
    public function toggleNotification(UserNotificationRequest $request)
    {
        try {
            $user = auth()->user();
            $notification = $request->get('notification');

            if ($user->notificationsSettings->contains('notification', $notification)) {
                $user->notificationsSettings()->where('notification', $notification)->first()->delete();

                return $this->respond([$notification => false]);
            }

            $user->notificationsSettings()->create(['notification' => $notification]);

            return $this->respondCreated([$notification => true]);

        } catch (Exception $e) {
            Log::error(logMessage($e, 'Não foi possível alterar o status da notificação.'), logUser());

            return $this->respondInternalError($e);
        }
    }
}
