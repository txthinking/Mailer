<?php
/**
 * Created by PhpStorm.
 * User: msowers
 * Date: 3/31/15
 * Time: 10:57 AM
 */

use Tx\Mailer\SMTP;
use Tx\Mailer\Message;
use ERB\Testing\Tools\TestHelper;

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
     * @var TestHelper
     */
    protected $testHelper;

    /** @var  Message */
    protected $message;


    public function setup()
    {
        $this->smtp = new SMTP();
        $this->testHelper = new TestHelper();
        $this->message = new Message();
        $this->message->setFrom('You', 'nobody@nowhere.no')
                ->addTo('Them', 'them@nowhere.no')
                ->setSubject('This is a test')
                ->setBody('This is a test part two');
        usleep(self::DELAY);
    }

    public function testSetServer()
    {
        $result = $this->smtp->setServer('localhost', '25', null);
        $this->assertEquals('localhost', $this->testHelper->getPropertyValue($this->smtp, 'host'));
        $this->assertEquals('25', $this->testHelper->getPropertyValue($this->smtp, 'port'));
        $this->assertSame($this->smtp, $result);
        usleep(self::DELAY);
    }

    public function testSetAuth()
    {
        $result = $this->smtp->setAuth('none', 'none');

        $this->assertEquals('none', $this->testHelper->getPropertyValue($this->smtp, 'username'));
        $this->assertEquals('none', $this->testHelper->getPropertyValue($this->smtp, 'password'));
        $this->assertSame($this->smtp, $result);
        usleep(self::DELAY);
    }

    public function testMessage()
    {
        $this->smtp->setServer(self::SERVER, self::PORT, null)
            ->setAuth(self::USER, self::PASS);

        $status = $this->smtp->send($this->message);
        $this->assertTrue($status);
        usleep(self::DELAY);
    }

    public function testTLSMessage()
    {
        $this->smtp->setServer(self::SERVER, self::PORT, 'tls')
                   ->setAuth(self::USER, self::PASS);

        $status = $this->smtp->send($this->message);
        $this->assertTrue($status);
        usleep(self::DELAY);
    }

    /**
     * @expectedException \Tx\Mailer\Exceptions\SMTPException
     */
    public function testConnectSMTPException()
    {
        $this->smtp->setServer('localhost', "99999", null)
            ->setAuth('none', 'none');
        $message = new Message();
        $message->setFrom('You', 'nobody@nowhere.no')
            ->addTo('Them', 'them@nowhere.no')
            ->setSubject('This is a test')
            ->setBody('This is a test part two');

        $this->smtp->send($message);
        usleep(self::DELAY);
    }


}
