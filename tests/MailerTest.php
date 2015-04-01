<?php

namespace Tests;

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
        try {
            $this->smtp->setServer('smtp.ym.163.com', 25)
                ->setAuth('', ''); // email, password

            $this->message->setFrom('Tom', '') // your name, your email
                ->setTo('Cloud', 'cloud@txthinking.com')
                ->setSubject('hi')
                ->setBody('for test');

            $status = $this->smtp->send($this->message);
            $this->assertTrue($status);
        } catch (SMTPException $se) {
            $this->fail("A SMTP exception has been raised.  Mailer has failed. {$se->getMessage()}");
        } catch (\Exception $e) {
            $this->fail("An Unknown exception has been raised.  Mailer has failed. {$e->getMessage()}");
        }
    }

    public function testSend(){
        $status = (new Mailer(new Logger('Mailer')))
            ->setServer('smtp.ym.163.com', 25)
            ->setAuth('', '') // email, password
            ->setFrom('You', '') //your name, your email
            ->setTo('Cloud', 'cloud@txthinking.com')
            ->setSubject('Test Mailer')
            ->setBody('Hi, I <strong>love</strong> you.')
            ->send();
        $this->assertTrue($status);
    }

}

