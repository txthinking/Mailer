<?php

/**
 * Configures the SMTP server used for testing
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    /** SMTP server to test against */
    const SERVER = 'smtp.mailtrap.io';
    /** plain text port */
    const PORT = 2525;
    /** TLS port */
    const PORT_TLS = 465;
    /** SSL port (not supported by mailtrap currently */
    const PORT_SSL = 0;
    /** user for LOGIN auth */
    const USER = 'e3f534cfe656f4';
    /** password for LOGIN auth */
    const PASS = 'b6e38ddc0f1e9d';

    /** from */
    const FROM_NAME = 'Mailer';
    const FROM_EMAIL = '739f35c64d-9422d2@inbox.mailtrap.io';
    /** to */
    const TO_NAME = 'Test To';
    const TO_EMAIL = 'cloud@txthinking.com';
    /** cc */
    const CC_NAME = 'Test Cc';
    const CC_EMAIL = 'cloud+cc@txthinking.com';
    /** bcc */
    const BCC_NAME = 'Test Bcc';
    /** email of receiver */
    const BCC_EMAIL = 'cloud+bcc@txthinking.com';

    /** delay in microsends between SMTP tests to avoid API limits (we're allowed two messages/second) */
    const DELAY = 500000; // half a second

    const  OAUTH_SERVER = 'smtp.gmail.com';
    const  OAUTH_PORT = '587';
    const  OAUTH_TOKEN = '';
    const  OAUTH_FROM_NAME = 'Cloud';
    const  OAUTH_FROM_EMAIL = 'cloud@txthinking.com';
}

