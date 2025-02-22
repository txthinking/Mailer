<?php

namespace Tx\Tests;

use Tx\Mailer\Message;

class MessageTest extends TestCase
{
    protected $message;

    protected function setUp(): void
    {
        parent::setUp();
        $this->message = new Message();
    }

    public function testSetFrom()
    {
        $this->message->setFrom('Sender Name', 'sender@example.com');
        $this->assertEquals('Sender Name', $this->message->getFromName());
        $this->assertEquals('sender@example.com', $this->message->getFromEmail());
    }

    public function testSetFakeFrom()
    {
        $this->message->setFakeFrom('Fake Name', 'fake@example.com');
        $this->assertEquals('Fake Name', $this->message->getFakeFromName());
        $this->assertEquals('fake@example.com', $this->message->getFakeFromEmail());
    }

    public function testAddTo()
    {
        $this->message->addTo('Recipient', 'recipient@example.com');
        $to = $this->message->getTo();
        $this->assertArrayHasKey('recipient@example.com', $to);
        $this->assertEquals('Recipient', $to['recipient@example.com']);
    }

    public function testAddCc()
    {
        $this->message->addCc('CC Recipient', 'cc@example.com');
        $cc = $this->message->getCc();
        $this->assertArrayHasKey('cc@example.com', $cc);
        $this->assertEquals('CC Recipient', $cc['cc@example.com']);
    }

    public function testAddBcc()
    {
        $this->message->addBcc('BCC Recipient', 'bcc@example.com');
        $bcc = $this->message->getBcc();
        $this->assertArrayHasKey('bcc@example.com', $bcc);
        $this->assertEquals('BCC Recipient', $bcc['bcc@example.com']);
    }

    public function testSetSubject()
    {
        $subject = 'Test Subject';
        $this->message->setSubject($subject);
        $this->assertEquals($subject, $this->message->getSubject());
    }

    public function testSetBody()
    {
        $body = 'Test email body content';
        $this->message->setBody($body);
        $this->assertEquals($body, $this->message->getBody());
    }

    public function testSetReplyTo()
    {
        $this->message->setReplyTo('Reply Name', 'reply@example.com');
        $messageString = $this->message->toString();
        $this->assertStringContainsString('Reply-To: =?utf-8?B?' . base64_encode('Reply Name') . '?= <reply@example.com>', $messageString);
    }

    public function testToString()
    {
        $this->message
            ->setFrom('Sender', 'sender@example.com')
            ->setSubject('Test Subject')
            ->setBody('Test Body')
            ->addTo('Recipient', 'recipient@example.com');

        $messageString = $this->message->toString();

        $this->assertStringContainsString('From: =?utf-8?B?' . base64_encode('Sender') . '?= <sender@example.com>', $messageString);
        $this->assertStringContainsString('To: =?utf-8?B?' . base64_encode('Recipient') . '?= <recipient@example.com>', $messageString);
        $this->assertStringContainsString('Subject: =?utf-8?B?' . base64_encode('Test Subject') . '?=', $messageString);
        $this->assertStringContainsString(chunk_split(base64_encode('Test Body')), $messageString);
    }
}
