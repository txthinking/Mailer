<?php namespace Tx\Mailer;
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
class Message
{
    /**
     * from name
     */
    protected $fromName;

    /**
     * from email
     */
    protected $fromEmail;

    /**
     * fake from name
     */
    protected $fakeFromName;

    /**
     * fake from email
     */
    protected $fakeFromEmail;

    /**
     * to email
     */
    protected $to = array();

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
    protected $attachment = array();

    /**
     * message header
     */
    protected $header = array();

    /**
     * charset
     */
    protected $charset = "UTF-8";

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
     * set mail from
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setFrom($name, $email)
    {
        $this->fromName = $name;
        $this->fromEmail = $email;
        return $this;
    }


    /**
     * set mail fake from
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setFakeFrom($name, $email)
    {
        $this->fakeFromName = $name;
        $this->fakeFromEmail = $email;
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
     * add mail receiver
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function addTo($name, $email){
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
     * add mail attachment
     * @param $name
     * @param $path
     * @return $this
     */
    public function setAttachment($name, $path){
        $this->attachment[$name] = $path;
        return $this;
    }

    /**
     * add mail attachment
     * @param $name
     * @param $path
     * @return $this
     */
    public function addAttachment($name, $path){
        $this->attachment[$name] = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @return string
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }


    /**
     * @return string
     */
    public function getFakeFromName()
    {
        return $this->fakeFromName;
    }

    /**
     * @return string
     */
    public function getFakeFromEmail()
    {
        return $this->fakeFromEmail;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * Create mail header
     * @return $this
     */
    protected function createHeader(){
        $this->header['Date'] = date('r');

        if(!empty($this->fakeFromEmail)){
            $this->header['Return-Path'] = $this->fakeFromEmail;
            $this->header['From'] = $this->fakeFromName . " <" . $this->fakeFromEmail . ">";
        } else{
            $this->header['Return-Path'] = $this->fromEmail;
            $this->header['From'] = $this->fromName . " <" . $this->fromEmail .">";
        }

        $this->header['To'] = '';
        foreach ($this->to as $toName => $toEmail) {
            $this->header['To'] .= $toName . " <" . $toEmail . ">, ";
        }
        $this->header['To'] = substr($this->header['To'], 0, -2);
        $this->header['Subject'] = $this->subject;
        $this->header['Message-ID'] = '<' . md5('TX'.md5(time()).uniqid()) . '@' . $this->fromEmail . '>';
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
        $in .= chunk_split(base64_encode($this->body)) . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . $this->CRLF;
        $in .= "Content-Type: text/html; charset=\"" . $this->charset ."\"" . $this->CRLF;
        $in .= "Content-Transfer-Encoding: base64" . $this->CRLF;
        $in .= $this->CRLF;
        $in .= chunk_split(base64_encode($this->body)) . $this->CRLF;
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
        $in .= chunk_split(base64_encode($this->body)) . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . $this->CRLF;
        $in .= "Content-Type: text/html; charset=\"" . $this->charset ."\"" . $this->CRLF;
        $in .= "Content-Transfer-Encoding: base64" . $this->CRLF;
        $in .= $this->CRLF;
        $in .= chunk_split(base64_encode($this->body)) . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . "--" . $this->CRLF;
        foreach ($this->attachment as $name => $path){
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

    public function toString(){
        $in = '';
        $this->createHeader();
        foreach ($this->header as $key => $value) {
            $in .= $key . ': ' . $value . $this->CRLF;
        }
        if (empty($this->attachment)) {
            $in .= $this->createBody();
        } else {
            $in .= $this->createBodyWithAttachment();
        }
        $in .= $this->CRLF . $this->CRLF . "." . $this->CRLF;
        return $in;
    }

}
