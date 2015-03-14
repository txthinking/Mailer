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

$mail = new Mailer();
$mail->setServer('smtp.ym.163.com', 25);
$mail->setAuth('', ''); // email, password
$mail->setFrom('You', ''); //your name, your email
$mail->setTo('Cloud', 'cloud@txthinking.com');
$mail->setSubject('Test Mailer');
$mail->setBody('Hi, I <strong>love</strong> you.');
$r = $mail->send();
var_dump($r);
```
