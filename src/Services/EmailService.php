<?php

namespace App\Services;

use App\Entity\Comment;
use App\Entity\Post;
use App\Message\EmailNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;

class EmailService
{
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function notifyPostAuthorAboutNewComment(Post $post, Comment $comment, string $locale): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@blog-demo.com', 'Blog Demo'))
            ->to($post->getAuthor()->getEmail())
            ->subject('New comment on your post')
            ->htmlTemplate('emails/new-comment.html.twig')
            ->context([
                'post' => $post,
                'comment' => $comment,
                'locale' => $locale,
            ]);

        $this->messageBus->dispatch(new EmailNotification($email));
    }
}