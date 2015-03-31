<?php
/**
 * Created by PhpStorm.
 * User: msowers
 * Date: 3/30/15
 * Time: 1:59 PM
 */

namespace Tx\Util\Mailer;


class Message
{
    /**
     * from name
     */
    protected $fromName;

    /**
     * from email
     */
    protected $fromEmail;

    /**
     * fake from name
     */
    protected $fakeFromName;

    /**
     * fake from email
     */
    protected $fakeFromEmail;

    /**
     * to email
     */
    protected $to = array();

    /**
     * mail subject
     */
    protected $subject;

    /**
     * mail body
     */
    protected $body;

    /**
     *mail attachment
     */
    protected $attachment = array();


    /**
     * set mail from
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setFrom($name, $email)
    {
        $this->fromName = $name;
        $this->fromEmail = $email;
        return $this;
    }


    /**
     * set mail fake from
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setFakeFrom($name, $email)
    {
        $this->fakeFromName = $name;
        $this->fakeFromEmail = $email;
        return $this;
    }

    /**
     * set mail receiver
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setTo($name, $email){
        $this->to[$name] = $email;
        return $this;
    }

    /**
     * set mail subject
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject){
        $this->subject = $subject;
        return $this;
    }

    /**
     * set mail body
     * @param string $body
     * @return $this
     */
    public function setBody($body){
        $this->body = $body;
        return $this;
    }

    /**
     * add mail attachment
     * @param $name
     * @param $path
     * @return $this
     */
    public function addAttachment($name, $path){
        $this->attachment[$name] = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @return string
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }


    /**
     * @return string
     */
    public function getFakeFromName()
    {
        return $this->fakeFromName;
    }

    /**
     * @return string
     */
    public function getFakeFromEmail()
    {
        return $this->fakeFromEmail;
    }

    public function hasFakeFrom()
    {
        return $this->fakeFromEmail !== null;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getAttachment()
    {
        return $this->attachment;
    }






}
