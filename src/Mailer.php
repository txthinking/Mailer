<?php namespace Tx;
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
use Exception;

class Mailer{
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
     * from email
     */
    protected $from;
    /**
     * the fake from email
     */
    protected $fakeFrom;
    /**
     * to email
     */
    protected $to;
    /**
     * mail subject
     */
    protected $subject;
    /**
     * mail body
     */
    protected $body;
    /**
     *mail attachment
     */
    protected $attachment;
    /**
     * charset
     */
    protected $charset;
    /**
     * message header
     */
    protected $header;
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
     */
    protected $CRLF;
    /**
     * responce message
     */
    protected $message;

    /**
     * construct function
     */
    public function __construct(){
        $this->from = array();
        $this->fakeFrom = array();
        $this->to = array();
        $this->attachment = array();
        $this->charset =  "UTF-8";
        $this->header = array();
        $this->CRLF = "\r\n";
        $this->message = "";
    }

    /**
     * set server and port
     * @param string $host server
     * @param int $port port
     * @param string $secure ssl tls
     * @return $this
     */
    public function setServer($host, $port, $secure=null){
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
        $this->username = $username;
        $this->password = $password;
        return $this;
    }

    /**
     * set mail from
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setFrom($name, $email){
        $this->from['name'] = $name;
        $this->from['email'] = $email;
        return $this;
    }

    /**
     * set fake mail from
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setFakeFrom($name, $email){
        $this->fakeFrom['name'] = $name;
        $this->fakeFrom['email'] = $email;
        return $this;
    }

    /**
     * set mail receiver
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setTo($name, $email){
        $this->to[$name] = $email;
        return $this;
    }

    /**
     * set mail subject
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject){
        $this->subject = $subject;
        return $this;
    }

    /**
     * set mail body
     * @param string $body
     * @return $this
     */
    public function setBody($body){
        $this->body = $body;
        return $this;
    }

    /**
     * set mail attachment
     * @param $name
     * @param $path
     * @return $this
     * @internal param string $attachment
     */
    public function setAttachment($name, $path){
        $this->attachment[$name] = $path;
        return $this;
    }

    /**
     *  send
     * @throws Exception
     * @return bool
     */
    public function send(){
        if($this->doSend() === false){
            throw new Exception($this->message);
        }
        return true;
    }

    /**
     * send mail
     * @return boolean
     */
    protected function doSend(){
        if (!$this->connect()){
            return false;
        }
        if (!$this->ehlo()){
            return false;
        }
        if ($this->secure == 'tls'){
            if(!$this->starttls()){
                return false;
            }
            if (!$this->ehlo()){
                return false;
            }
        }
        if (!$this->authLogin()){
            return false;
        }
        if (!$this->mailFrom()){
            return false;
        }
        if (!$this->rcptTo()){
            return false;
        }
        if (!$this->data()){
            return false;
        }
        if (!$this->quit()){
            return false;
        }
        return fclose($this->smtp);
    }

    /**
     * connect the server
     * SUCCESS 220
     * @return boolean
     */
    protected function connect(){
        $host = ($this->secure == 'ssl') ? 'ssl://' . $this->host : $this->host;
        $this->smtp = fsockopen($host, $this->port);
        //set block mode
        //    stream_set_blocking($this->smtp, 1);
        if (!$this->smtp){
            return false;
        }
        if ($this->getCode() != 220){
            return false;
        }
        return true;
    }

