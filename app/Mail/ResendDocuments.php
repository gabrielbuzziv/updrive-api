<?php

namespace App\Mail;

use App\Dispatch;
use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\DocumentTransformer;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tymon\JWTAuth\Facades\JWTAuth;

class ResendDocuments extends Mailable
{
    use Queueable, SerializesModels, Transformable;

    /**
     * Dispatch
     *
     * @var
     */
    protected $dispatch;

    /**
     * Recipient
     *
     * @var
     */
    protected $recipient;

    /**
     * Token
     *
     * @var
     */
    protected $token;

    /**
     * ResendDocuments constructor.
     *
     * @param Dispatch $dispatch
     * @param User $recipient
     */
    public function __construct(Dispatch $dispatch, User $recipient)
    {
        $this->dispatch = $dispatch;
        $this->recipient = $recipient;
        $this->token = JWTAuth::fromUser($this->recipient);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $account = config('account');

        $this->from(env('MAIL_FROM_ADDRESS'), $account->name)
            ->subject("{$this->dispatch->subject}")
            ->replyTo($account->email)
            ->view('emails.default', [
                'subject'       => $this->dispatch->subject,
                'company'       => $this->dispatch->company,
                'description'   => $this->dispatch->message,
                'documents'     => $this->transformCollection($this->dispatch->documents, new DocumentTransformer()),
                'regards'       => [
                    'name'  => $account->name,
                    'email' => $account->email,
                ],
                'token'         => $this->token,
                'authorize_url' => action('AuthController@refreshToken', $account->slug),
                'frontend_url'  => config('app.frontend'),
                'footer'        => true,
            ]);

        $this->withSwiftMessage(function ($message) {
            $message->getHeaders()
                ->addTextHeader('X-Mailgun-Variables', json_encode([
                    'account'  => config('account')->id,
                    'dispatch' => $this->dispatch->id,
                    'contact'  => $this->recipient->id
                ]));
        });
    }
}
