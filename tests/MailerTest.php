<?php
use Tx\Mailer;
class MailerTest extends TestCase{
    public function testSend(){
        $r = (new Mailer())
            ->setServer('smtp.ym.163.com', 25)
            ->setAuth('', '') // email, password
            ->setFrom('You Name', '') //your name, your email
            ->setTo('Cloud', 'cloud@txthinking.com')
            ->setSubject('Test Mailer')
            ->setBody('Hi, I <strong>love</strong> you.')
            ->send();
        $this->assertTrue($r['result']);
    }
}

