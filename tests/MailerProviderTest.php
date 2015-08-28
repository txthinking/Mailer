<?php

use Laasti\Mailer\MailerProvider;
use League\Container\Container;


class MailerProviderTest extends  \PHPUnit_Framework_TestCase {


    public function testProvider()
    {
        $container = new Container;
        $container->addServiceProvider(new MailerProvider);
        $container['config.mailer'] = ['server' => 'Laasti\Mailer\Servers\NullServer'];
        $container->get('Laasti\Mailer\Mailer');
    }

}

