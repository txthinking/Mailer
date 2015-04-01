<?php

namespace Tests;

use Tx\Mailer;
use \Tx\Util\Mailer\Exceptions\SMTPException;

class MailerTest extends \PHPUnit_Framework_TestCase {
    public function testSend(){
        try {
            $mail = new Mailer();
            $mail->setServer('smtp.ym.163.com', 25);
            $mail->setAuth('', ''); // email, password
            $mail->setFrom('You', ''); //your name, your email
            $mail->setFakeFrom('Them', ''); //your name, your email
            $mail->setTo('Cloud', 'cloud@txthinking.com');
            $mail->setSubject('Test Mailer');
            $mail->setBody('Hi, I <strong>love</strong> you.');
            $status = $mail->send();
            $this->assertTrue($status);
        } catch (SMTPException $se) {
            $this->fail("A SMTP exception has been raised.  Mailer has failed. {$se->getMessage()}");
        } catch (\Exception $e) {
            $this->fail("An Unknown exception has been raised.  Mailer has failed. {$e->getMessage()}");
        }
    }
}

