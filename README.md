Mailer [![Build Status](https://api.travis-ci.org/txthinking/Mailer.svg?branch=master)](https://travis-ci.org/txthinking/Mailer)
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
    ->setServer('smtp.server.com', 25)
    ->setAuth('tom@server.com', 'password')
    ->setFrom('Tom', 'tom@server.com')
    ->setFakeFrom('Obama', 'fake@address.com') // if u want, a fake name, a fake email
    ->addTo('Jerry', 'jerry@server.com')
    ->setSubject('Hello')
    ->setBody('Hi, Jerry! I <strong>love</strong> you.')
    ->addAttachment('host', '/etc/hosts')
    ->send();
var_dump($ok);
```
More [Example](https://github.com/txthinking/Mailer/tree/master/tests)

