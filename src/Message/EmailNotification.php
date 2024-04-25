<?php

namespace App\Message;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class EmailNotification
{
    private $email;

    public function __construct(TemplatedEmail $email)
    {
        $this->email = $email;
    }

    public function getEmail(): TemplatedEmail
    {
        return $this->email;
    }
}