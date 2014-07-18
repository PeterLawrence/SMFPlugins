<?php
/*
SMF Flickr Gallery Browser Mod
Version 0.6
November 2010
P.J.Lawrence
*/

if (!defined('SMF'))
	die('Hacking attempt...');

require_once("phpFlickr/phpFlickr.php");
//
// Extract the public sets from a Flickr account
//
function getSets($person,$f)
{
	$photosets = $f->photosets_getList($person['id']);
    $sets = array();
    if (empty($photosets)) {
        return $sets;
    }

	$i = 0;
	foreach ((array)$photosets['photoset'] as $photoset)
	{
		$info = $f->photos_getInfo($photoset['primary']);
		
		$row = array();		
		$row['id'] = $i;
		$row['title'] = $photoset['title'];
		$row['description'] = $photoset['description'];
		$row['thumbnail'] = "http://static.flickr.com/$photoset[server]/$photoset[primary]_$photoset[secret]_s.jpg";	
		$row['slug'] = $photoset['id'];
	
		$sets[$i] = $row;
		$i++;
	}
	return $sets;
}
//
// Extract the public groups from a Flickr account
//
function getGroups($person,$f)
{
    $groups = $f->people_getPublicGroups($person['id']);
	$groupphotos = array();
    if (empty($groups)) {
        return $groupphotos;
    }
    $i=0;
    for ( $i = 0; $i < count($groups); $i++ ) 
    {
        //$photos_getInfo = $f->groups_pools_getPhotos($groups[$i]['nsid']);
        $photos = $f->groups_pools_getPhotos($groups[$i]['nsid'], NULL, NULL, NULL, 1);

        foreach ((array)$photos['photo'] as $photo) {		
            $row = array();		
            $row['id'] = $i;
            $row['title'] = $groups[$i]['name'] ;
            $row['description'] = '';
            $row['thumbnail'] = $f->buildPhotoURL($photo, "Square");	
            $row['slug'] = $i+1;
            $groupphotos[$i] = $row;
       }
	}
	return $groupphotos;
}
//
// extract the photos from a Flickr set: title, description, thumbnails, url
//
function getFlickrSetPhotos($person,$f,$photoset_id,$title) 
{
    global $FlickrPage,$MaxImages,$max_page;
    if ($FlickrPage==0) $FlickrPage=1;
	$setphotos = $f->photosets_getPhotos($photoset_id, NULL, NULL,$MaxImages,$FlickrPage);
    if (empty($setphotos)) {
        return null;
    }
	$setinfo=$f->photosets_getInfo($photoset_id);
	$title=$setinfo['title'];
    $max_page = $setphotos['pages'];
    $MaxImages = $setphotos['total'];
	
	$photos = array();
	$i = 0;
	foreach ((array)$setphotos['photo'] as $photo)
	{
			$info = $f->photos_getInfo($photo['id']);
			$image_size=$f->photos_getSizes($photo['id']);
			$row = array();		
			$row['id'] = $i;			
			$row['gid'] = $photo['id'];
			$row['title'] = $photo['title'];
			$row['height'] = $image_size[4]['height']; 
			$row['width'] = $image_size[4]['width']; 
			$row['size'] = '10'; //round($photo['gphoto']['size']/102.40)/10.0;
			$row['timestamp'] = $info['dates']['posted'];// lastupdate
			$row['taken'] = $info['dates']['taken'];
			$row['description'] = $info['description'];
			$row['thumbnail72'] = $f->buildPhotoURL($photo, "square");
			$row['thumbnail144'] = $f->buildPhotoURL($photo, "thumbnail");
			$row['thumbnail288'] = $f->buildPhotoURL($photo, "small");
			$row['url'] = $f->buildPhotoURL($photo, "medium");

			$photos[$i] = $row;
			$i++;
	}
	return $photos;
}
//
// extract the photos from a Flickr group: title, description, thumbnails, url
//
function getFlickrGroupPhotos($person,$f,$photoset_id,$title) 
{
    global $FlickrPage,$MaxImages,$max_page;
    if ($FlickrPage==0) $FlickrPage=1;
    $groups = $f->people_getPublicGroups($person['id']);
    if (empty($groups)) {
        return null;
    }
    $setphotos = $f->groups_pools_getPhotos($groups[$photoset_id-1]['nsid'], NULL, NULL, NULL, $MaxImages,$FlickrPage);
    if (empty($setphotos)) {
        return null;
    }
    $max_page = $setphotos['pages'];
    $MaxImages = $setphotos['total'];
	 
	$photos = array();
	$i = 0;
	foreach ((array)$setphotos['photo'] as $photo)
	{
			$info = $f->photos_getInfo($photo['id']);
			$image_size=$f->photos_getSizes($photo['id']);
			$row = array();		
			$row['id'] = $i;			
			$row['gid'] = $photo['id'];
			$row['title'] = $photo['title'];
			$row['height'] = 100;//$image_size[4]['height']; 
			$row['width'] = 100; //$image_size[4]['width']; 
			$row['size'] = '10'; //round($photo['gphoto']['size']/102.40)/10.0;
			$row['timestamp'] = $info['dates']['posted'];// lastupdate
			$row['taken'] = $info['dates']['taken'];
			$row['description'] = $info['description'];
			$row['thumbnail72'] = $f->buildPhotoURL($photo, "square");
			$row['thumbnail144'] = $f->buildPhotoURL($photo, "thumbnail");
			$row['thumbnail288'] = $f->buildPhotoURL($photo, "small");
			$row['url'] = $f->buildPhotoURL($photo, "medium");

			$photos[$i] = $row;
			$i++;
	}
	return $photos;
}
//
//  Removes a parameter from a URL
//
function RemoveFromUrl($TheURL,$TheParm)
{
	if (strpos($TheURL, "&{$TheParm}=")==false) // remove $TheParm]tag
	{
		if (strpos($TheURL, "?{$TheParm}=")!=false)
		{
			if (strpos($TheURL, '&')==false)
			{
				$TheURL = str_replace("?{$TheParm}=".$_GET["$TheParm"],'',$TheURL); // remove $TheParm link
			}
			else
			{
				$TheURL = str_replace("?{$TheParm}=".$_GET["$TheParm"].'&','?',$TheURL); // remove $TheParm link
			}
		}
		else if (strpos($TheURL, "/{$TheParm},")!=false) //  using search engine friendly URLs
		{
			$TheURL = str_replace("/{$TheParm},".$_GET["$TheParm"],'',$TheURL); // remove $TheParm link
		}
	}
	else
	{
		$TheURL = str_replace("&{$TheParm}=".$_GET["$TheParm"],'',$TheURL); // remove $TheParm link
	}
	return ($TheURL);
}
//
//  Formats a parameter for use in a URL
//
function FormatParmForUrl($TheURL,$TheParm,$UsingOldSearchFriendlyURLs=0,$MustUseAnd=false)
{
	if ($UsingOldSearchFriendlyURLs==1) //  using search engine friendly URLs
	{
		$TheParm="/{$TheParm},";
	}
	elseif (strpos($TheURL , '?')===false)
	{
        if ($MustUseAnd) {
            // override
            $TheParm="&amp;{$TheParm}=";
        }
        else{
     		$TheParm="?{$TheParm}=";
       }
	}
	else {
		$TheParm="&amp;{$TheParm}=";
	}
	return ($TheParm);
}
//
// Main template code
//
function FlickrGalleryBrowser()
{
	// SMF Globals
	global $modSettings,$context,$mbname, $txt, $scripturl,$settings;
	
	// Flickr Browser Globals
	global $FlickrSetID,$FlickrSetTitle,$UsingOldSearchFriendlyURLs,$FlickrCols,$FlickrUserURL;
	global $FlickrPhotos,$FlickrSets,$FlickrErrorMessage,$FlickrServerURL,$FlickrPhotoURL;
	global $FlickrWarningMessage;
    global $FlickrPage,$MaxImages,$max_page,$DisplayGroups;
	
	// Set the page title
	$context['page_title'] = $mbname . ' ' . $txt['flickr_table_title'];
    
    $DisplayGroups=false;
    if (!empty($modSettings['gallery_flickr_mode']) && $modSettings['gallery_flickr_mode']==1) {
        $DisplayGroups=true;
    }
    if (empty($modSettings['gallery_flickr_columns']) || $modSettings['gallery_flickr_columns']<1)
	{
		$FlickrCols = 6;
	}
	else
	{
		$FlickrCols = $modSettings['gallery_flickr_columns'];
	}
    $FlickrRows=4; // default four rows
    if (!empty($modSettings['gallery_flickr_rows']) && $modSettings['gallery_flickr_rows']>0)
	{
		$FlickrRows = $modSettings['gallery_flickr_rows'];
	}
    $MaxImages=$FlickrCols*$FlickrRows; 
    $max_page=1;
    $FlickrPage=0;
	
	// Link Tree
	if (empty($modSettings['gallery_flickr_fixed_setgroup']) && !empty($modSettings['gallery_flickr_default_setgroup']))
	{
		$context['linktree'][] = array(
						'url' => $scripturl . '?action=flickrgallerybrowser&ListSets=1',
						'name' => $txt['flickr_table_title']
					);
	}
	else
	{
		$context['linktree'][] = array(
						'url' => $scripturl . '?action=flickrgallerybrowser',
						'name' => $txt['flickr_table_title']
					);
	}
	
	$FlickrAPIKey='';
	$FlickrSetTitle='';
	// check settings
	if (empty($modSettings['gallery_flickr_API_Key']))
	{	
	    $FlickrErrorMessage = '<br /><b>'.$txt['no_flickr_APIKey'].'</b><br />';
	}
	else
	{
		$FlickrAPIKey = $modSettings['gallery_flickr_API_Key']; // Link to your Flickr Server URL
		$f = new phpFlickr($FlickrAPIKey);
	
	    //  If selected enable cacheing 
		$DBCacheEnabled=FALSE;
		if (!empty($modSettings['gallery_flickr_enable_db_catch']))
		{
			// Database cache: Supports all SMF databases types MySQL,PostgreSQL and SQLite, plus many more
			global $db_type,$db_server,$db_name,$db_user,$db_passwd;
			if (empty($db_type))
			{
				// smf 1 database, mysql only
				$DBCacheEnabled=$f->enableCache("db","mysql://$db_user:$db_passwd@$db_server/$db_name");
			}
			else
			{
				// smf 2 database
				if ($db_type == 'postgresql') // this acronym is different for SMF and PEAR lib 
				{
					$DBCacheEnabled=$f->enableCache("db","pgsql://$db_user:$db_passwd@$db_server/$db_name");
				}
				else
				{
					$DBCacheEnabled=$f->enableCache("db","$db_type://$db_user:$db_passwd@$db_server/$db_name");
				}
			}
			if (!$DBCacheEnabled)
			{
				$FlickrWarningMessage=$txt['flickr_db_cache_failed'];
			}
		}
		
		if (!$DBCacheEnabled)
		{
			// if DB cache not working try File cache option if enabled
			if (!empty($modSettings['gallery_flickr_enable_file_catch']) && !empty($modSettings['gallery_flickr_cache_folder']))
			{
				$FSCacheEnabled=FALSE;
				if (file_exists ($modSettings['gallery_flickr_cache_folder']))
				{
					$FSCacheEnabled=$f->enableCache("fs", $modSettings['gallery_flickr_cache_folder']);
				}

				if (!$FSCacheEnabled)
				{
					if (empty($FlickrWarningMessage))
					{
						$FlickrWarningMessage=$txt['flickr_fs_cache_failed'];
					}
					else
					{
						$FlickrWarningMessage.="<br />{$txt['flickr_fs_cache_failed']}";
					}
				}
			}
		}
	}
	
	$FlickrPhotoURL = '';
	if (empty($modSettings['gallery_flickr_account']))
	{	
		$FlickrErrorMessage .= '<br /><b>'.$txt['no_flickr_account'].'</b><br />';
	}
	else
	{
		if (!empty($f))
		{
			$FlickrID = $modSettings['gallery_flickr_account'];
			
			$person = $f->people_findByUsername($FlickrID);
			if (!empty($person))
			{
				$PersonInfo=$f->people_getInfo($person['id']);
				if (!empty($PersonInfo))
				{
					$FlickrPhotoURL =  $PersonInfo['photosurl'];
				}
			}
		}
	}
	
	if (!empty($FlickrErrorMessage))
	{
		// Error message 
		// Load the main flickr gallery template and finish
        $MaxImages=0;
		loadtemplate('flickrgallerybrowser');
		return;
	}
	
	// see what should be displayed
	if (!empty($modSettings['gallery_flickr_default_setgroup']))
	{
		// default set specified
		if (empty($_GET['ListSets']) || !empty($modSettings['gallery_flickr_fixed_setgroup']))
		{
			// identify set id
            if ($DisplayGroups) 
                $FlickrSets=getGroups($person,$f);
            else
                $FlickrSets=getSets($person,$f);
			if (empty($FlickrSets))
			{
                $MaxImages=0;
                if ($DisplayGroups) {
                    $FlickrErrorMessage = '<br /><b>'.$txt['no_flickr_groups'].'</b><br />';
                }
                else {
                    $FlickrErrorMessage = '<br /><b>'.$txt['no_flickr_sets'].'</b><br />';
                }
				loadtemplate('flickrgallerybrowser');
				return;
			}
			else
			{
				$FlickrSetID = $modSettings['gallery_flickr_default_setgroup'];
				foreach($FlickrSets as $set) 
				{
					if ($modSettings['gallery_flickr_default_setgroup']==$set['title'])
					{
						$FlickrSetID = $set['slug'];
					}
				}
			}
		}
	}
	if (empty($modSettings['gallery_flickr_fixed_setgroup']))
	{
		if (!empty($_GET['ListSets']))
		{
			if ($_GET['ListSets']=='1')
			{
				$FlickrSetID='';
			}
		}
		
		if (!empty($_GET['setid']))
		{
			$FlickrSetID = $_GET['setid'];
            $DisplayGroups=false;
		}
        if (!empty($_GET['groupid']))
		{
			$FlickrSetID = $_GET['groupid'];
            $DisplayGroups=true;
		}
	}
	else
	{
		if (empty($FlickrSetID))
		{
            $MaxImages=0;
			$FlickrErrorMessage = '<br /><b>'.$txt['no_flickr_default_set'].'</b><br />';
			loadtemplate('flickrgallerybrowser');
			return;
		}
	}
    
    if (!empty($_GET['pageid'])) {
        $FlickrPage = $_GET['pageid'];
    }
	
	// find out what format the URL is in.
	$FlickrUserURL = $_SERVER["REQUEST_URI"];
	$UsingOldSearchFriendlyURLs=0;
	if (strpos($FlickrUserURL, '/action,')!=false && strpos($FlickrUserURL, '.html')==false && strpos($FlickrUserURL, 'index.php')!=false)
	{
		$UsingOldSearchFriendlyURLs=1; //  using old search engine friendly URLs
	}
	
	if (!empty($FlickrSetID))
	{	
		$FlickrSetURL=$FlickrUserURL;
		$FlickrUserURL=RemoveFromUrl($FlickrUserURL,'setid');
        $FlickrUserURL=RemoveFromUrl($FlickrUserURL,'groupid');
        $FlickrUserURL=RemoveFromUrl($FlickrUserURL,'pageid');

		//	3) These CSS-styles are necessary for the script to work. You may also put
		//	them in an external CSS-file. See the webpage for documentation.
	
		$context['html_headers'] =  $context['html_headers'] . '
		<link rel="stylesheet" type="text/css" href="'.$settings['default_theme_url'].'/flickrhighslide/flickrhs.css" />';
        if ($DisplayGroups)
            $FlickrPhotos = getFlickrGroupPhotos($person,$f,$FlickrSetID,&$FlickrSetTitle);
        else
            $FlickrPhotos = getFlickrSetPhotos($person,$f,$FlickrSetID,&$FlickrSetTitle);
		//echo '$FlickrSetTitle ',$FlickrSetTitle; echo '<br />';
		if (empty($modSettings['gallery_flickr_fixed_setgroup']))
		{
			// Add link tree if they can browse sets
			if (empty($FlickrSetTitle))
			{
				$context['linktree'][] = array(
							'url' => $FlickrSetURL,
							'name' => $FlickrSetID
						);
			}
			else
			{
				$context['linktree'][] = array(
							'url' => $FlickrSetURL,
							'name' => $FlickrSetTitle
						);
			}
		}
        else {
            $MaxImages=0;
        }
	}
	else
	{
        if (!empty($_GET['pageid'])) // remove pageid tag
        {
            $FlickrUserURL=RemoveFromUrl($FlickrUserURL,'pageid');
        }
        if ($DisplayGroups) 
            $FlickrSets=getGroups($person,$f);
        else
            $FlickrSets=getSets($person,$f);
        if (!empty($FlickrSets))
        {
          //global $FlickrPage,$MaxImages,;
          $ImageCount = count($FlickrSets);
          if ($ImageCount>$MaxImages) {
              if ($FlickrPage<1) $FlickrPage=1;
              $max_page = ceil($ImageCount/$MaxImages);
              if ($FlickrPage>$max_page) {
                $FlickrPage=$max_page;
              }
              $StartIndex=($FlickrPage-1)*$MaxImages;
              $FlickrSets=array_splice($FlickrSets, $StartIndex);
              $EndIndex=$MaxImages;
              $FlickrSets=array_splice($FlickrSets, 0,$EndIndex);
          }
          $MaxImages=$ImageCount;
        }
        else {
            $MaxImages=0;
        }
	}
	
	if (!empty($_GET['ListSets'])) // remove ListSets tag
	{
	    $FlickrUserURL=RemoveFromUrl($FlickrUserURL,'ListSets');
	}

	// Load the main flickr gallery template
	loadtemplate('flickrgallerybrowser');
}
?>