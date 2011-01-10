<?php
class MailException extends Exception
{}

class Mail
{
	protected $_parts = array(),
	          $_mime = null,
	          $_charset = null,
	          $_headers = array(),
	          $_from = null,
	          $_to = array(),
	          $_recipients = array(),
	          $_returnPath = null,
	          $_subject = null,
	          $_bodyText = false,
	          $_bodyHtml = false,
	          $_body = false,
	          $_type = null;

	public $EOL = "\n";

	public function __construct($charset = 'UTF-8')
    {
		$this->_charset = $charset;
	}

	public function setBody($txt, $type = 'html', $encoding = Mime::ENCODING_BASE64)
    {
		switch ($type) {
			case 'html':
				$this->_type = Mime::TYPE_HTML;
				break;
			case 'txt':
			default:
				$this->_type = Mime::TYPE_TEXT;
				break;
		}
		$this->_body = $txt;
		return $this;
	}

	public function setFrom($email, $name = '')
    {
		if ($this->_from === null) {
			$email = strtr ( $email, "\r\n\t", '???' );
			$this->_from = $email;
			if ($name) {
				$this->_storeHeader('From',$this->_encodeHeader('"'.$name.'"').' <'.$email.'>', true);
			} else {
				$this->_storeHeader('From', $email, true);
			}
		}
        return $this;
	}

	protected function _storeHeader($headerName, $value, $append = false)
	{
		$value = strtr ($value, "\r\n\t", '???');
		if (isset($this->_headers[$headerName])) {
			$this->_headers[$headerName][] = $value;
		} else {
			$this->_headers[$headerName] = array($value);
		}
		if ($append) {
			$this->_headers[$headerName]['append'] = true;
		}
	}

	protected function _encodeHeader($value)
	{
		if (Mime::isPrintable($value)) {
			return $value;
		} else {
			$quotedValue = Mime::encodeBase64($value);
			//$quotedValue = str_replace ( array ('?', ' ' ), array ('=3F', '=20' ), $quotedValue );
			return '=?' . $this->_charset . '?B?' . $quotedValue . '?=';
		}
	}

	public function addTo($email, $name = '')
    {
		$this->_addRecipientAndHeader('To', $name, $email);
		return $this;
	}

	protected function _addRecipientAndHeader($headerName, $name, $email)
    {
		$email = strtr($email, "\r\n\t", '???');
		$this->_addRecipient($email, ($headerName=='To')?true:false);
		if ($name != '') {
			$name = '"'.$this->_encodeHeader($name).'" ';
		}
	}

	protected function _addRecipient($email, $to = false)
    {
		// prevent duplicates
		$this->_recipients [$email] = 1;
		if ($to) {
			$this->_to[] = $email;
		}
	}

	public function setSubject($subject)
    {
		if ($this->_subject === null) {
			$subject = strtr($subject, "\r\n\t",'???');
			$this->_subject = $this->_encodeHeader($subject);
		} else {
			throw new MailException('Subject set twice');
		}
		return $this;
	}

	protected function _prepareHeaders()
    {
		$headers = '';
		$this->_storeHeader('Content-Type', "$this->_type; charset=$this->_charset");
		foreach ($this->_headers as $header => $content) {
			if (isset($content['append'])) {
				unset($content['append']);
				$value = implode(',' . $this->EOL . ' ', $content);
				$headers.= $header . ': ' . $value . $this->EOL;
			} else {
				array_walk($content, array('mail', '_formatHeader'), $header);
				$item = $prefix . ': ' . $item;
				$headers .= implode($this->EOL, $content) . $this->EOL;
			}
		}
		return $headers;
	}

	protected function _formatHeader(&$item, $key, $prefix)
    {
		$item = $prefix . ': ' . $item;
	}

	public function send()
    {
		$recipients = implode(',', array_keys($this->_recipients));
		return mail($recipients, $this->_subject, $this->_body, $this->_prepareHeaders(), "-f$this->_from");
	}

	public static function valid_address($address)
    {
		//Generic email
		if (preg_match('/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', $address)) {
			return true;
		//rare but valid email
		} elseif (preg_match(":^[a-z0-9,!#\$%&'\*\+/=\?\^_`\{\|}~-]+(\.[a-z0-9,!#\$%&'\*\+/=\?\^_`\{\|}~-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.([a-z]{2,})$:i", $address)) {
			return true;
		//well, it's not an email
		}
        return false;
	}
}