<?php

use \Tx\Mailer;
use \Tx\Mailer\SMTP;
use \Tx\Mailer\Message;
use \Tx\Mailer\Exceptions\SMTPException;
use \Monolog\Logger;

class MailerTest extends TestCase
{

    public function setUp(): void
    {
    }

    public function testSend()
    {
        $mail = new Mailer(new Logger('Mailer'));
        $status = $mail->setServer(self::SERVER, self::PORT_TLS, 'tls')
            ->setAuth(self::USER, self::PASS) // email, password
            ->setFrom(self::FROM_NAME, self::FROM_EMAIL) // your name, your email
            //->setFakeFrom('张全蛋', 'zhangquandan@hello.com') // a fake name, a fake email
            ->addTo(self::TO_NAME, self::TO_EMAIL)
            ->addCc(self::CC_NAME, self::CC_EMAIL)
            ->addBcc(self::BCC_NAME, self::BCC_EMAIL)
            ->setSubject('Test Mailer '. time())
            ->setBody('Hi, boy')
            ->addAttachment('test', __FILE__)
            ->send();
        $this->assertTrue($status);
        usleep(self::DELAY);
    }

}

