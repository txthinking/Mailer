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
class FileServer implements ServerInterface
{
    protected $logger;
    protected $filepath;

    public function __construct($filepath, LoggerInterface $logger = null)
    {
        $this->filepath = $filepath;
        $this->logger = $logger;
    }

    public function send(Message $message) {

        if (!is_writable($this->filepath)) {
            throw new \Laasti\Mailer\Exceptions\FileServerException('The message destination directory is not writeable: '.$this->filepath);
        }
        $in = $message->toString();
        $file = addslashes($this->filepath.'/'.date('Y-m-d H-i-s').'-'.md5($in).'.txt');

        if (!file_put_contents($file, $in)) {
            throw new \Laasti\Mailer\Exceptions\FileServerException('Could not write message file to disk: '.$file);
        }

        $this->logger && $this->logger->addDebug('Mail saved: '. $file);

        return true;
    }

}
