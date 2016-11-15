<?php

namespace Laasti\Mailer;

interface MailerAwareInterface
{
    /**
     * @returns Mailer
     */
    public function getMailer();
    
    /**
     * 
     * @param \Laasti\Mailer\Mailer $mailer
     */
    public function setMailer(Mailer $mailer);
}
