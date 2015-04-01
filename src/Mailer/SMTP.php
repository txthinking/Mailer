<?php

namespace Tx\Mailer;

use Tx\Mailer\Exceptions\CodeException;
use Tx\Mailer\Exceptions\CryptoException;
use Tx\Mailer\Exceptions\SMTPException;
use Monolog\Logger;

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
     * smtp username
     */
    protected $username;

    /**
     * smtp password
     */
    protected $password;

    /**
     * charset
     */
    protected $charset = "UTF-8";

    /**
     * message header
     */
    protected $header = array();

    /**
     * header multipart boundaryMixed
     */
    protected $boundaryMixed;

    /**
     * header multipart alternative
     */
    protected $boundaryAlternative;

    /**
     * $this->CRLF
     * @var string
     */
    protected $CRLF = "\r\n";

    /**
     * @var Message
     */
    protected $mailMessage;

    /**
     * @var Logger
     */
    protected static $loggerStatic;

    /**
     * @var Logger - Used to make things prettier than self::$logger
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

    public function __construct(Logger $logger=null)
    {
        if ($logger === null) {
            $this->logger = new Logger('Mailer');
        }else{
            $this->logger = $logger;
        }
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
        $this->logger->addDebug("Setting the server");
        $this->host = $host;
        $this->port = $port;
        $this->secure = $secure;
        return $this;
    }

    /**
     * auth with server
     * @param string $username
     * @param string $password
     * @return $this
     */
    public function setAuth($username, $password){
        $this->logger->addDebug("Setting the auth");
        $this->username = $username;
        $this->password = $password;
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
        $this->logger->addDebug('Got the request to send the message');
        $this->mailMessage = $message;
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
        $this->logger->addDebug("Connecting to {$this->host} at {$this->port}");
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
        $code = $this->pushStack("STARTTLS");
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
        $in = "EHLO " . $this->host . $this->CRLF;
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
        $in = "MAIL FROM:<{$this->mailMessage->getFromEmail()}>" . $this->CRLF;
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
        foreach ($this->mailMessage->getTo() as $toName=>$toEmail) {
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
        $this->mailMessage->setBody(
            chunk_split(base64_encode($this->mailMessage->getBody()))
        );

        $in = '';
        $this->createHeader();
        foreach ($this->header as $key => $value) {
            $in .= $key . ': ' . $value . $this->CRLF;
        }

        $attachments = $this->mailMessage->getAttachment();
        if (empty($attachments)) {
            $in .= $this->createBody();
        } else {
            $in .= $this->createBodyWithAttachment();
        }
        $in .= $this->CRLF . $this->CRLF . "." . $this->CRLF;

        $code = $this->pushStack($in);
        if ($code !== '250'){
            throw new CodeException('250', $code, array_pop($this->resultStack));
        }
        return $this;
    }

    /**
     * @brief createBody create body
     *
     * @return string
     */
    protected function createBody(){
        $in = "";
        $in .= "Content-Type: multipart/alternative; boundary=\"$this->boundaryAlternative\"" . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . $this->CRLF;
        $in .= "Content-Type: text/plain; charset=\"" . $this->charset . "\"" . $this->CRLF;
        $in .= "Content-Transfer-Encoding: base64" . $this->CRLF;
        $in .= $this->CRLF;
        $in .= $this->mailMessage->getBody() . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . $this->CRLF;
        $in .= "Content-Type: text/html; charset=\"" . $this->charset ."\"" . $this->CRLF;
        $in .= "Content-Transfer-Encoding: base64" . $this->CRLF;
        $in .= $this->CRLF;
        $in .= $this->mailMessage->getBody() . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . "--" . $this->CRLF;
        return $in;
    }

    /**
     * @brief createBodyWithAttachment create body with attachment
     *
     * @return string
     */
    protected function createBodyWithAttachment(){
        $attachments = $this->mailMessage->getAttachment();
        $in = "";
        $in .= $this->CRLF;
        $in .= $this->CRLF;
        $in .= '--' . $this->boundaryMixed . $this->CRLF;
        $in .= "Content-Type: multipart/alternative; boundary=\"$this->boundaryAlternative\"" . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . $this->CRLF;
        $in .= "Content-Type: text/plain; charset=\"" . $this->charset . "\"" . $this->CRLF;
        $in .= "Content-Transfer-Encoding: base64" . $this->CRLF;
        $in .= $this->CRLF;
        $in .= $this->mailMessage->getBody() . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . $this->CRLF;
        $in .= "Content-Type: text/html; charset=\"" . $this->charset ."\"" . $this->CRLF;
        $in .= "Content-Transfer-Encoding: base64" . $this->CRLF;
        $in .= $this->CRLF;
        $in .= $this->mailMessage->getBody() . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . "--" . $this->CRLF;
        foreach ($attachments as $name => $path){
            $in .= $this->CRLF;
            $in .= '--' . $this->boundaryMixed . $this->CRLF;
            $in .= "Content-Type: application/octet-stream; name=\"". $name ."\"" . $this->CRLF;
            $in .= "Content-Transfer-Encoding: base64" . $this->CRLF;
            $in .= "Content-Disposition: attachment; filename=\"" . $name . "\"" . $this->CRLF;
            $in .= $this->CRLF;
            $in .= chunk_split(base64_encode(file_get_contents($path))) . $this->CRLF;
        }
        $in .= $this->CRLF;
        $in .= $this->CRLF;
        $in .= '--' . $this->boundaryMixed . '--' . $this->CRLF;
        return $in;
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

    /**
     * Create mail header
     * @return $this
     */
    protected function createHeader(){
        $this->header['Date'] = date('r');

        if(!$this->mailMessage->hasFakeFrom()){
            $this->header['Return-Path'] = $this->mailMessage->getFakeFromEmail();
            $this->header['From'] = $this->mailMessage->getFakeFromName() . " <" . $this->mailMessage->getFakeFromEmail() . ">";
        } else{
            $this->header['Return-Path'] = $this->mailMessage->getFromEmail();
            $this->header['From'] = $this->mailMessage->getFromName() . " <" . $this->mailMessage->getFromEmail() .">";
        }

        $this->header['To'] = '';
        foreach ($this->mailMessage->getTo() as $toName => $toEmail) {
            $this->header['To'] .= $toName . " <" . $toEmail . ">, ";
        }
        $this->header['To'] = substr($this->header['To'], 0, -2);
        $this->header['Subject'] = $this->mailMessage->getSubject();
        $this->header['Message-ID'] = '<' . md5('TX'.md5(time()).uniqid()) . '@' . $this->username . '>';
        $this->header['X-Priority'] = '3';
        $this->header['X-Mailer'] = 'Mailer (https://github.com/txthinking/Mailer)';
        $this->header['MIME-Version'] = '1.0';
        if (!empty($this->attachment)){
            $this->boundaryMixed = md5(md5(time().'TxMailer').uniqid());
            $this->header['Content-Type'] = "multipart/mixed; \r\n\tboundary=\"" . $this->boundaryMixed . "\"";
        }
        $this->boundaryAlternative = md5(md5(time().'TXMailer').uniqid());
        return $this;
    }

    protected function pushStack($string)
    {
        $this->commandStack[] = $string;
        fputs($this->smtp, $string, strlen($string));
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
            $this->resultStack[] = $str;
            if(substr($str,3,1) == " ") {
                $code = substr($str,0,3);
                $this->logger->addDebug("Got a code of: {$code}");
                return $code;
            }
        }
        throw new SMTPException("SMTP Server did not respond with anything I recognized");
    }

}

