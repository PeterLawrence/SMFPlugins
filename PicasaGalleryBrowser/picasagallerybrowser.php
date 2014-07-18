<?php
/*
SMF Picasa Gallery Browser Mod
Version 0.3
November 2008
P.J.Lawrence
*/

if (!defined('SMF'))
	die('Hacking attempt...');

require_once('rss-functions-mod.php');
//
// Extract the public albums from a Picasa account
//
function getAlbums($baseurl, $user, $access = 'public') {
	$feedURL = $baseurl.'/data/feed/base/user/'.$user.'?kind=album&alt=rss&hl=en_US&access='.$access;
	$feedContent = fetch_rss_mod($feedURL);
	
	// try to get the content for three more times in case the above call failed
	for ($i = 0; $i < 3; $i++) {
		if ( (!is_object($feedContent)) || (!is_array($feedContent->items)) || (sizeof($feedContent->items) == 0)) {
			$feedContent = fetch_rss_mod($feedURL);
		} else {
			break;
		}
	}
	$PicasaAlbums = array();
	$i = 0;
	if (is_object($feedContent) && is_array($feedContent->items)) {
		foreach ($feedContent->items as $item) { 
			$row = array();		
			$row['id'] = $i;
			$row['timestamp'] = $item['date_timestamp'];
			$row['title'] = $item['title'];
			if (empty($item['media']['group_description']))
			{
				$row['description'] = '';
			}
			else
			{
				$row['description'] = $item['media']['group_description'];
			}
			$row['thumbnail'] = $item['media']['group_thumbnail@url'];
			
			$link = $item['link'];
			$pieces = explode("/", $link);			
			$row['slug'] = $pieces[sizeof($pieces) - 1];
		
			$PicasaAlbums[$i] = $row;
			$i++;
		}
	}
	return $PicasaAlbums;
}
//
// Extract the photos from a Picasa album: title, description, thumbnails, url
//
function getPicasaPhotos($baseurl, $user, $album, $access = 'public') {
	$feedURL = $baseurl.'/data/feed/api/user/'.$user.'/album/'.$album.'?kind=photo&alt=rss';
	//echo $feedURL."<br>";
	$feedContent = fetch_rss_mod($feedURL);

	// try to get the content for three more times in case the above call failed
	for ($i = 0; $i < 3; $i++)
	{
		if ((!is_object($feedContent)) || (!is_array($feedContent->items)) || (sizeof($feedContent->items) == 0)) {
			$feedContent = fetch_rss_mod($feedURL);
		} 
		else
		{
			break;
		}
	}

	$PicasaPhotos = array();
	$i = 0;
	if (is_object($feedContent) && is_array($feedContent->items))
	{
		foreach ($feedContent->items as $item)
		{ 
			//array_walk($item['gphoto'],"displaydata"); echo '<br>';
			$row = array();		
			$row['id'] = $i;			
			$row['gid'] = $item['gphoto']['id'];
			$row['title'] = $item['title'];
			$row['height'] = $item['gphoto']['height'];
			$row['width'] = $item['gphoto']['width'];
			$row['size'] = round($item['gphoto']['size']/102.40)/10.0;
			//$row['timestamp'] = $item['exif']['tags_time'];
			$row['timestamp'] = $item['date_timestamp'];
			$row['taken'] = round($item['gphoto']['timestamp']/1000.0);
			if (empty($item['description']))
			{
				$row['description'] = '';
			}
			else
			{
				$row['description'] = $item['description'];
			}
			$row['thumbnail72'] = $item['media']['group_thumbnail@url'];
			$row['thumbnail144'] = $item['media']['group_thumbnail#2@url'];
			$row['thumbnail288'] = $item['media']['group_thumbnail#3@url'];
			$row['url'] = $item['enclosure@url'];		
			$PicasaPhotos[$i] = $row;
			$i++;
		}
	}
	return $PicasaPhotos;
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
function PicasaGalleryBrowser()
{
	// SMF Globals
	global $modSettings,$context,$mbname, $txt, $scripturl,$settings;
	
	// Picasa Browser Globals
	global $PicasaAlbum,$UsingOldSearchFriendlyURLs,$PicasaCols,$PicasaUserURL;
	global $PicasaPhotos,$PicasaAlbums,$PicasaErrorMessage,$PicasaServerURL;
    global $PicasaPage,$MaxImages,$max_page;
	
	// Set the page title
	$context['page_title'] = $mbname . ' ' . $txt['picasa_table_title'];

	// Link Tree
	if (empty($modSettings['gallery_picasa_fixed_album']) && !empty($modSettings['gallery_picasa_default_album']))
	{
		$context['linktree'][] = array(
						'url' => $scripturl . '?action=picasagallerybrowser&ListAlbums=1',
						'name' => $txt['picasa_table_title']
					);
	}
	else
	{
		$context['linktree'][] = array(
						'url' => $scripturl . '?action=picasagallerybrowser',
						'name' => $txt['picasa_table_title']
					);
	}
	
	$PicasaServerURL='';
	// check settings
	if (empty($modSettings['gallery_picasa_server_url']))
	{	
	    $PicasaErrorMessage = '<br /><b>'.$txt['no_picasa_server'].'<br /><br />';
	}
	else
	{
		$PicasaServerURL = $modSettings['gallery_picasa_server_url']; // Link to your Picasa Server URL
	}
	
	if (empty($modSettings['gallery_picasa_account']))
	{	
		$PicasaErrorMessage .= '<br /><b>'.$txt['no_picasa_account'].'</b><br />';
	}
	else
	{
		$PicasaID = $modSettings['gallery_picasa_account'];
	}
	
	if (empty($modSettings['gallery_picasa_columns']) || $modSettings['gallery_picasa_columns']<1)
	{
		$PicasaCols = 4;
	}
	else
	{
		$PicasaCols = $modSettings['gallery_picasa_columns'];
	}
    $PicasaRows=4; // default four rows
    if (!empty($modSettings['gallery_picasa_rows']) && $modSettings['gallery_picasa_rows']>0)
	{
		$PicasaRows = $modSettings['gallery_picasa_rows'];
	}
    $MaxImages=$PicasaCols*$PicasaRows; 
    $max_page=1;
    $PicasaPage=0;
	
	if (!empty($PicasaErrorMessage))
	{
		// Error message 
		// Load the main picasa gallery template and finish
        $MaxImages=0;
		loadtemplate('picasagallerybrowser');
		return;
	}
	
	// see what should be displayed
	if (!empty($modSettings['gallery_picasa_default_album']))
	{
		$PicasaAlbum = $modSettings['gallery_picasa_default_album'];
	}
	if (empty($modSettings['gallery_picasa_fixed_album']))
	{
		if (!empty($_GET['ListAlbums']))
		{
			if ($_GET['ListAlbums']=='1')
			{
				$PicasaAlbum='';
			}
		}
		
		if (!empty($_GET['album']))
		{
			$PicasaAlbum = $_GET['album'];
		}
	}
	else
	{
		if (empty($PicasaAlbum))
		{
            $MaxImages=0;
			$PicasaErrorMessage = '<br /><b>'.$txt['no_picasa_default_album'].'</b><br />';
			loadtemplate('picasagallerybrowser');
			return;
		}
	}
	
    if (!empty($_GET['pageid'])) {
        $PicasaPage = $_GET['pageid'];
    }
	// find out what format the URL is in.
	$PicasaUserURL = $_SERVER["REQUEST_URI"];
	$UsingOldSearchFriendlyURLs=0;
	if (strpos($PicasaUserURL, '/action,')!=false && strpos($PicasaUserURL, '.html')==false && strpos($PicasaUserURL, 'index.php')!=false)
	{
		$UsingOldSearchFriendlyURLs=1; //  using old search engine friendly URLs
	}
	
	if (!empty($PicasaAlbum))
	{
		if (empty($modSettings['gallery_picasa_fixed_album']))
		{
			// Add link tree if they can browse albums
			$context['linktree'][] = array(
						'url' => $PicasaUserURL,
						'name' => $PicasaAlbum
					);
		}
		
        $PicasaUserURL=RemoveFromUrl($PicasaUserURL,'album');
        $PicasaUserURL=RemoveFromUrl($PicasaUserURL,'pageid');

		//	3) These CSS-styles are necessary for the script to work. You may also put
		//	them in an external CSS-file. See the webpage for documentation.
	
		$context['html_headers'] =  $context['html_headers'] . '
		<link rel="stylesheet" type="text/css" href="'.$settings['default_theme_url'].'/picasahighslide/picasahs.css" />';
		$PicasaPhotos = getPicasaPhotos($PicasaServerURL, $PicasaID, $PicasaAlbum);
        if (!empty($PicasaPhotos))
        {
          $ImageCount = count($PicasaPhotos);
          if ($ImageCount>$MaxImages) {
              if ($PicasaPage<1) $PicasaPage=1;
              $max_page = ceil($ImageCount/$MaxImages);
              if ($PicasaPage>$max_page) {
                $PicasaPage=$max_page;
              }
              $StartIndex=($PicasaPage-1)*$MaxImages;
              $PicasaPhotos=array_splice($PicasaPhotos, $StartIndex);
              $EndIndex=$MaxImages;
              $PicasaPhotos=array_splice($PicasaPhotos, 0,$EndIndex);
          }
          $MaxImages=$ImageCount;
        }
        else {
            $MaxImages=0;
        }
	}
	else
	{
        $PicasaUserURL=RemoveFromUrl($PicasaUserURL,'pageid');
		$PicasaAlbums=getAlbums($PicasaServerURL, $PicasaID, $access = 'public');
        if (!empty($PicasaAlbums))
        {
          $ImageCount = count($PicasaAlbums);
          if ($ImageCount>$MaxImages) {
              if ($PicasaPage<1) $PicasaPage=1;
              $max_page = ceil($ImageCount/$MaxImages);
              if ($PicasaPage>$max_page) {
                $PicasaPage=$max_page;
              }
              $StartIndex=($PicasaPage-1)*$MaxImages;
              $PicasaAlbums=array_splice($PicasaAlbums, $StartIndex);
              $EndIndex=$MaxImages;
              $PicasaAlbums=array_splice($PicasaAlbums, 0,$EndIndex);
          }
          $MaxImages=$ImageCount;
        }
        else {
            $MaxImages=0;
        }
	}
	
    $PicasaUserURL=RemoveFromUrl($PicasaUserURL,'ListAlbums'); // remove ListAlbums tag
	
	
	// Load the main picasa gallery template
	loadtemplate('picasagallerybrowser');
}
?>