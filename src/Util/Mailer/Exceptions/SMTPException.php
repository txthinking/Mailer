<?php
/**
 * Created by PhpStorm.
 * User: msowers
 * Date: 3/30/15
 * Time: 1:51 PM
 */

namespace Tx\Util\Mailer\Exceptions;


use Monolog\Logger;

class SMTPException extends \Exception
{
    /**
     * @var Logger $logger
     */
    protected static $logger;

    public function __construct($message)
    {
        parent::__construct($message);
        if(self::$logger !== null) {
            self::$logger->addDebug("Exception triggered : " . $this->getMessage());
        }
    }

    public static function setLogger(Logger $logger)
    {
        self::$logger = $logger;
    }

}
