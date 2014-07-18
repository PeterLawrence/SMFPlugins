<?php
	/*
	SMF Picasa Gallery Mod
	Version 0.3
	November 2008
	P.J.Lawrence
	*/

	// <code>picasagalleryinstallSMF2.php</code>
	
	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	  require_once(dirname(__FILE__) . '/SSI.php');
	// no SSI.php and no SMF?
	elseif (!defined('SMF'))
	  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');
	global $modSettings, $smcFunc, $db_prefix;
	
	// Insert the settings
	if (empty($modSettings['gallery_picasa_server_url']))
	{
		// SMF 2
		$smcFunc['db_insert']('ignore', $db_prefix . 'settings', array('variable' => 'string', 'value' => 'string'), array('gallery_picasa_server_url', 'http://picasaweb.google.com'), '');
	}
?>