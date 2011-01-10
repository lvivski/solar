<?php
class Log
{
    protected $_fid, $_prefix;

    const LOG_ERROR   = 'EE',
          LOG_WARNING = 'WW',
          LOG_INFO    = 'II',
          LOG_DEBUG   = 'DD';

    public function __construct($file = '')
    {
		$this->_fid = fopen($file, 'a+');
	}

    public function error($message)
    {
		$this->_log($message, self::LOG_ERROR);
	}

	public function warn($message)
    {
		$this->_log($message, self::LOG_WARNING);
	}

	public function info($message)
    {
		$this->_log($message, self::LOG_INFO);
	}

	public function debug($message)
    {
		$this->_log($message, self::LOG_DEBUG);
	}

    public static function rotate($file)
    {
        $file .= '.log';
        if (is_file($file)) {
            $log = tempnam(dirname($file),'.tmp');
            unlink($log);
            rename($file,$log);
            $arch = '/arch_'.$file.'_'.date('Y-m-d').'.log';
            $fbuffer = file_get_contents($log);
            file_put_contents($arch, $fbuffer, FILE_APPEND);
            unlink($log);
        }
    }

    protected function _log($msg, $level)
    {
        $locked = false;
		while (! $locked) {
			usleep(100);
			$locked = flock($this->_fid, LOCK_EX);
		}
		fwrite($this->_fid, $this->_formatMessage($msg, $level));
		flock($this->_fid, LOCK_UN);
		fclose($this->_fid);
		return true;
    }

    protected function _formatMessage($message, $level)
    {
		return date('Y-m-d H:i:s')." ({$level}): {$message}\n";
	}
}