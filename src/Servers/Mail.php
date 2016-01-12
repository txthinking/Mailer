<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Laasti\Mailer\Servers;

use Psr\Log\LoggerInterface;
use Laasti\Mailer\Exceptions\SendException;
use Laasti\Mailer\Message;

/**
 * Description of Mail
 *
 * @author Sonia
 */
class Mail implements ServerInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function send(Message $message) {
        // most documentation of sendmail using the "-f" flag lacks a space after it, however
        // we've encountered servers that seem to require it to be in place.
        $sent = mail($message->getHeader('To'), $message->getSubject(), $message->getEncodedBody(true), $message->headersToString(), '-f '.$message->getHeader('Return-Path'));

        if ($sent) {
            $this->logger && $this->logger->addDebug('Sent: '. $message->getHeader('To'));
        } else {
            throw new SendException('The message could not be delivered using mail().');
        }

        return $sent;
    }
}
