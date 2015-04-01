<?php
/**
 * Created by PhpStorm.
 * User: msowers
 * Date: 3/31/15
 * Time: 10:57 AM
 */

namespace Tests;


use Tx\Util\Mailer\SMTP;
use Tx\Util\Mailer\Message;
use ERB\Testing\Tools\TestHelper;

/**
 * Class SMTPTest
 * @package Tests
 *
 * This test set requires the use of an open SMTP server mock.  Currently, I'm using FakeSMTPServer
 *
 */
class SMTPTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var SMTP
     */
    protected $smtp;

    /**
     * @var TestHelper
     */
    protected $testHelper;

    public function setup()
    {
        $this->smtp = new SMTP();
        $this->testHelper = new TestHelper();

    }

    public function testSetServer()
    {
        $result = $this->smtp->setServer("localhost", "25", null);
        $this->assertEquals('localhost', $this->testHelper->getPropertyValue($this->smtp, 'host'));
        $this->assertEquals('25', $this->testHelper->getPropertyValue($this->smtp, 'port'));
        $this->assertSame($this->smtp, $result);
    }

    public function testSetAuth()
    {
        $result = $this->smtp->setAuth('none', 'none');

        $this->assertEquals('none', $this->testHelper->getPropertyValue($this->smtp, 'username'));
        $this->assertEquals('none', $this->testHelper->getPropertyValue($this->smtp, 'password'));
        $this->assertSame($this->smtp, $result);
    }

    public function testMessage()
    {
        $this->smtp->setServer("localhost", "25", null)
            ->setAuth('none', 'none');

        $message = new Message();
        $message->setFrom('You', 'nobody@nowhere.no')
            ->setTo('Them', 'them@nowhere.no')
            ->setSubject('This is a test')
            ->setBody('This is a test part two');

        $status = $this->smtp->send($message);
        $this->assertTrue($status);
    }


    /**
     * @expectedException \Tx\Util\Mailer\Exceptions\SMTPException
     */
    public function testConnectSMTPException()
    {
        $this->smtp->setServer("localhost", "99999", null)
            ->setAuth('none', 'none');
        $message = new Message();
        $message->setFrom('You', 'nobody@nowhere.no')
            ->setTo('Them', 'them@nowhere.no')
            ->setSubject('This is a test')
            ->setBody('This is a test part two');

        $this->smtp->send($message);

    }


}