    /**
     * SMTP STARTTLS
     * SUCCESS 220
     * @return boolean
     */
    protected function starttls(){
        fputs($this->smtp,"STARTTLS" . $this->CRLF);
        if ($this->getCode() != 220){
            return false;
        }
        if(!stream_socket_enable_crypto($this->smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            return false;
        }
        return true;
    }

    /**
     * SMTP EHLO
     * SUCCESS 250
     * @return boolean
     */
    protected function ehlo(){
        $in = "EHLO " . $this->host . $this->CRLF;
        fputs($this->smtp, $in, strlen($in));
        if ($this->getCode() != 250){
            return false;
        }
        return true;
    }

    /**
     * SMTP AUTH LOGIN
     * SUCCESS 334
     * SUCCESS 334
     * SUCCESS 235
     * @return boolean
     */
    protected function authLogin(){
        if ($this->username === null && $this->password === null) {
            // Unless the user has specifically set a username/password
            // Do not try to authorize.
            return true;
        }

        $in = "AUTH LOGIN" . $this->CRLF;
        fputs($this->smtp, $in, strlen($in));
        if ($this->getCode() != 334){
            return false;
        }
        $in = base64_encode($this->username) . $this->CRLF;
        fputs($this->smtp, $in, strlen($in));
        if ($this->getCode() != 334){
            return false;
        }
        $in = base64_encode($this->password) . $this->CRLF;
        fputs($this->smtp, $in, strlen($in));
        if ($this->getCode() != 235){
            return false;
        }
        return true;
    }
    /**
     * SMTP MAIL FROM
     * SUCCESS 250
     * @return boolean
     */
    protected function mailFrom(){
        $in = "MAIL FROM:<" . $this->from['email'] . ">" . $this->CRLF;
        fputs($this->smtp, $in, strlen($in));
        if ($this->getCode() != 250){
            return false;
        }
        return true;
    }

    /**
     * SMTP RCPT TO
     * SUCCESS 250
     * @return boolean
     */
    protected function rcptTo(){
        foreach ($this->to as $v){
            $in = "RCPT TO:<" . $v . ">" . $this->CRLF;
            fputs($this->smtp, $in, strlen($in));
            if ($this->getCode() != 250){
                return false;
            }
        }
        return true;
    }

    /**
     * SMTP DATA
     * SUCCESS 354
     * SUCCESS 250
     * @return boolean
     */
    protected function data(){
        $in = "DATA" . $this->CRLF;
        fputs($this->smtp, $in, strlen($in));
        if ($this->getCode() != 354){
            return false;
        }
        $this->body = chunk_split(base64_encode($this->body));

        $in = '';
        $this->createHeader();
        foreach ($this->header as $k=>$v){
            $in .= $k . ': ' . $v . $this->CRLF;
        }
        if (empty($this->attachment)){
            $in .= $this->createBody();
        }else {
            $in .= $this->createBodyWithAttachment();
        }
        $in .= $this->CRLF;
        $in .= $this->CRLF . '.' . $this->CRLF;
        fputs($this->smtp, $in, strlen($in));
        if ($this->getCode() != 250){
            return false;
        }
        return true;
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
        $in .= $this->body . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . $this->CRLF;
        $in .= "Content-Type: text/html; charset=\"" . $this->charset ."\"" . $this->CRLF;
        $in .= "Content-Transfer-Encoding: base64" . $this->CRLF;
        $in .= $this->CRLF;
        $in .= $this->body . $this->CRLF;
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
        $in .= $this->body . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . $this->CRLF;
        $in .= "Content-Type: text/html; charset=\"" . $this->charset ."\"" . $this->CRLF;
        $in .= "Content-Transfer-Encoding: base64" . $this->CRLF;
        $in .= $this->CRLF;
        $in .= $this->body . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . "--" . $this->CRLF;
        foreach ($this->attachment as $k=>$v){
            $in .= $this->CRLF;
            $in .= '--' . $this->boundaryMixed . $this->CRLF;
            $in .= "Content-Type: application/octet-stream; name=\"". $k ."\"" . $this->CRLF;
            $in .= "Content-Transfer-Encoding: base64" . $this->CRLF;
            $in .= "Content-Disposition: attachment; filename=\"" . $k . "\"" . $this->CRLF;
            $in .= $this->CRLF;
            $in .= chunk_split(base64_encode(file_get_contents($v))) . $this->CRLF;
        }
        $in .= $this->CRLF;
        $in .= $this->CRLF;
        $in .= '--' . $this->boundaryMixed . '--' . $this->CRLF;
        return $in;
    }
    /**
     * SMTP QUIT
     * SUCCESS 221
     * @return boolean
     */
    protected function quit(){
        $in = "QUIT" . $this->CRLF;
        fputs($this->smtp, $in, strlen($in));
        if ($this->getCode() != 221){
            return false;
        }
        return true;
    }

    /**
     * create message header
     */
    protected function createHeader(){
        $this->header['Date'] = date('r');
        if(!empty($this->fakeFrom)){
            $this->header['Return-Path'] = $this->fakeFrom['email'];
            $this->header['From'] = $this->fakeFrom['name'] . " <" . $this->fakeFrom['email'] .">";
        }else{
            $this->header['Return-Path'] = $this->from['email'];
            $this->header['From'] = $this->from['name'] . " <" . $this->from['email'] .">";
        }
        $this->header['To'] = '';
        foreach ($this->to as $k=>$v){
            $this->header['To'] .= $k . " <" . $v . ">, ";
        }
        $this->header['To'] = substr($this->header['To'], 0, -2);
        $this->header['Subject'] = $this->subject;
        $this->header['Message-ID'] = '<' . md5('TX'.md5(time()).uniqid()) . '@' . $this->username . '>';
        $this->header['X-Priority'] = '3';
        $this->header['X-Mailer'] = 'Mailer (https://github.com/txthinking/Mailer)';
        $this->header['MIME-Version'] = '1.0';
        if (!empty($this->attachment)){
            $this->boundaryMixed = md5(md5(time().'Mailer').uniqid());
            $this->header['Content-Type'] = "multipart/mixed; \r\n\tboundary=\"" . $this->boundaryMixed . "\"";
        }
        $this->boundaryAlternative = md5(md5(time().'Mailer').uniqid());
    }

    /**
     * get smtp response code
     * once time has three digital and a space
     * @return int
     */
    protected function getCode() {
        while($str = @fgets($this->smtp,515)) {
            $this->message .= $str;
            if(substr($str,3,1) == " ") {
                return substr($str,0,3);
            }
        }
        return false;
    }
}

