<?php

namespace Tx\Tests;

use PHPUnit\Framework\TestCase;
use Tx\Mailer\Message;

class MessageTest extends TestCase
{
    protected $message;

    protected function setUp(): void
    {
        $this->message = new Message();
    }

    public function testSetFrom()
    {
        $name = 'Sender Name';
        $email = 'sender@example.com';
        $expectedHeader = 'From: =?utf-8?B?U2VuZGVyIE5hbWU=?= <sender@example.com>';

        $this->message->setFrom($name, $email);

        // Test getters
        $this->assertEquals($name, $this->message->getFromName());
        $this->assertEquals($email, $this->message->getFromEmail());

        // Test toString output
        $messageString = $this->message->toString();
        $this->assertStringContainsString($expectedHeader, $messageString);
    }

    public function testSetFakeFrom()
    {
        $name = 'Fake Name';
        $email = 'fake@example.com';
        $expectedHeader = 'From: =?utf-8?B?RmFrZSBOYW1l?= <fake@example.com>';

        $this->message->setFakeFrom($name, $email);

        // Test getters
        $this->assertEquals($name, $this->message->getFakeFromName());
        $this->assertEquals($email, $this->message->getFakeFromEmail());

        // Test toString output
        $messageString = $this->message->toString();
        $this->assertStringContainsString($expectedHeader, $messageString);
    }

    public function testAddTo()
    {
        $name = 'Recipient';
        $email = 'recipient@example.com';
        $expectedHeader = 'To: =?utf-8?B?UmVjaXBpZW50?= <recipient@example.com>';

        $this->message->addTo($name, $email);

        // Test getter
        $to = $this->message->getTo();
        $this->assertArrayHasKey($email, $to);
        $this->assertEquals($name, $to[$email]);

        // Test toString output
        $messageString = $this->message->toString();
        $this->assertStringContainsString($expectedHeader, $messageString);
    }

    public function testAddCc()
    {
        $name = 'CC Recipient';
        $email = 'cc@example.com';
        $expectedHeader = 'Cc: =?utf-8?B?Q0MgUmVjaXBpZW50?= <cc@example.com>';

        $this->message->addCc($name, $email);

        // Test getter
        $cc = $this->message->getCc();
        $this->assertArrayHasKey($email, $cc);
        $this->assertEquals($name, $cc[$email]);

        // Test toString output
        $messageString = $this->message->toString();
        $this->assertStringContainsString($expectedHeader, $messageString);
    }

    public function testAddBcc()
    {
        $name = 'BCC Recipient';
        $email = 'bcc@example.com';
        $expectedHeader = 'Bcc: =?utf-8?B?QkNDIFJlY2lwaWVudA==?= <bcc@example.com>';

        $this->message->addBcc($name, $email);

        // Test getter
        $bcc = $this->message->getBcc();
        $this->assertArrayHasKey($email, $bcc);
        $this->assertEquals($name, $bcc[$email]);

        // Test toString output
        $messageString = $this->message->toString();
        $this->assertStringContainsString($expectedHeader, $messageString);
    }

    public function testSetSubject()
    {
        $subject = 'Test Subject with Special Chars: äöü';

        $this->message->setSubject($subject);

        // Test getter
        $this->assertEquals($subject, $this->message->getSubject());

        // Test toString output
        $messageString = $this->message->toString();
        $expectedHeader = 'Subject: =?utf-8?B?VGVzdCBTdWJqZWN0IHdpdGggU3BlY2lhbCBDaGFyczogw6TDtsO8?=';
        $this->assertStringContainsString($expectedHeader, $messageString);
    }

    public function testSetBody()
    {
        $body = 'Test email body content with special chars: äöü';

        $this->message->setBody($body);

        // Test getter
        $this->assertEquals($body, $this->message->getBody());

        // Test toString output
        $messageString = $this->message->toString();
        $expectedBody = "VGVzdCBlbWFpbCBib2R5IGNvbnRlbnQgd2l0aCBzcGVjaWFsIGNoYXJzOiDDpMO2w7w=\r\n";
        $this->assertStringContainsString($expectedBody, $messageString);
    }

    public function testSetReplyTo()
    {
        $name = 'Reply Name';
        $email = 'reply@example.com';
        $expectedHeader = 'Reply-To: =?utf-8?B?UmVwbHkgTmFtZQ==?= <reply@example.com>';

        $this->message->setReplyTo($name, $email);

        // Test toString output
        $messageString = $this->message->toString();
        $this->assertStringContainsString($expectedHeader, $messageString);
    }

    public function testAddAttachment()
    {
        $name = 'test.txt';
        $path = tempnam(sys_get_temp_dir(), 'test_');


        // Create a test file
        file_put_contents($path, 'Test content');

        $this->message->addAttachment($name, $path);

        // Test getter
        $attachments = $this->message->getAttachment();
        $this->assertArrayHasKey($name, $attachments);
        $this->assertEquals($path, $attachments[$name]);

        // Test toString output
        $messageString = $this->message->toString();
        $this->assertStringContainsString(
            'Content-Type: application/octet-stream; name="' . $name . '"',
            $messageString
        );
        $this->assertStringContainsString(
            'Content-Disposition: attachment; filename="' . $name . '"',
            $messageString
        );

        // Cleanup
        unlink($path);
    }

    public function testCompleteMessage()
    {
        $fromName = 'Sender';
        $fromEmail = 'sender@example.com';
        $toName = 'Recipient';
        $toEmail = 'recipient@example.com';
        $subject = 'Complete Test';
        $body = 'Complete test body';
        $expectedHeaderFrom = 'From: =?utf-8?B?U2VuZGVy?= <sender@example.com>';
        $expectedHeaderTo = 'To: =?utf-8?B?UmVjaXBpZW50?= <recipient@example.com>';
        $expectedHeaderSubject = 'Subject: =?utf-8?B?Q29tcGxldGUgVGVzdA==?=';
        $expectedBody = "Q29tcGxldGUgdGVzdCBib2R5\r\n";

        $this->message
            ->setFrom($fromName, $fromEmail)
            ->addTo($toName, $toEmail)
            ->setSubject($subject)
            ->setBody($body);

        $messageString = $this->message->toString();

        // Verify all parts are present and properly encoded
        
        $this->assertStringContainsString($expectedHeaderFrom, $messageString);
        $this->assertStringContainsString($expectedHeaderTo, $messageString);
        $this->assertStringContainsString($expectedHeaderSubject, $messageString);
        $this->assertStringContainsString($expectedBody, $messageString);
        $this->assertStringContainsString('MIME-Version: 1.0', $messageString);
        $this->assertStringContainsString('Content-Type: multipart/alternative', $messageString);
    }
}
