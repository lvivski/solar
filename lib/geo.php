<?php
class Geo
{
	public static function country($ip = '')
	{
		if (! $ip)
		    $ip = self::getip();
		return strtolower(geoip_country_code_by_name($ip));
	}

  public static function ruRegion($ip='')
	{
	  $ru = array('ru', 'ua', 'by', 'kz', 'md', 'ge');
		$country = self::country($ip);
		return in_array($country, $ru);
	}

	public static function region($ip='')
	{
        if (! $ip)
		    $ip = self::getip();
        
        $data = geoip_region_by_name($ip);
        
        return strtolower($data['region']);
	}

	public static function & getIp()
	{
		if (! empty($_SERVER['HTTP_CLIENT_IP'])) {  //check ip from share internet
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  //to check ip is pass from proxy
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			return $_SERVER['REMOTE_ADDR'];
		}
	}
}