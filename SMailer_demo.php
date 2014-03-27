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

$mail->setAuth('you@example.com', 'password');
$mail->setFrom('You', 'you@example.com');
// $mail->setFakeFrom('A Fake Name', 'a_fake_email@xxx.com'); // if you want use fake from then extra add this line

// Repleat for more people, name must be unique
$mail->setTo('Tom', 'tom@gmail.com');
$mail->setTo('Jerry', 'jerry@gmail.com');

$mail->setSubject('Test');
$mail->setBody('Hi, I <strong>love</strong> you.');

// Repleat for more attachment, name must be unique
$mail->setAttachment('love.png', '/tmp/hello.png');
$mail->setAttachment('you.png', '/tmp/world.png');

var_dump($mail->send());

