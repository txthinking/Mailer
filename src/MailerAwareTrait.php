<?php

namespace Laasti\Mailer;

trait MailerAwareTrait
{
    protected $mailer;
    
    /**
     * @returns Mailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }
    
    /**
     * 
     * @param \Laasti\Mailer\Mailer $mailer
     */
    public function setMailer(Mailer $mailer)
    {
        $this->mailer = $mailer;
        return $this;
    }
}
