<?php
	/*
	SMF Picasa Gallery Mod
	Version 0.3
	November 2008
	P.J.Lawrence
	*/

	// <code>picasagalleryinstall.php</code>
	
	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	  require_once(dirname(__FILE__) . '/SSI.php');
	// no SSI.php and no SMF?
	elseif (!defined('SMF'))
	  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');
	global $modSettings;
	
	// Insert the settings
	if (empty($modSettings['gallery_picasa_server_url']))
	{
		// SMF 1
		db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('gallery_picasa_server_url','http://picasaweb.google.com')", __FILE__, __LINE__);
	}
?>