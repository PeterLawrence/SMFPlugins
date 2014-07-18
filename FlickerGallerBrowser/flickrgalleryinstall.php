<?php
	/*
	SMF Flickr Gallery Browser Mod
	Version 0.2
	November 2008
	P.J.Lawrence
	*/

	// <code>flickrgalleryinstall.php</code>
	
	if ( !function_exists('sys_get_temp_dir') )
	{
		// Based on http://www.phpit.net/
		// article/creating-zip-tar-archives-dynamically-php/2/
		// http://uk3.php.net/manual/en/function.sys-get-temp-dir.php
		function sys_get_temp_dir()
		{
			// Try to get from environment variable
			if ( !empty($_ENV['TMP']) )
			{
				return realpath( $_ENV['TMP'] );
			}
			else if ( !empty($_ENV['TMPDIR']) )
			{
				return realpath( $_ENV['TMPDIR'] );
			}
			else if ( !empty($_ENV['TEMP']) )
			{
				return realpath( $_ENV['TEMP'] );
			}

			// Detect by creating a temporary file
			else
			{
				// Try to use system's temporary directory
				// as random name shouldn't exist
				$temp_file = tempnam( md5(uniqid(rand(), TRUE)), '' );
				if ( $temp_file )
				{
					$temp_dir = realpath( dirname($temp_file) );
					unlink( $temp_file );
					return $temp_dir;
				}
				else
				{
					return FALSE;
				}
			}
		}
	}
	
	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	  require_once(dirname(__FILE__) . '/SSI.php');
	// no SSI.php and no SMF?
	elseif (!defined('SMF'))
	  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');
	global $modSettings, $db_prefix;
	
	// Insert the settings
	if (empty($modSettings['gallery_flickr_cache_folder']))
	{
		global $cachedir;
		$flickrcachedir='';
		if (!empty($cachedir))
		{
			// smf cache folder
			if (file_exists ($cachedir))
			{
				$flickrcachedir = $cachedir;
			}
		}
		
		if (empty($flickrcachedir))
		{
			// try system temp
			$temppath=sys_get_temp_dir();
			if (!empty($temppath))
			{
				// remove last / or \ since I don't need them
				$temppath = preg_replace("/\/$/", "", $temppath);  // unix etc
				$temppath = preg_replace('/\\\$/', "", $temppath); // for ms systems
				if (file_exists ($temppath))
				{
					$flickrcachedir = $temppath;
				}
			}
		}

		if (!empty($flickrcachedir))
		{
			if (isset($smcFunc))
			{
				// SMF 2
				$smcFunc['db_insert']('ignore', $db_prefix . 'settings', array('variable' => 'string', 'value' => 'string'), array('gallery_flickr_cache_folder', "$flickrcachedir"), '');
			}
			else
			{
				$flickrcachedir=addslashes($flickrcachedir); // only need to do this for SMF 1
				// SMF 1
				db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('gallery_flickr_cache_folder',\"$flickrcachedir\")", __FILE__, __LINE__);
			}
		}
	}
?>