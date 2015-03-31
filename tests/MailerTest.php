<?php
use Tx\Mailer;
use \Tx\Util\Mailer\Exceptions\SMTPException;

class MailerTest extends TestCase{


    public function testSend(){
        try {
            $mail = new Mailer();
//        $mail->setServer('smtp.ym.163.com', 25);
            $mail->setServer('erbfs2', 25);
//        $mail->setAuth('', ''); // email, password
            $mail->setFrom('You', ''); //your name, your email
            $mail->setFakeFrom('Them', ''); //your name, your email
//        $mail->setTo('Cloud', 'cloud@txthinking.com');
            $mail->setTo('MS', 'msowers@erblearn.org');
            $mail->setSubject('Test Mailer');
            $mail->setBody('Hi, I <strong>love</strong> you.');
            $r = $mail->send();
        }
        catch (SMTPException $e) {
            $this->fail("An exception has been raised.  Mailer has failed.");
        }
    }


}

