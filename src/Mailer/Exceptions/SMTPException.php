<?php
/**
 * Created by PhpStorm.
 * User: msowers
 * Date: 3/30/15
 * Time: 1:51 PM
 */

namespace Tx\Mailer\Exceptions;

class SMTPException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }

}
