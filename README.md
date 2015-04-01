Mailer
=======

A lightweight SMTP mail sender

### Install

```
$ composer require txthinking/mailer
```

### Usage

```
<?php
use \Tx\Mailer;
use \Tx\Mailer\SMTP;
use \Tx\Mailer\Message;
use \Monolog\Logger;

try {
    // set logger to receive debug log
    $logger = new Logger('Mailer');
    $smtp = new SMTP($logger);
    // or not
    $smtp = new SMTP();

    $smtp->setServer('smtp.ym.163.com', 25)
        ->setAuth('', ''); // email, password

    $message = new Message();
    $message->setFrom('Tom', '') // your name, your email
        ->setTo('Cloud', 'cloud@txthinking.com')
        ->setSubject('hi')
        ->setBody('for test');

    $status = $smtp->send($message);
    var_dump($status);
} catch (\Exception $e) {
    // error
}
```
OR
```
<?php
use Tx\Mailer;

$r = (new Mailer())
    ->setServer('smtp.ym.163.com', 25)
    ->setAuth('', '') // email, password
    ->setFrom('You', '') //your name, your email
    ->setTo('Cloud', 'cloud@txthinking.com')
    ->setSubject('Test Mailer')
    ->setBody('Hi, I <strong>love</strong> you.')
    ->send();
var_dump($r);
```
