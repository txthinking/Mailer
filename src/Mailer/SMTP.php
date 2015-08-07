<?php
/***************************************************\
 *
 *  Mailer (https://github.com/txthinking/Mailer)
 *
 *  A lightweight PHP SMTP mail sender.
 *  Implement RFC0821, RFC0822, RFC1869, RFC2045, RFC2821
 *
 *  Support html body, don't worry that the receiver's
 *  mail client can't support html, because Mailer will
 *  send both text/plain and text/html body, so if the
 *  mail client can't support html, it will display the
 *  text/plain body.
 *
 *  Create Date 2012-07-25.
 *  Under the MIT license.
 *
 \***************************************************/

namespace Tx\Mailer;

use Psr\Log\LoggerInterface;
use Tx\Mailer\Exceptions\CodeException;
use Tx\Mailer\Exceptions\CryptoException;
use Tx\Mailer\Exceptions\SMTPException;

class SMTP
{
    /**
     * smtp socket
     */
    protected $smtp;

    /**
     * smtp server
     */
    protected $host;

    /**
     * smtp server port
     */
    protected $port;

    /**
     * smtp secure ssl tls
     */
    protected $secure;

    /**
     * EHLO message
     */
    protected $ehlo;

    /**
     * smtp username
     */
    protected $username;

    /**
     * smtp password
     */
    protected $password;

    /**
     * $this->CRLF
     * @var string
     */
    protected $CRLF = "\r\n";

    /**
     * @var Message
     */
    protected $message;

    /**
     * @var LoggerInterface - Used to make things prettier than self::$logger
     */
    protected $logger;

    /**
     * Stack of all commands issued to SMTP
     * @var array
     */
    protected $commandStack = array();

    /**
     * Stack of all results issued to SMTP
     * @var array
     */
    protected $resultStack = array();

    public function __construct(LoggerInterface $logger=null)
    {
        $this->logger = $logger;
    }

    /**
     * set server and port
     * @param string $host server
     * @param int $port port
     * @param string $secure ssl tls
     * @return $this
     */
    public function setServer($host, $port, $secure=null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->secure = $secure;
        if(!$this->ehlo) $this->ehlo = $host;
        $this->logger && $this->logger->debug("Set: the server");
        return $this;
    }

    /**
     * auth with server
     * @param string $username
     * @param string $password
     * @return $this
     */
    public function setAuth($username, $password){
        $this->username = $username;
        $this->password = $password;
        $this->logger && $this->logger->debug("Set: the auth");
        return $this;
    }

    /**
     * set the EHLO message
     * @param $ehlo
     * @return $this
     */
    public function setEhlo($ehlo){
        $this->ehlo = $ehlo;
        return $this;
    }

    /**
     * Send the message
     *
     * @param Message $message
     * @return $this
     * @throws CodeException
     * @throws CryptoException
     * @throws SMTPException
     */
    public function send(Message $message){
        $this->logger && $this->logger->debug('Set: a message will be sent');
        $this->message = $message;
        $this->connect()
            ->ehlo();

        if ($this->secure === 'tls'){
            $this->starttls()
                ->ehlo();
        }
        $this->authLogin()
            ->mailFrom()
            ->rcptTo()
            ->data()
            ->quit();
        return fclose($this->smtp);
    }

    /**
     * connect the server
     * SUCCESS 220
     * @return $this
     * @throws CodeException
     * @throws SMTPException
     */
    protected function connect(){
        $this->logger && $this->logger->debug("Connecting to {$this->host} at {$this->port}");
        $host = ($this->secure == 'ssl') ? 'ssl://' . $this->host : $this->host;
        $this->smtp = @fsockopen($host, $this->port);
        //set block mode
        //    stream_set_blocking($this->smtp, 1);
        if (!$this->smtp){
            throw new SMTPException("Could not open SMTP Port.");
        }
        $code = $this->getCode();
        if ($code !== '220'){
            throw new CodeException('220', $code, array_pop($this->resultStack));
        }
        return $this;
    }

