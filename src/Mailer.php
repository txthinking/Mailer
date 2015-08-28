<?php

namespace Laasti\Mailer;

use Laasti\Mailer\Message;
use Laasti\Mailer\Servers\ServerInterface;

/**
 * Class Mailer
 *
 * @package Laasti
 */
class Mailer{
    /**
     * Server Class
     * @var ServerInterface
     */
    protected $server;

    /**
     * Mail Message
     * @var Message
     */
    protected $message;

    /**
     * construct function
     */
    public function __construct(ServerInterface $server=null){
        $this->server = $server;
        $this->message = new Message();
    }

    /**
     * Message Factory
     * @return Message
     */
    public function createMessage()
    {
        return new Message();
    }

    /**
     * Clears current message so that a new message can be sent using the fluent API
     * @return Mailer
     */
    public function clearMessage()
    {
        $this->message = new Message();
        return $this;
    }
    
    /**
     * set mail from
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setFrom($name, $email){
        $this->message->setFrom($name, $email);
        return $this;
    }

    /**
     * set fake mail from
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setFakeFrom($name, $email){
        $this->message->setFakeFrom($name, $email);
        return $this;
    }

    /**
     * set mail reply to
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setReplyTo($name, $email)
    {
        $this->message->setReplyTo($name, $email);
        return $this;
    }

    /**
     * set mail receiver
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setTo($name, $email){
        $this->message->addTo($name, $email);
        return $this;
    }

    /**
     * add mail receiver
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function addTo($name, $email){
        $this->message->addTo($name, $email);
        return $this;
    }

    /**
     * set mail subject
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject){
        $this->message->setSubject($subject);
        return $this;
    }

    /**
     * set mail text body
     * @param string $txtbody
     * @return $this
     */
    public function setTextBody($txtbody)
    {
        $this->message->setTextBody($txtbody);
        return $this;
    }

    /**
     * set mail body
     * @param string $body
     * @return $this
     */
    public function setBody($body){
        $this->message->setBody($body);
        return $this;
    }

    /**
     * set mail attachment
     * @param $name
     * @param $path
     * @return $this
     * @internal param string $attachment
     */
    public function setAttachment($name, $path){
        $this->message->addAttachment($name, $path);
        return $this;
    }

    /**
     * add mail attachment
     * @param $name
     * @param $path
     * @return $this
     * @internal param string $attachment
     */
    public function addAttachment($name, $path){
        $this->message->addAttachment($name, $path);
        return $this;
    }
    /**
     * set mail charset
     * @param string $charset
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->message->setCharset($charset);
        return $this;
    }

    /**
     *  Send the message...
     * @return boolean
     */
    public function send($message = null){
        return $this->server->send($message ?: $this->message);
    }

}

