<?php

/**
 * Configures the SMTP server used for testing
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    /** SMTP server to test against */
    const SERVER = 'mailtrap.io';
    /** plain text port */
    const PORT = 25;
    /** TLS port */
    const PORT_TLS = 25;
    /** SSL port (not supported by mailtrap currently */
    const PORT_SSL = 25;
    /** user for LOGIN auth */
    const USER = '4139926c57fd07bf5';
    /** password for LOGIN auth */
    const PASS = '1b214cf5f3874c';
    /** name of sender */
    const FROM_NAME = 'mailer';
    /** email of sender */
    const FROM_EMAIL = 'bot@mail.txthinking.com';
    /** name of receiver */
    const TO_NAME = 'Cloud';
    /** email of receiver */
    const TO_EMAIL = 'cloud@txthinking.com';
    /** delay in microsends between SMTP tests to avoid API limits (we're allowed two messages/second) */
    const DELAY = 500000; // half a second
}

