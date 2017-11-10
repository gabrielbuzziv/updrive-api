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

class ExpiringDocuments extends Mailable
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
     * ExpiringDocuments constructor.
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
                    'account'   => config('account')->id,
                    'dispatch'  => $this->dispatch->id,
                    'recipient' => $this->recipient->id
                ]));
        });
    }

    /**
     * Generate data base in the days left to expire document.
     *
     * @return object
     */
    private function getData()
    {
        switch ($this->days) {
            case 0:
                return (object) [
                    'subject'     => 'Você tem documentos que vencem hoje.',
                    'description' => 'Identificamos que alguns documentos que enviamos para você ainda não foram baixados, evite perder a data de vencimento. Abaixo está os documentos com vencimento para hoje.'
                ];
            case 1:
                return (object) [
                    'subject'     => 'Você tem documentos que vencem amanhã.',
                    'description' => 'Identificamos que alguns documentos que enviamos para você ainda não foram baixados, evite perder a data de vencimento. Abaixo está os documentos com vencimento para amanhã.'
                ];
            case 2:
                return (object) [
                    'subject'     => 'Você tem documentos que vencem em 2 dias.',
                    'description' => 'Identificamos que alguns documentos que enviamos para você ainda não foram baixados, evite perder a data de vencimento. Abaixo está os documentos com vencimento em 2 dias.'
                ];
        }
    }
}
