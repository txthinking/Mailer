<?php
/**
 * Created by PhpStorm.
 * User: msowers
 * Date: 3/31/15
 * Time: 10:57 AM
 */

namespace Tests;


use Tx\Util\Mailer\SMTP;


/**
 * Class SMTPTest
 * @package Tests
 *
 * This test set requires the use of an open SMTP server mock.  I'm still looking for something reliable to
 * provide SMTP testing without unleashing an open relay on the internet.
 */
class SMTPTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var SMTP
     */
    protected $smtp;

    /**
     * @var \ReflectionClass
     */
    protected $reflect;

    public function setup()
    {
        $this->smtp = new SMTP();
        $this->reflect = new \ReflectionClass($this->smtp);

    }

    public function testSetServer()
    {
        $result = $this->smtp->setServer("localhost", "25", null);

        $hProp = $this->reflect->getProperty('host');
        $hProp->setAccessible(true);
        $pProp = $this->reflect->getProperty('port');
        $pProp->setAccessible(true);

        $this->assertEquals('localhost', $hProp->getValue($this->smtp));
        $this->assertEquals('25', $pProp->getValue($this->smtp));
        $this->assertSame($this->smtp, $result);
    }

    public function testSetAuth()
    {
        $result = $this->smtp->setAuth('none', 'none');

        $uProp = $this->reflect->getProperty('username');
        $uProp->setAccessible(true);
        $pProp = $this->reflect->getProperty('password');
        $pProp->setAccessible(true);

        $this->assertEquals('none', $uProp->getValue($this->smtp));
        $this->assertEquals('none', $pProp->getValue($this->smtp));
        $this->assertSame($this->smtp, $result);
    }


}
