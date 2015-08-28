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
class Sendmail implements ServerInterface
{
    protected $logger;
    protected $mailpath;

    public function __construct(LoggerInterface $logger = null, $mailpath = '/usr/sbin/sendmail')
    {
        $this->logger = $logger;
        $this->mailpath = $mailpath;
    }

    public function send(Message $message) {
        $in = $message->toString();

        $email = !is_null($message->getFakeFromEmail()) ? $message->getFakeFromEmail() : $message->getFromEmail();

        // is popen() enabled?
        if ( ! function_exists('popen')
                || FALSE === ($fp = @popen($this->mailpath.' -oi -f '.$email.' -t -r '.$email, 'w'))
        ) {
            // server probably has popen disabled, so nothing we can do to get a verbose error.
            throw new SendException('The message could not be delivered using sendmail. The function popen() is disabled.');
        }
        fputs($fp, $message->headersToString());
        fputs($fp, $in);

        $status = pclose($fp);
        if ($status !== 0) {
            throw new SendException('Cannot open a socket to Sendmail. Check settings. Status code: '.$status.'.');
        }

        $this->logger && $this->logger->addDebug('Sent: '. $message->getHeader('To'));

        return true;
    }
}
