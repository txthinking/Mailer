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
        $this->smtp = new SMTP();
        $this->message = new Message();
    }

    public function testSend(){
        try {
            $this->smtp->setServer('smtp.ym.163.com', 25)
                ->setAuth('', ''); // email, password

            $this->message->setFrom('Tom', '')
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

}

