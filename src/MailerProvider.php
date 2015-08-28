<?php

namespace Laasti\Mailer;

class MailerProvider extends \League\Container\ServiceProvider
{

    protected $provides = [
        'Laasti\Mailer\Message',
        'Laasti\Mailer\Mailer',
        'Laasti\Mailer\ServerInterface',
    ];

    public function register()
    {
        $di = $this->getContainer();

        $config = $di['config.mailer'];
        if (!isset($config['arguments'])) {
            $config['arguments'] = [];
        }

        $di->add('Laasti\Mailer\Message');
        $di->add('Laasti\Mailer\ServerInterface', $config['server'])->withArguments($config['arguments']);
        $di->add('Laasti\Mailer\Mailer', null, true)->withArguments(['Laasti\Mailer\ServerInterface']);
    }
}
