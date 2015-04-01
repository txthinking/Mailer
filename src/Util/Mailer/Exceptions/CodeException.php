<?php
/**
 * Created by PhpStorm.
 * User: msowers
 * Date: 3/30/15
 * Time: 2:42 PM
 */

namespace Tx\Util\Mailer\Exceptions;


class CodeException extends SMTPException
{
    public function __construct($expected, $received, $serverMessage = null)
    {
        $message = "Unexpected return code - Expected: {$expected}, Got: {$received}";
        if (isset($serverMessage)) {
            $message .= " | " . $serverMessage;
        }
        parent::__construct($message);
    }

}
