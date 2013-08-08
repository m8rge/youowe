<?php

class AlternativeMail
{
    /**
     * @var array
     */
    public $to = array();

    /**
     * @var array
     */
    public $from;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $htmlBody;

    /**
     * @var string
     */
    public $textBody;

    function __construct()
    {
        mb_internal_encoding('UTF-8');
    }

    public function setFrom($email, $name = '')
    {
        $this->from = array(
            'email' => $email,
            'name' => $name
        );

        return $this;
    }

    public function addTo($email, $name = '')
    {
        $this->to[] = array(
            'email' => $email,
            'name' => $name
        );

        return $this;
    }

    public function setHtmlBody($html)
    {
        $this->htmlBody = $html;

        return $this;
    }

    public function setTextBody($text)
    {
        $this->textBody = $text;

        return $this;
    }

    public function getPart($part)
    {
        $headers[] = "Content-Type: $part/plain; charset=UTF-8";
        $headers[] = 'Content-Transfer-Encoding: base64';
        $bodyVarName = $part . 'Body';
        $body = chunk_split(base64_encode($this->$bodyVarName));

        return array($headers, $body);
    }

    public function getMailBody()
    {
        $body = '';
        $boundary = md5(time());
        $additionalHeaders = array();
        if (!empty($this->textBody) && !empty($this->htmlBody)) {
            $additionalHeaders[] = 'Content-Type: multipart/alternative; boundary=' . $boundary;

            list($textHeaders, $textBody) = $this->getPart('text');
            list($htmlHeaders, $htmlBody) = $this->getPart('html');
            array_unshift($textHeaders, '--' . $boundary);
            array_unshift($htmlHeaders, '--' . $boundary);

            $textHeaders = implode("\r\n", $textHeaders);
            $htmlHeaders = implode("\r\n", $htmlHeaders);
            $body .= $textHeaders . "\r\n\r\n" . $textBody . "\r\n";
            $body .= $htmlHeaders . "\r\n\r\n" . $htmlBody . "\r\n";
            $body .= '--' . $boundary . '--';
        }
        if (!empty($this->textBody)) {
            list($additionalHeaders, $body) = $this->getPart('text');
        }
        if (!empty($this->htmlBody)) {
            list($additionalHeaders, $body) = $this->getPart('html');
        }

        return array($additionalHeaders, $body);
    }

    /**
     * @return bool
     */
    public function send()
    {
        $to = array_map(
            function ($value) {
                if (!empty($value['name'])) {
                    return mb_encode_mimeheader($value['name']) . " <{$value['email']}>";
                } else {
                    return $value['email'];
                }
            },
            $this->to
        );
        $to = implode(', ', $to);
        if (!empty($this->from['name'])) {
            $from = mb_encode_mimeheader($this->from['name']) . " <{$this->from['email']}>";
        } else {
            $from = $this->from['email'];
        }
        $additionalHeaders = array(
            'From: ' . $from,
        );

        $subject = mb_encode_mimeheader($this->subject, 'UTF-8');
        $additionalHeaders = implode("\r\n", $additionalHeaders);
        return mail($to, $subject, $this->getMailBody(), $additionalHeaders);
    }
}
/**
 * todo: compare mb_decode_mimeheader iconv_mime_decode imap_utf8 imap_mime_header_decode
 */