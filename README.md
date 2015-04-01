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
