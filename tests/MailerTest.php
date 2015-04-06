<?php

use \Tx\Mailer;
use \Tx\Mailer\SMTP;
use \Tx\Mailer\Message;
use \Tx\Mailer\Exceptions\SMTPException;
use \Monolog\Logger;

class MailerTest extends TestCase {

    protected $smtp;
    protected $message;

    public function setup(){
        $this->smtp = new SMTP(new Logger('SMTP'));
        $this->message = new Message();
    }

    public function testSMTP(){
        $this->smtp
            ->setServer('smtp.ym.163.com', 25)
            ->setAuth('bot@ym.txthinking.com', '111111'); // email, password

        $this->message
            ->setFrom('Tom', 'bot@ym.txthinking.com') // your name, your email
            ->setFakeFrom('heelo', 'bot@baidu.com') // a fake name, a fake email
            ->setTo('Cloud', 'cloud@txthinking.com')
            ->setSubject('Test SMTP ' . time())
            ->setBody('<h1>for test</h1>');

        $status = $this->smtp->send($this->message);
        $this->assertTrue($status);
    }

    public function testSend(){
        $status = (new Mailer(new Logger('Mailer')))
            ->setServer('smtp.ym.163.com', 25)
            ->setAuth('bot@ym.txthinking.com', '111111') // email, password
            ->setFrom('You', 'bot@ym.txthinking.com') //your name, your email
            ->setTo('Cloud', 'cloud@txthinking.com')
            ->setSubject('Test Mailer '. time())
            ->setBody('Hi, I <strong>love</strong> you.')
            ->send();
        $this->assertTrue($status);
    }

}

