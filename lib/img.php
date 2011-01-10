<?php
class Img
{
	protected  $img,
	           $xs,$ys,
	           $last_x,$last_y;
	const IMG_WIDTH = 1,
	      IMG_HEIGHT = 2,
          IMG_MOST = 4,
	      IMG_ONLYBIG = 8,
	
	      IMG_TYPE_JPG = 0,
	      IMG_TYPE_GIF = 1,
	      IMG_TYPE_PNG = 2,
	
	      IMG_POPULAR = 15;
	
	
	function __construct($fname='')
	{
		if (mb_strlen($fname))	
			$this->load($fname);
	}
	function load($fname)
	{
		@$this->img = imagecreatefromjpeg($fname);
		if (! $this->img)
 			@$this->img = imagecreatefromgif($fname);
		if (! $this->img)
			 @$this->img = imagecreatefrompng($fname);
		
		if (! $this->img)
			return(0);
		$this->xs = imagesx($this->img);
		$this->ys = imagesy($this->img);
		return (1);
	}
    
	function getResized($flags, $width = 0, $height = 0, $type = IMG_TYPE_JPG, $fname = null, $bgcolor = '#ffffff')
	{
		if(!$this->img)
			return 0;
		settype($flags,'integer');
		$paramX=0;
		$paramY=0;
		if ($flags & self::IMG_WIDTH)
			$paramX = ($width > $this->xs);
		if ($flags & self::IMG_HEIGHT)
			$paramY = ( $height > $this->ys);
		$param = ($paramX && $paramY);
		if (($flags & self::IMG_ONLYBIG) && ($param)) {
			$dim = imagecreatetruecolor($this->xs,$this->ys);
			imagecopyresampled($dim, $this->img, 0, 0, 0, 0, $this->xs, $this->ys, $this->xs, $this->ys);
			switch($type) {
				case self::IMG_TYPE_JPG:
                    imagejpeg($dim, $fname);
                    break;
				case self::IMG_TYPE_GIF: 
                    imagegif($dim, $fname);
                    break;
				case self::IMG_TYPE_PNG: 
                    imagepng($dim, $fname);
                    break;		
			}
			if ($fname != null)
				chmod($fname, 0777);
			imagedestroy($dim);
			return array('x' => $this->xs, 'y' => $this->ys);;
		}
		$xs = $this->xs;
		$ys = $this->ys;
		
		if (! $flags & self::IMG_MOST) {
			if ($flags & self::IMG_WIDTH)
				$xs = $width;
			if ($flags & self::IMG_HEIGHT)
				$ys = $height;
		} else {
			if ($width >= $height) {
				$xs = intval(floatval($this->xs) * $height / floatval($this->ys));
				$ys = $height;
			} else {
				$xs = $width;
				$ys = intval(floatval($this->ys) * $width / floatval($this->xs));
			}
		}
        
		$dim = imagecreatetruecolor($width, $height);
		$bg_col = sscanf($bgcolor, '#%2x%2x%2x');
		$bgcol = imagecolorallocate($dim, $bg_col[0], $bg_col[1], $bg_col[2]);
		imagefill($dim, 0, 0, $bgcol);
		$st_x = 0;
		if ($xs < $width)
			$st_x = intval((floatval($width) - floatval($xs)) /2);
		imagecopyresampled($dim, $this->img, $st_x, 0, 0, 0, $xs, $ys, $this->xs, $this->ys);
		switch($type) {
			case self::IMG_TYPE_JPG:
                imagejpeg($dim, $fname);
                break;
			case self::IMG_TYPE_GIF:
                imagegif($dim, $fname);
                break;
			case self::IMG_TYPE_PNG:
                imagepng($dim, $fname);
                break;
			
		}
		if ($fname != null)
			chmod($fname, 0777);
		imagedestroy($dim);
		return array('x' => $xs, 'y' => $ys);
	}
}
