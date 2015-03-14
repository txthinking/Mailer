<?php
use Tx\Mailer;
class MailerTest extends TestCase{
    public function testSend(){
        $mail = new Mailer();
        $mail->setServer('smtp.ym.163.com', 25);
        $mail->setAuth('', '');
        $mail->setFrom('You', '');
        $mail->setTo('Cloud', 'cloud@txthinking.com');
        $mail->setSubject('Test Mailer');
        $mail->setBody('Hi, I <strong>love</strong> you.');
        $r = $mail->send();
        $this->assertTrue($r);
    }
}

