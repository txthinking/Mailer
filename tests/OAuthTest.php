<?php

use \Tx\Mailer;
use \Tx\Mailer\SMTP;
use \Tx\Mailer\Message;
use \Tx\Mailer\Exceptions\SMTPException;
use \Monolog\Logger;

class OAuthTest extends TestCase
{
    public function testOAuth2()
    {
        if(!self::OAUTH_TOKEN){
            return;
        }
        $mail = new Mailer(new Logger('Mailer.OAuth'));
        $status = $mail->setServer(self::OAUTH_SERVER, self::OAUTH_PORT, 'tls')
            ->setOAuth(self::OAUTH_TOKEN)
            ->setFrom(self::OAUTH_FROM_NAME, self::OAUTH_FROM_EMAIL)
            ->addTo(self::TO_NAME, self::TO_EMAIL)
            ->addCc(self::CC_NAME, self::CC_EMAIL)
            ->addBcc(self::BCC_NAME, self::BCC_EMAIL)
            ->setSubject('Test Mailer OAuth2'. time())
            ->setBody('Hi, boy')
            ->addAttachment('test', __FILE__)
            ->send();
        $this->assertTrue($status);
    }
}

