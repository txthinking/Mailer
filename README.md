SMailer
=======

A lightweight SMTP mail sender

```
require "SMailer.php";
$mail = new SMailer();

$mail->setServer('smtp.example.com', 25);
$mail->setAuth('you@example.com', 'password');
$mail->setFrom('You', 'you@example.com');
// $mail->setFakeFrom('A Fake Name', 'a_fake_email@xxx.com'); // if you want use fake from then extra add this line
$mail->setTo('Tom', 'tom@gmail.com');

$mail->setSubject('Test');
$mail->setBody('Hi, I love you.');
$mail->setAttachment('you.png', '/tmp/world.png');

var_dump($mail->send());
```
