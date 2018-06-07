# Laasti/mailer
=======

A lightweight mail sender, credits to https://github.com/txthinking/Mailer for the inspiration and SMTP server.

## Installation

```
$ composer require laasti/mailer
```

## Usage

```php
<?php
use Laasti\Mailer;
$server = new Servers\SMTP($logger, $host, $username, $password, $port);
$ok = (new Mailer($server))
    ->setFrom('You', '') //your name, your email
    ->setFakeFrom('heelo', 'bot@fake.com') // if u want, a fake name, a fake email
    ->addTo('Cloud', 'cloud@txthinking.com')
    ->setSubject('Test Mailer')
    ->setBody('Hi, I <strong>love</strong> you.')
    ->addAttachment('host', '/etc/hosts')
    ->setHeader('List-Unsubscribe', 'mailto:unsub@unsub@mydomain.com')
    ->send();
var_dump($ok);
```
OR
```php
<?php
use Laasti\Mailer\Servers\SMTP;
use \Laasti\Mailer\Message;
use \Monolog\Logger;

$server = new SMTP($logger, $host, $username, $password, $port);
$mailer = new Mailer($server);

$message = new Message();
$message->setFrom('Tom', 'your@mail.com') // your name, your email
    ->setFakeFrom('heelo', 'bot@fake.com') // if u want, a fake name, a fake email
    ->addTo('Cloud', 'cloud@txthinking.com')
    ->setSubject('Test Mailer')
    ->setBody('<h1>For test</h1>')
    ->addAttachment('host', '/etc/hosts');

$ok = $mailer->send($message);
var_dump($ok);
```
A number of servers are available: FileServer (prints message to file), Mail, NullServer (does nothing), SMTP, Sendmail


## Contributing

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request :D

## History

See CHANGELOG.md for more information.

## Credits

Author: Sonia Marquette (@nebulousGirl)

## License

Released under the MIT License. See LICENSE file.
