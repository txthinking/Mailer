<?php

namespace Laasti\Mailer;

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
     * reply to name
     */
    protected $replyToName;

    /**
     * reply to email
     */
    protected $replyToEmail;

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
     * mail body
     */
    protected $textBody;

    /**
     * mail attachment
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
    public $CRLF = "\r\n";

    /**
     * set mail from
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setFrom($name, $email)
    {
        $this->fromName = $this->safeHeaderString($name);
        $this->fromEmail = $email;
        return $this;
    }

    /**
     * set mail reply to
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setReplyTo($name, $email)
    {
        $this->replyToName = $this->safeHeaderString($name);
        $this->replyToEmail = $email;
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
        $this->fakeFromName = $this->safeHeaderString($name);
        $this->fakeFromEmail = $email;
        return $this;
    }

    /**
     * set mail receiver
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setTo($name, $email)
    {
        $this->to[$this->safeHeaderString($name)] = $email;
        return $this;
    }

    /**
     * add mail receiver
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function addTo($name, $email)
    {
        $this->to[$this->safeHeaderString($name)] = $email;
        return $this;
    }

    /**
     * set mail subject
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $this->safeHeaderString($subject);
        return $this;
    }

    /**
     * set mail body
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * set mail text body
     * @param string $body
     * @return $this
     */
    public function setTextBody($txtbody)
    {
        $this->textBody = $txtbody;
        return $this;
    }

    /**
     * add mail attachment
     * @param $name
     * @param $path
     * @return $this
     */
    public function setAttachment($name, $path)
    {
        $this->attachment[$name] = $path;
        return $this;
    }

    /**
     * add mail attachment
     * @param $name
     * @param $path
     * @return $this
     */
    public function addAttachment($name, $path)
    {
        $this->attachment[$name] = $path;
        return $this;
    }

    /**
     * set mail charset
     * @param string $charset
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
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
     * @return string
     */
    public function getReplyToName()
    {
        return $this->replyToName;
    }

    /**
     * @return string
     */
    public function getReplyToEmail()
    {
        return $this->replyToEmail;
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
     * @return mixed
     */
    public function getTextBody()
    {
        return $this->textBody;
    }

    /**
     * @return mixed
     */
    public function getAltBody()
    {
        if (!empty($this->textBody)) {
            return $this->textBody;
        }

        //Create text body from HTML body
        $match = [];
        $body = preg_match('/\<body.*?\>(.*)\<\/body\>/si', $this->body, $match) ? $match[1] : $this->body;
        $body = str_replace("\t", '', preg_replace('#<!--(.*)--\>#', '', trim(strip_tags($body))));
        for ($i = 20; $i >= 3; $i--) {
            $body = str_replace(str_repeat("\n", $i), "\n\n", $body);
        }
        // Reduce multiple spaces
        $body = preg_replace('| +|', ' ', $body);
        return $body;
    }

    /**
     * @return array
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @return string
     */
    public function getHeader($header, $default = null)
    {
        return isset($this->header[$header]) ? $this->header[$header] : $default;
    }

    /**
     * Create mail header
     * @return $this
     */
    protected function createHeader($mail = false)
    {
        $this->header['Date'] = date('r');

        if (!empty($this->fakeFromEmail)) {
            $this->header['Return-Path'] = $this->fakeFromEmail;
            $this->header['From'] = $this->fakeFromName . " <" . $this->fakeFromEmail . ">";
            $this->header['Reply-To'] = $this->fakeFromName . " <" . $this->fakeFromEmail . ">";
        } else {
            $this->header['Return-Path'] = $this->fromEmail;
            $this->header['From'] = $this->fromName . " <" . $this->fromEmail . ">";
            $this->header['Reply-To'] = $this->fromName . " <" . $this->fromEmail . ">";
        }

        if (!is_null($this->replyToName)) {
            $this->header['Reply-To'] = $this->replyToName . " <" . $this->replyToEmail . ">";
        }

        $this->header['To'] = '';
        foreach ($this->to as $toName => $toEmail) {
            $this->header['To'] .= $toName . " <" . $toEmail . ">, ";
        }
        $this->header['To'] = substr($this->header['To'], 0, -2);
        $this->header['Subject'] = $this->subject;
        $this->header['Message-ID'] = '<' . md5('Laasti' . md5(time()) . uniqid()) . '@' . $this->fromEmail . '>';
        $this->header['X-Priority'] = '3';
        $this->header['X-Mailer'] = 'Mailer (https://github.com/laasti/mailer)';
        $this->header['MIME-Version'] = '1.0';
        
        $this->boundaryAlternative = md5(md5(time() . 'LaastiMailer') . uniqid("", true));
        if (!empty($this->attachment)) {
            $this->boundaryMixed = md5(md5(time() . 'LaastiMailer') . uniqid("", true));
            $this->header['Content-Type'] = "multipart/mixed; charset=\"" . $this->charset . "\"; boundary=\"" . $this->boundaryMixed . "\"";
        } else if (!empty($this->textBody)) {
            $this->header['Content-Type'] = "text/plain; charset=\"" . $this->charset . "\"";
            if ($mail) {
                $this->header['Content-Transfer-Encoding'] = "base64";
            }
        } else {
            $this->header['Content-Type'] = "multipart/alternative; charset=\"" . $this->charset . "\"; boundary=\"$this->boundaryAlternative\"";
        }
        return $this;
    }

    /**
     * @brief createBody create body
     *
     * @return string
     */
    protected function createBody($mail = false)
    {
        $in = "";
        if (!$mail) {
            $in .= "Content-Type: multipart/alternative; boundary=\"$this->boundaryAlternative\"" . $this->CRLF; //MAIL: In header, not body
        }
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . $this->CRLF;
        $in .= "Content-Type: text/plain; charset=\"" . $this->charset . "\"" . $this->CRLF;
        $in .= "Content-Transfer-Encoding: base64" . $this->CRLF;
        $in .= $this->CRLF;
        $in .= chunk_split(base64_encode($this->getAltBody())) . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . $this->CRLF;
        $in .= "Content-Type: text/html; charset=\"" . $this->charset . "\"" . $this->CRLF;
        $in .= "Content-Transfer-Encoding: base64" . $this->CRLF;
        $in .= $this->CRLF;
        $in .= chunk_split(base64_encode($this->getBody())) . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . "--" . $this->CRLF;
        return $in;
    }

    /**
     * @brief createTextBody create text body
     *
     * @return string
     */
    protected function createTextBody($mail = false)
    {
        $in = "";
        if (!$mail) {
            $in .= "Content-Type: text/plain; charset=\"" . $this->charset . "\"" . $this->CRLF; //Mail, in header, not body
            $in .= "Content-Transfer-Encoding: base64" . $this->CRLF; //Mail, in header, not body
        }
        $in .= $this->CRLF;
        $in .= chunk_split(base64_encode($this->getTextBody())) . $this->CRLF;
        return $in;
    }

    /**
     * @brief createTextBodyWithAttachment create text body with attachment
     *
     * @return string
     */
    protected function createTextBodyWithAttachment($mail = false)
    {
        $in = "";
        //Mixed could be related for image attachment in Thunderbird
        if (!$mail) {
            //$in .= 'Content-Type: multipart/mixed; boundary="' . $this->boundaryMixed . '"'; //Mail, in header, not body
        }
        $in .= $this->CRLF;
        $in .= $this->CRLF;
        $in .= '--' . $this->boundaryMixed . $this->CRLF;
        $in .= "Content-Type: text/plain; charset=\"" . $this->charset . "\"" . $this->CRLF;
        $in .= "Content-Transfer-Encoding: base64" . $this->CRLF;
        $in .= $this->CRLF;
        $in .= chunk_split(base64_encode($this->getTextBody())) . $this->CRLF;
        $in .= $this->CRLF;
        foreach ($this->attachment as $name => $path) {
            $in .= $this->CRLF;
            $in .= '--' . $this->boundaryMixed . $this->CRLF;
            $in .= "Content-Type: application/octet-stream; name=\"" . $name . "\"" . $this->CRLF;
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
     * @brief createBodyWithAttachment create body with attachment
     *
     * @return string
     */
    protected function createBodyWithAttachment($mail = false)
    {
        $in = "";
        if (!$mail) {
            $in .= 'Content-Type: multipart/mixed; boundary="' . $this->boundaryMixed . '"'; //Mail, in header, not body
        }
        $in .= $this->CRLF;
        $in .= '--' . $this->boundaryMixed . $this->CRLF;
        $in .= "Content-Type: multipart/alternative; boundary=\"$this->boundaryAlternative\"" . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . $this->CRLF;
        $in .= "Content-Type: text/plain; charset=\"" . $this->charset . "\"" . $this->CRLF;
        $in .= "Content-Transfer-Encoding: base64" . $this->CRLF;
        $in .= $this->CRLF;
        $in .= chunk_split(base64_encode($this->getAltBody())) . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . $this->CRLF;
        $in .= "Content-Type: text/html; charset=\"" . $this->charset . "\"" . $this->CRLF;
        $in .= "Content-Transfer-Encoding: base64" . $this->CRLF;
        $in .= $this->CRLF;
        $in .= chunk_split(base64_encode($this->getBody())) . $this->CRLF;
        $in .= $this->CRLF;
        $in .= "--" . $this->boundaryAlternative . "--" . $this->CRLF;
        foreach ($this->attachment as $name => $path) {
            $in .= $this->CRLF;
            $in .= '--' . $this->boundaryMixed . $this->CRLF;
            $in .= "Content-Type: application/octet-stream; name=\"" . $name . "\"" . $this->CRLF;
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

    public function getEncodedBody($mail = false)
    {
        $this->createHeader($mail);
        $in = '';
        if (empty($this->attachment)) {
            if (empty($this->body)) {
                $in .= $this->createTextBody($mail);
            } else {
                $in .= $this->createBody($mail);
            }
        } else {
            if (empty($this->body)) {
                $in .= $this->createTextBodyWithAttachment($mail);
            } else {
                $in .= $this->createBodyWithAttachment($mail);
            }
        }
        $in .= $this->CRLF;

        return $in;
    }

    public function toString($mail = false)
    {
        $in = '';
        $this->createHeader($mail);
        $in = $this->headersToString();
        if (empty($this->attachment)) {
            if (empty($this->body)) {
                $in .= $this->createTextBody($mail);
            } else {
                $in .= $this->createBody($mail);
            }
        } else {
            if (empty($this->body)) {
                $in .= $this->createTextBodyWithAttachment($mail);
            } else {
                $in .= $this->createBodyWithAttachment($mail);
            }
        }
        $in .= $this->CRLF;
        //$in .= $this->CRLF . $this->CRLF. "." . $this->CRLF;
        return $in;
    }

    public function headersToString()
    {
        $in = '';
        foreach ($this->header as $key => $value) {
            $in .= $key . ': ' . $value . $this->CRLF;
        }
        return $in;
    }

    protected function safeHeaderString($str)
    {
        return '=?' . $this->charset . '?B?' . base64_encode($str) . '?=';
        $str = str_replace(array("\r", "\n"), '', $str);
        $icon_enabled = extension_loaded('iconv');
        if ($this->charset === 'UTF-8') {
            if (extension_loaded('mbstring')) {
                return mb_encode_mimeheader($str, $this->charset, 'Q', $this->CRLF);
            } elseif ($icon_enabled) {
                $output = @iconv_mime_encode('', $str, array(
                            'scheme' => 'Q',
                            'line-length' => 76,
                            'input-charset' => $this->charset,
                            'output-charset' => $this->charset,
                            'line-break-chars' => $this->CRLF
                                )
                );
                // There are reports that iconv_mime_encode() might fail and return FALSE
                if ($output !== FALSE) {
                    // iconv_mime_encode() will always put a header field name.
                    // We've passed it an empty one, but it still prepends our
                    // encoded string with ': ', so we need to strip it.
                    return substr($output, 2);
                }
                $chars = iconv_strlen($str, 'UTF-8');
            }
        }
        // We might already have this set for UTF-8
        isset($chars) OR $chars = strlen($str);
        $output = '=?' . $this->charset . '?Q?';
        for ($i = 0, $length = strlen($output); $i < $chars; $i++) {
            $chr = ($this->charset === 'UTF-8' && $icon_enabled) ? '=' . implode('=', str_split(strtoupper(bin2hex(iconv_substr($str, $i, 1, $this->charset))), 2)) : '=' . strtoupper(bin2hex($str[$i]));
            // RFC 2045 sets a limit of 76 characters per line.
            // We'll append ?= to the end of each line though.
            if ($length + ($l = strlen($chr)) > 74) {
                $output .= '?=' . $this->CRLF // EOL
                        . ' =?' . $this->charset . '?Q?' . $chr; // New line
                $length = 6 + strlen($this->charset) + $l; // Reset the length for the new line
            } else {
                $output .= $chr;
                $length += $l;
            }
        }
        // End the header
        return $output . '?=';
    }

}
