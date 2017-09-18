<?php

namespace App\Illuminate\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class UPMailMessage extends MailMessage
{

    public $listTitle = null;

    public $formatedList = [];

    public $description;

    public $documents;

    public $token;

    public $regards;

    /**
     * Set formated list as array.
     *
     * @param $list
     * @param $title
     * @return $this
     */
    public function formatedList($list, $title = null)
    {
        if (! empty($title)) {
            $this->listTitle = $title;
        }

        $this->formatedList[] = $list;

        return $this;
    }

    /**
     * Set description
     *
     * @param $description
     * @return $this
     */
    public function description($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set token
     *
     * @param $token
     * @return $this
     */
    public function token($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Set documents
     *
     * @param $documents
     * @return $this
     */
    public function documents($documents)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * Set regards.
     *
     * @param $regards
     * @return $this
     */
    public function regards($regards)
    {
        $this->regards = $regards;

        return $this;
    }

    /**
     * Class as array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'subject'      => $this->subject,
            'description'  => $this->description,
            'token'        => $this->token,
            'level'        => $this->level,
            'subject'      => $this->subject,
            'greeting'     => $this->greeting,
            'introLines'   => $this->introLines,
            'documents'    => $this->documents,
            'outroLines'   => $this->outroLines,
            'actionText'   => $this->actionText,
            'actionUrl'    => $this->actionUrl,
            'formatedList' => $this->formatedList,
            'listTitle'    => $this->listTitle,
            'regards'      => $this->regards,
        ];
    }

    /**
     * Get the data array for the mail message.
     *
     * @return array
     */
    public function data()
    {
        return array_merge($this->toArray(), $this->viewData);
    }
}