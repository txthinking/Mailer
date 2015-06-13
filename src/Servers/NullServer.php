<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Laasti\Mailer\Servers;

use Laasti\Mailer\Message;
use Psr\Log\LoggerInterface;

/**
 * Description of Null
 *
 * @author Sonia
 */
class NullServer implements ServerInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function send(Message $message) {

        $this->logger && $this->logger->addDebug('Should have sent: '. $message->getHeader('To'));

        return true;
    }

}
