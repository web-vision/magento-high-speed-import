<?php

/**
 * web-vision GmbH
 *
 * NOTICE OF LICENSE
 *
 * <!--LICENSETEXT-->
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.web-vision.de for more information.
 *
 * @category    WebVision
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class Email extends Fci_Objects_AbstractScript
{
    protected $_from;

    protected $_to;

    protected $_subject;

    protected $_message;

    protected $_htmlMessage = false;

    protected $_attachments = array();

    /**
     * @inheritDoc
     */
    public function __construct($params)
    {
        if (array_key_exists('event', $params)) {
            $this->_eventName = $params['event'];
        }
        if (array_key_exists('from', $params)) {
            $this->_from = $params['from'];
        }
        if (array_key_exists('to', $params)) {
            $this->_to = $params['to'];
            if (!is_array($this->_to)) {
                $this->_to = array($this->_to);
            }
        }
        if (array_key_exists('subject', $params)) {
            $this->_subject = $params['subject'];
        }
        if (array_key_exists('message', $params)) {
            $this->_message = $params['message'];
        }
        if (array_key_exists('html_message', $params)) {
            $this->_htmlMessage = (bool)$params['html_message'];
        }
        if (array_key_exists('attachments', $params)) {
            $attachments = $params['attachments'];
            if (!is_array($attachments)) {
                if (strpos($attachments, ',') !== false) {
                    $attachments = explode(',', $attachments);
                } else {
                    $attachments = array($attachments);
                }
            }

            foreach ($attachments as $attachment) {
                $attachment = trim($attachment);
                if (is_file($attachment)) {
                    $this->_attachments[] = $attachment;
                }
            }
        }
    }

    /**
     * Checks if all needed data is set.
     *
     * @return bool
     */
    public function canExecute()
    {
        if ($this->_from === null) {
            return false;
        }
        if ($this->_to === null) {
            return false;
        }
        if ($this->_subject === null) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->canExecute()) {
            return false;
        }

        $separator = md5(date('r', time()));
        $eol = PHP_EOL;

        $to = implode(',', $this->_to);

        // main header
        $headers = 'From: ' . $this->_from . $eol;
        $headers .= 'To: ' . $to . $eol;
        $headers .= 'MIME-Version: 1.0' . $eol;
        $headers .= 'Content-Type: multipart/mixed; boundary="' . $separator . '"' . $eol . $eol;
        $headers .= 'Content-Transfer-Encoding: 7bit' . $eol;
        $headers .= 'This is a MIME encoded message.' . $eol . $eol;

        if ($this->_htmlMessage) {
            // message html
            $headers .= '--' . $separator . $eol;
            $headers .= 'Content-Type: text/html; charset="iso-8859-1"' . $eol;
            $headers .= 'Content-Transfer-Encoding: 8bit' . $eol . $eol;
            $headers .= $this->_message . $eol . $eol;
        } else {
            // message plain
            $headers .= '--' . $separator . $eol;
            $headers .= 'Content-Type: text/plain; charset="iso-8859-1"' . $eol;
            $headers .= 'Content-Transfer-Encoding: 8bit' . $eol . $eol;
            $headers .= strip_tags($this->_message) . $eol . $eol;
        }

        if (is_array($this->_attachments) && !empty($this->_attachments)) {
            foreach ($this->_attachments as $attachment) {
                $fileName = basename($attachment);
                $base64File = chunk_split(base64_encode(file_get_contents($attachment)));

                $headers .= '--' . $separator . $eol;
                $headers .= 'Content-Type: application/octet-stream; name="' . $fileName . '"' . $eol;
                $headers .= 'Content-Transfer-Encoding: base64' . $eol;
                $headers .= 'Content-Disposition: attachment' . $eol . $eol;
                $headers .= $base64File . $eol . $eol;
                $headers .= '--' . $separator . '--';
            }
        }

        $mail_sent = @mail($to, $this->_subject, '', $headers);

        return $mail_sent;
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration()
    {
        $attachments = $this->_attachments;
        if ($this->_attachments) {
            foreach ($this->_attachments as $attachment) {
                $attachments[] = basename($attachment);
            }
        }

        return [
            'event'        => $this->_eventName,
            'from'         => $this->_from,
            'to'           => implode(',', $this->_to),
            'subject'      => $this->_subject,
            'message'      => $this->_message,
            'html_message' => (int)$this->_htmlMessage,
            'attachments'  => implode(',', $attachments),
        ];
    }
}
