<?php

// Grab an XML file given an IP, port, path, and authentication info.

function uhqradiobasic_fetchxml ($ipfqdn, $port, $xmlpath, $auth, &$xmldata)
{
	$cachefile = 'modules/uhq_radiobasic/cache/xml_'.$ipfqdn.'_'.$port.'.xml';
	
	// Load Module Config

	$modhandler			=& xoops_gethandler('module');
	$xoopsModule		=& $modhandler->getByDirname('uhq_radio');
	$config_handler		=& xoops_gethandler('config');
	$xoopsModuleConfig	=& $config_handler->getConfigsByCat(0,$xoopsModule->getVar('mid'));
	
	// Check cache.  Use cached data if it's not too old.
	
	if ( file_exists($cachefile) ) {
		if ( (time() - filemtime($cachefile)) > $xoopsModuleConfig['cache_time'] ) {
			// Don't read the cache if the data is old.
		} else {
			// Read cache and return from the function.
			$cp=fopen($cachefile,"r");
			$xmldata=fread($cp, filesize($cachefile));
			fclose($cp);
			return false;
		}
	}
	
	// Get XML File!
	
	$fp=fsockopen($ipfqdn,$port,$errno,$errstr,1);

	if (!$fp) {
		return $errno;
	}

	$httpreq = "GET ".$xmlpath." HTTP/1.0\r\n";
	$httpreq .= "User-Agent: Mozilla/3.0 (compatible; XOOPS) UHQ-Radio\r\n";
	$httpreq .= "Authorization: Basic ".$auth."\r\n\r\n";

	fwrite($fp,$httpreq);

	$xmldata = '';
	while (!feof($fp))
		$xmldata .= fgets($fp,512);
	fclose($fp);

	return false;
}

// Isolate XML between specified tags.

function uhqradiobasic_isolatexml ($input,$tagA,$tagB)
{
	$line = substr($input, strpos($input,$tagA)+strlen($tagA));
	$output = substr($line,0,strpos($line,$tagB));

	return trim($output);
}

// Take a given title, and make and split out artist and song.

function uhqradiobasic_titlesplit ($title,&$artist,&$song)
{
	$endpos = strpos($title," - ");
	$artist = substr($title,0,$endpos);
	$song = substr($title,($endpos+3));
	
	return true;
}

?>