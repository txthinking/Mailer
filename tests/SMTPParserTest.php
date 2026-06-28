<?php

namespace Tx\Mailer\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Tx\Mailer\SMTP;
use Tx\Mailer\Exceptions\SMTPException;

/**
 * Unit tests for SMTP::getCode() / readLine() reply parsing (no network).
 *
 * The parser reads from the protected $smtp socket, so each test wires a real
 * SMTP instance to an in-memory stream and invokes the protected getCode() via
 * reflection — that keeps us testing the real implementation without a helper
 * subclass and without stubbing anything away.
 */
class SMTPParserTest extends BaseTestCase
{
    /**
     * Build a real SMTP whose socket is an in-memory stream preloaded with the
     * given reply, and return: a callable that invokes the protected getCode(),
     * the stream itself (to assert how much was consumed), and a callable that
     * returns the protected resultStack (the reassembled replies CodeException
     * reports on failure).
     *
     * @return array{0: callable, 1: resource, 2: callable}
     */
    private function smtpReading($reply)
    {
        $fp = fopen('php://memory', 'r+');
        fwrite($fp, $reply);
        rewind($fp);

        $smtp = new SMTP();

        $prop = new \ReflectionProperty(SMTP::class, 'smtp');
        $getCode = new \ReflectionMethod(SMTP::class, 'getCode');
        $stack = new \ReflectionProperty(SMTP::class, 'resultStack');

        // setAccessible() is a no-op since PHP 8.1 and a deprecation as of 8.5,
        // so only call it on the older PHP versions where it is still required.
        if (PHP_VERSION_ID < 80100) {
            $prop->setAccessible(true);
            $getCode->setAccessible(true);
            $stack->setAccessible(true);
        }

        $prop->setValue($smtp, $fp);

        $invoke = function () use ($getCode, $smtp) {
            return $getCode->invoke($smtp);
        };
        $resultStack = function () use ($stack, $smtp) {
            return $stack->getValue($smtp);
        };

        return array($invoke, $fp, $resultStack);
    }

    public function testSingleLineReply()
    {
        list($getCode) = $this->smtpReading("250 OK\r\n");
        $this->assertSame('250', $getCode());
    }

    /**
     * Every continuation line is consumed, leaving the stream at EOF so the
     * next command reads its own reply.
     */
    public function testMultiLineReplyIsFullyConsumed()
    {
        list($getCode, $fp) = $this->smtpReading(
            "250-mx.example.com\r\n" .
            "250-PIPELINING\r\n" .
            "250-SIZE 1000000\r\n" .
            "250 8BITMIME\r\n"
        );
        $this->assertSame('250', $getCode());
        $this->assertFalse(fgets($fp));
    }

    /**
     * A multi-line reply is recorded as a single reassembled resultStack entry,
     * keeping resultStack aligned with commandStack (one result per command).
     */
    public function testMultiLineReplyIsReassembledIntoOneStackEntry()
    {
        $reply =
            "250-mx.example.com\r\n" .
            "250-PIPELINING\r\n" .
            "250-SIZE 1000000\r\n" .
            "250 8BITMIME\r\n";
        list($getCode, , $resultStack) = $this->smtpReading($reply);
        $this->assertSame('250', $getCode());

        $stack = $resultStack();
        $this->assertCount(1, $stack);
        $this->assertSame($reply, $stack[0]);
    }

    /**
     * The whole multi-line reply is preserved in the single resultStack entry
     * callers pop for CodeException, so an explanatory line survives alongside
     * the final one.
     */
    public function testMultiLineErrorReplyKeepsExplanatoryText()
    {
        $reply =
            "535-5.7.8 Username and Password not accepted.\r\n" .
            "535 5.7.8 https://support.google.com/mail/?p=BadCredentials\r\n";
        list($getCode, , $resultStack) = $this->smtpReading($reply);
        $this->assertSame('535', $getCode());

        $stack = $resultStack();
        $popped = array_pop($stack);
        $this->assertSame($reply, $popped);
        $this->assertStringContainsString('Username and Password not accepted', $popped);
    }

    /**
     * A reply line longer than readLine()'s fgets() chunk is reassembled
     * byte-for-byte instead of being truncated, leaving nothing buffered.
     */
    public function testLongLineIsNotTruncated()
    {
        // > the 1024-byte fgets() chunk, so reassembly must stitch >1 chunk
        $line = "250 " . str_repeat("X", 2000) . "\r\n";
        list($getCode, $fp, $resultStack) = $this->smtpReading($line);
        $this->assertSame('250', $getCode());
        $captured = $resultStack();
        $this->assertSame($line, $captured[0]);
        $this->assertFalse(fgets($fp));
    }

    /**
     * After a line longer than the fgets() chunk is reassembled, the stream is
     * left at the start of the next reply, so the following command reads its
     * own code.
     */
    public function testTwoRepliesStayInSyncAfterLongLine()
    {
        // first line > the 1024-byte fgets() chunk, forcing reassembly
        $first = "250 " . str_repeat("X", 2000) . "\r\n";
        list($getCode) = $this->smtpReading($first . "354 go ahead\r\n");
        $this->assertSame('250', $getCode());
        $this->assertSame('354', $getCode());
    }

    public function testBareCodeLine()
    {
        list($getCode) = $this->smtpReading("220\r\n");
        $this->assertSame('220', $getCode());
    }

    public function testEmptyReplyThrows()
    {
        list($getCode) = $this->smtpReading("");
        $this->expectException(SMTPException::class);
        $getCode();
    }

    public function testGarbageReplyThrows()
    {
        list($getCode) = $this->smtpReading("not an smtp reply\r\n");
        $this->expectException(SMTPException::class);
        $getCode();
    }
}
