<?php

use \Tx\Mailer;
use \Tx\Mailer\SMTP;
use \Tx\Mailer\Message;
use \Tx\Mailer\Exceptions\SMTPException;
use \Monolog\Logger;

class MailerTest extends TestCase {

    /** @var  SMTP */
    protected $smtp;
    /** @var  Message */
    protected $message;

    public function setup(){
        $this->smtp = new SMTP(new Logger('SMTP'));
        $this->message = new Message();
    }

    public function testSMTP(){
        $this->smtp
            ->setServer(self::SERVER, self::PORT)
            ->setAuth(self::USER, self::PASS); // email, password

        $this->message
            ->setFrom('Tom', 'bot@ym.txthinking.com') // your name, your email
            ->setFakeFrom('heelo', 'bot@hello.com') // a fake name, a fake email
            ->addTo('Cloud', 'cloud@txthinking.com')
            ->setSubject('Test SMTP ' . time())
            ->setBody('<h1>for test</h1>')
            ->addAttachment('host', __FILE__);

        $status = $this->smtp->send($this->message);
        $this->assertTrue($status);
        usleep(self::DELAY);
    }

    public function testSend(){
        $mail = new Mailer(new Logger('Mailer'));
        $mail->setServer(self::SERVER, self::PORT)
            ->setAuth(self::USER, self::PASS) // email, password
            ->setFrom('Tom', 'bot@ym.txthinking.com') // your name, your email
            ->setFakeFrom('张全蛋', 'zhangquandan@hello.com') // a fake name, a fake email
            ->addTo('Cloud', 'cloud@txthinking.com')
            ->setSubject('hello '. time())
            ->setBody('Hi, boy')
            ->addAttachment('host', __FILE__)
            ->send();
        $this->assertTrue($status);
        usleep(self::DELAY);
    }

}

