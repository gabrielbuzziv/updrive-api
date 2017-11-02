<?php

namespace App\Mail;

use App\DocumentDispatch;
use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\DocumentTransformer;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tymon\JWTAuth\Facades\JWTAuth;

class NewDocuments extends Mailable
{

    use Queueable, SerializesModels, Transformable;

    /**
     * Dispatch
     *
     * @var
     */
    protected $dispatch;

    /**
     * Contact
     *
     * @var
     */
    protected $contact;

    /**
     * Token
     *
     * @var
     */
    protected $token;

    /**
     * Create a new message instance.
     *
     * @param DocumentDispatch $dispatch
     * @param User $contact
     */
    public function __construct(DocumentDispatch $dispatch, User $contact)
    {
        $this->dispatch = $dispatch;
        $this->contact = $contact;
        $this->token = JWTAuth::fromUser($this->contact);
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
            ->replyTo($this->dispatch->user->email)
            ->view('emails.default', [
                'subject'       => "{$this->dispatch->company->name}: {$this->dispatch->subject}",
                'description'   => $this->dispatch->message,
                'documents'     => $this->transformCollection($this->dispatch->documents, new DocumentTransformer()),
                'regards'       => [
                    'name'  => $this->dispatch->user->name,
                    'email' => $this->dispatch->user->email,
                ],
                'token'         => $this->token,
                'authorize_url' => action('AuthController@refreshToken', $account->slug),
                'frontend_url'  => config('app.frontend'),
                'footer'        => true,
            ]);

        $this->withSwiftMessage(function ($message) {
            $variables = json_encode([
                'account'  => config('account')->id,
                'dispatch' => $this->dispatch->id,
                'contact'  => $this->contact->id,
            ]);

            $message->getHeaders()
                ->addTextHeader('X-Mailgun-Variables', $variables);
        });
    }
}