    /**
     * SMTP STARTTLS
     * SUCCESS 220
     * @return $this
     * @throws CodeException
     * @throws CryptoException
     * @throws SMTPException
     */
    protected function starttls(){
        $in = "STARTTLS" . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '220'){
            throw new CodeException('220', $code, array_pop($this->resultStack));
        }
        if(!stream_socket_enable_crypto($this->smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new CryptoException("Start TLS failed to enable crypto");
        }
        return $this;
    }

    /**
     * SMTP EHLO
     * SUCCESS 250
     * @return $this
     * @throws CodeException
     * @throws SMTPException
     */
    protected function ehlo(){
        $in = "EHLO " . $this->ehlo . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '250'){
            throw new CodeException('250', $code, array_pop($this->resultStack));
        }
        return $this;
    }

    /**
     * SMTP AUTH LOGIN
     * SUCCESS 334
     * SUCCESS 334
     * SUCCESS 235
     * @return $this
     * @throws CodeException
     * @throws SMTPException
     */
    protected function authLogin()
    {
        if ($this->username === null && $this->password === null) {
            // Unless the user has specifically set a username/password
            // Do not try to authorize.
            return $this;
        }

        $in = "AUTH LOGIN" . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '334'){
            throw new CodeException('334', $code, array_pop($this->resultStack));
        }
        $in = base64_encode($this->username) . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '334'){
            throw new CodeException('334', $code, array_pop($this->resultStack));
        }
        $in = base64_encode($this->password) . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '235'){
            throw new CodeException('235', $code, array_pop($this->resultStack));
        }
        return $this;
    }

    /**
     * SMTP MAIL FROM
     * SUCCESS 250
     * @return $this
     * @throws CodeException
     * @throws SMTPException
     */
    protected function mailFrom(){
        $in = "MAIL FROM:<{$this->message->getFromEmail()}>" . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '250') {
            throw new CodeException('250', $code, array_pop($this->resultStack));
        }
        return $this;
    }

    /**
     * SMTP RCPT TO
     * SUCCESS 250
     * @return $this
     * @throws CodeException
     * @throws SMTPException
     */
    protected function rcptTo(){
        foreach ($this->message->getTo() as $toEmail) {
            $in = "RCPT TO:<" . $toEmail . ">" . $this->CRLF;
            $code = $this->pushStack($in);
            if ($code !== '250') {
                throw new CodeException('250', $code, array_pop($this->resultStack));
            }
        }
        return $this;
    }

    /**
     * SMTP DATA
     * SUCCESS 354
     * SUCCESS 250
     * @return $this
     * @throws CodeException
     * @throws SMTPException
     */
    protected function data(){
        $in = "DATA" . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '354') {
            throw new CodeException('354', $code, array_pop($this->resultStack));
        }
        $in = $this->message->toString();
        $code = $this->pushStack($in);
        if ($code !== '250'){
            throw new CodeException('250', $code, array_pop($this->resultStack));
        }
        return $this;
    }

    /**
     * SMTP QUIT
     * SUCCESS 221
     * @return $this
     * @throws CodeException
     * @throws SMTPException
     */
    protected function quit(){
        $in = "QUIT" . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '221'){
            throw new CodeException('221', $code, array_pop($this->resultStack));
        }
        return $this;
    }

    protected function pushStack($string)
    {
        $this->commandStack[] = $string;
        fputs($this->smtp, $string, strlen($string));
        $this->logger && $this->logger->debug('Sent: '. $string);
        return $this->getCode();
    }

    /**
     * get smtp response code
     * once time has three digital and a space
     * @return string
     * @throws SMTPException
     */
    protected function getCode() {
        while ($str = fgets($this->smtp, 515)) {
            $this->logger && $this->logger->debug("Got: ". $str);
            $this->resultStack[] = $str;
            if(substr($str,3,1) == " ") {
                $code = substr($str,0,3);
                return $code;
            }
        }
        throw new SMTPException("SMTP Server did not respond with anything I recognized");
    }

}

