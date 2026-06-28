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
     * socket timeout in seconds (connect + read/write)
     */
    protected $timeout;

    /**
     * smtp secure ssl tls tlsv1.0 tlsv1.1 tlsv1.2
     */
    protected $secure;

    /**
     * smtp allow insecure ssl
     */
    protected $allowInsecure;

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
     * oauth access token
     */
    protected $oauthToken;

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
        $this->timeout = (int) ini_get('default_socket_timeout');
    }

    /**
     * set server and port
     * @param string $host server
     * @param int $port port
     * @param string $secure ssl tls
     * @param bool $allowInsecure skip certificate verification?
     * @return $this
     */
    public function setServer($host, $port, $secure=null, $allowInsecure=null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->secure = $secure;
        $this->allowInsecure = (bool) $allowInsecure;
        if(!$this->ehlo) $this->ehlo = $host;
        $this->logger && $this->logger->debug("Set: the server");
        return $this;
    }

    /**
     * auth login with server
     * @param string $username
     * @param string $password
     * @return $this
     */
    public function setAuth($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->logger && $this->logger->debug("Set: the auth login");
        return $this;
    }

    /**
     * auth oauthbearer with server
     * @param string $accessToken
     * @return $this
     */
    public function setOAuth($accessToken)
    {
        $this->oauthToken = $accessToken;
        $this->logger && $this->logger->debug("Set: the auth oauthbearer");
        return $this;
    }

    /**
     * set the EHLO message
     * @param $ehlo
     * @return $this
     */
    public function setEhlo($ehlo)
    {
        $this->ehlo = $ehlo;
        return $this;
    }

    /**
     * set the socket timeout in seconds, applied to both connecting and
     * to each read/write while talking to the server
     * @param int $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (int) $timeout;
        return $this;
    }

    /**
     * Send the message
     *
     * @param Message $message
     * @return bool
     * @throws CodeException
     * @throws CryptoException
     * @throws SMTPException
     */
    public function send(Message $message)
    {
        $this->logger && $this->logger->debug('Set: a message will be sent');
        $this->message = $message;
        $this->connect()
            ->ehlo();

        if ($this->secure === 'tls' || $this->secure === 'tlsv1.0' || $this->secure === 'tlsv1.1' | $this->secure === 'tlsv1.2') {
            $this->starttls()
                ->ehlo();
        }

        if ($this->username !== null || $this->password !== null) {
            $this->authLogin();
        } elseif ($this->oauthToken !== null) {
            $this->authOAuthBearer();
        }
        $this->mailFrom()
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
    protected function connect()
    {
        $this->logger && $this->logger->debug("Connecting to {$this->host} at {$this->port}");
        $host = ($this->secure == 'ssl') ? 'ssl://' . $this->host : $this->host;
        // Create connection
        $context = stream_context_create([]);
        if ($this->allowInsecure) {
            $context = stream_context_create([
                'ssl' => [
                    'security_level' => 0,
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);
        }
        $this->smtp = @stream_socket_client(
            $host.':'.$this->port,
            $error_code,
            $error_message,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );
        if (!$this->smtp){
            throw new SMTPException("Could not open SMTP Port to $host:{$this->port}");
        }
        stream_set_timeout($this->smtp, $this->timeout);
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
    protected function starttls()
    {
        $in = "STARTTLS" . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '220'){
            throw new CodeException('220', $code, array_pop($this->resultStack));
        }

        if ($this->secure !== 'tls' && version_compare(phpversion(), '5.6.0', '<')) {
            throw new CryptoException('Crypto type expected PHP 5.6 or greater');
        }

        if ($this->allowInsecure) {
            stream_context_set_option($this->smtp, 'ssl', 'verify_peer', false);
            stream_context_set_option($this->smtp, 'ssl', 'verify_peer_name', false);
            stream_context_set_option($this->smtp, 'ssl', 'allow_self_signed', true);
        }

        if(!\stream_socket_enable_crypto($this->smtp, true, STREAM_CRYPTO_METHOD_ANY_CLIENT)) {
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
    protected function ehlo()
    {
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
     * SMTP AUTH OAUTHBEARER
     * SUCCESS 235
     * @return $this
     * @throws CodeException
     * @throws SMTPException
     */
    protected function authOAuthBearer()
    {
        $authStr = sprintf("n,a=%s,%shost=%s%sport=%s%sauth=Bearer %s%s%s",
            $this->message->getFromEmail(),
            chr(1),
            $this->host,
            chr(1),
            $this->port,
            chr(1),
            $this->oauthToken,
            chr(1),
            chr(1)
        );
        $authStr = base64_encode($authStr);
        $in = "AUTH OAUTHBEARER $authStr" . $this->CRLF;
        $code = $this->pushStack($in);
        if ($code !== '235'){
            throw new CodeException('235', $code, array_pop($this->resultStack));
        }
        return $this;
    }

    /**
     * SMTP AUTH XOAUTH2
     * SUCCESS 235
     * @return $this
     * @throws CodeException
     * @throws SMTPException
     */
    protected function authXOAuth2()
    {
        $authStr = sprintf("user=%s%sauth=Bearer %s%s%s",
            $this->message->getFromEmail(),
            chr(1),
            $this->oauthToken,
            chr(1),
            chr(1)
        );
        $authStr = base64_encode($authStr);
        $in = "AUTH XOAUTH2 $authStr" . $this->CRLF;
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
    protected function mailFrom()
    {
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
    protected function rcptTo()
    {
        $to = array_merge(
            $this->message->getTo(),
            $this->message->getCc(),
            $this->message->getBcc()
        );
        foreach ($to as $toEmail=>$_) {
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
    protected function data()
    {
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
    protected function quit()
    {
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
        if (fputs($this->smtp, $string, strlen($string)) === false) {
            throw new SMTPException("Failed to write to SMTP socket");
        }
        $this->logger && $this->logger->debug('Sent: '. $string);
        return $this->getCode();
    }

    /**
     * Read one complete SMTP reply and return its 3-digit status code.
     *
     * An SMTP reply may span several lines (RFC 5321 §4.2.1): continuation
     * lines have the form "NNN-text", the final line "NNN text" (or "NNN").
     * All lines are read and reassembled into a single reply, which is pushed
     * as one entry onto $this->resultStack -- so it stays aligned with
     * $this->commandStack (one result per command) and CodeException can
     * surface the whole reply, not just its last line.
     *
     * @return string the 3-digit reply code
     * @throws SMTPException on a closed/timed-out connection or an unparseable reply
     */
    protected function getCode()
    {
        $code = null;
        $reply = '';

        do {
            $line = $this->readLine();
            if ($line === null) {
                $meta = stream_get_meta_data($this->smtp);
                if (!empty($meta['timed_out'])) {
                    throw new SMTPException("SMTP read timed out after {$this->timeout} seconds");
                }
                throw new SMTPException("SMTP Server did not respond with anything I recognized");
            }

            $this->logger && $this->logger->debug("Got: " . $line);
            $reply .= $line;

            // RFC 5321 §4.2.1: "NNN " (or bare "NNN") is the final line,
            // "NNN-" marks a continuation line that must be followed by more.
            if (!preg_match('/^(\d{3})([ -]?)/', $line, $m)) {
                $this->resultStack[] = $reply;
                throw new SMTPException("SMTP Server did not respond with anything I recognized");
            }

            $code      = $m[1];
            $separator = $m[2];
        } while ($separator === '-');

        $this->resultStack[] = $reply;

        return $code;
    }

    /**
     * Read a single complete line (terminated by LF) from the socket,
     * regardless of how long it is or how the OS chunks it. Reassembling the
     * whole line here is what keeps a long reply from leaving a tail in the
     * buffer that would desync the next getCode() call.
     *
     * @return string|null the line (including its trailing CRLF), or null on EOF/timeout
     */
    protected function readLine()
    {
        $line = '';
        while (true) {
            $chunk = fgets($this->smtp, 1024);
            if ($chunk === false) {
                return ($line === '') ? null : $line;   // EOF / timeout
            }
            $line .= $chunk;
            if (substr($line, -1) === "\n") {
                return $line;                            // got the full line
            }
        }
    }

}

