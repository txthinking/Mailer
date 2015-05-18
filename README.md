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
use Tx\Mailer;

$ok = (new Mailer())
    ->setServer('smtp.ym.163.com', 25)
    ->setAuth('', '') // email, password
    ->setFrom('You', '') //your name, your email
    ->setFakeFrom('heelo', 'bot@fake.com') // if u want, a fake name, a fake email
    ->addTo('Cloud', 'cloud@txthinking.com')
    ->setSubject('Test Mailer')
    ->setBody('Hi, I <strong>love</strong> you.')
    ->addAttachment('host', '/etc/hosts')
    ->send();
var_dump($ok);
```
OR
```
<?php
use \Tx\Mailer\SMTP;
use \Tx\Mailer\Message;
use \Monolog\Logger;

$smtp = new SMTP(); // new SMTP(new Logger('Mailer')); # set logger to receive debug log
$smtp->setServer('smtp.ym.163.com', 25)
    ->setAuth('bot@ym.txthinking.com', ''); // email, password

$message = new Message();
$message->setFrom('Tom', 'your@mail.com') // your name, your email
    ->setFakeFrom('heelo', 'bot@fake.com') // if u want, a fake name, a fake email
    ->addTo('Cloud', 'cloud@txthinking.com')
    ->setSubject('Test Mailer')
    ->setBody('<h1>For test</h1>')
    ->addAttachment('host', '/etc/hosts');

$ok = $smtp->send($message);
var_dump($ok);
```
