<?php

declare(strict_types=1);

namespace Galeas\Api\Service\Email;

class Emailer {
    public function send(
        string $to,
        string $subject,
        string $message,
        string $from)
    {
        $headers = 'From: ' . $from;
        $success = mail($to, $subject, $message, $headers);

        if (!$success) {
            throw new CouldNotSendEmail();
        }
    }
}