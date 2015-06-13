<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Laasti\Mailer\Servers;

use Laasti\Mailer\Message;

/**
 *
 * @author Sonia
 */
interface ServerInterface
{
    public function send(Message $message);
}
