<?php
/**
* @file SMailer_demo.php
* @brief
* @author cloud@txthinking.com
* @version 0.0.1
* @date 2012-07-25
 */

require "SMailer.php";

$mail = new SMailer();

$mail->setServer('smtp.example.com', 25); // no ssl
//$mail->setServer('smtp.txthinking.com', 465, 'ssl'); // use ssl
$mail->setAuth('you@example.com', 'aaaaaaaa');
$mail->setFrom('You', 'you@example.com');

$mail->setTo('Tom', 'tom@gmail.com'); // you can repleat this for send to more people
$mail->setTo('Jerry', 'jerry@gmail.com');

$mail->setSubject('Test');
$mail->setBody('Hi, I love you.');

$mail->setAttachment('love.png', '/tmp/hello.png'); // you can repleat this for add more attachment
$mail->setAttachment('you.png', '/tmp/world.png');

$mail->setHtml(true);

var_dump($mail->send());
