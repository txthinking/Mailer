<?php

namespace Tx\Mailer\Tests;

use Tx\Mailer\SMTP;
use Tx\Mailer\Message;
use Monolog\Logger;

/**
 * Class SMTPTest
 * @package Tests
 *
 * This test set requires the use of an open SMTP server mock.  Currently, I'm using FakeSMTPServer
 *
 */
class SMTPTest extends TestCase
{

    /**
     * @var SMTP
     */
    protected $smtp;

    /**
     * @var  Message
     */
    protected $message;

    public function setUp(): void
    {
        $this->message = new Message();
        $this->message
            ->setFrom(self::FROM_NAME, self::FROM_EMAIL) // your name, your email
            //->setFakeFrom('Hello', 'bot@fakeemail.com') // a fake name, a fake email
            ->addTo(self::TO_NAME, self::TO_EMAIL)
            ->addCc(self::CC_NAME, self::CC_EMAIL)
            ->addBcc(self::BCC_NAME, self::BCC_EMAIL)
            ->setSubject('Test SMTP ' . time())
            ->setBody('<h1>for test</h1>')
            ->addAttachment('test', __FILE__);
        usleep(self::DELAY);
    }

    public function testSend()
    {
        $this->smtp = new SMTP(new Logger('SMTP'));
        $this->smtp
            ->setServer(self::SERVER, self::PORT)
            ->setAuth(self::USER, self::PASS);

        $status = $this->smtp->send($this->message);
        $this->assertTrue($status);
        usleep(self::DELAY);
    }

    public function testTLSSend()
    {
        $this->smtp = new SMTP(new Logger('SMTP.tls'));
        $this->smtp
            ->setServer(self::SERVER, self::PORT_TLS, 'tls')
            ->setAuth(self::USER, self::PASS);

        $status = $this->smtp->send($this->message);
        $this->assertTrue($status);
        usleep(self::DELAY);
    }

    public function testConnectSMTPException()
    {
        $this->expectException(\Tx\Mailer\Exceptions\SMTPException::class);
        $this->smtp = new SMTP(new Logger('SMTP.FakePort'));
        $this->smtp
            ->setServer('localhost', "99999", null)
            ->setAuth('none', 'none');

        $this->smtp->send($this->message);
        usleep(self::DELAY);
    }

}
