<?php
/*
SMF Picasa Gallery Browser Mod
Version 0.6
November 2008
P.J.Lawrence
*/

function template_main()
{
	// SMF Globals
	global $modSettings,$txt,$settings;
	
	// Picasa Browser Globals
	global $PicasaAlbum,$UsingOldSearchFriendlyURLs,$PicasaCols,$PicasaUserURL;
	global $PicasaPhotos,$PicasaAlbums,$PicasaErrorMessage,$PicasaServerURL;
    global $PicasaPage,$MaxImages,$max_page;
	
	$HighSlideWebPath=$settings['default_theme_url'].'/picasahighslide';       // Web path to Highslide java code
	
	echo '
	<div class="tborder">
		<div class="catbg" style="padding: 5px 5px 5px 10px;">',$txt['picasa_table_title'],'</div>';
	
	if ($PicasaErrorMessage)
	{
		echo $PicasaErrorMessage;
	}
	elseif (!empty($PicasaAlbum))
	{
		$PicasaID = $modSettings['gallery_picasa_account'];
		//	1 ) Reference to the file containing the javascript.
		//	This file must be located on your server.

		echo '<script type="text/javascript" src="',$HighSlideWebPath,'/highslide-with-gallery.js"></script>';

		//	2) Optionally override the settings defined at the top
		//	of the highslide.js file. The parameter hs.graphicsDir is important!

		echo "<script type=\"text/javascript\">
			hs.graphicsDir = '",$HighSlideWebPath,"/graphics/';
			hs.align = 'center';
			hs.transitions = ['expand', 'crossfade'];
			hs.outlineType = 'rounded-white';
			hs.fadeInOut = true;
			hs.numberPosition = 'caption';
			hs.dimmingOpacity = 0.1;

			// Add the controlbar
			if (hs.addSlideshow) hs.addSlideshow({
				//slideshowGroup: 'group1',
				interval: 5000,
				repeat: false,
				useControls: true,
				fixedControls: 'fit',
				overlayOptions: {
					opacity: .75,
					position: 'bottom center',
					hideOnMouseOut: true
				}
			});
		</script>";

		//	3) These CSS-styles are necessary for the script to work. You may also put
		//	them in an external CSS-file. See the webpage for documentation.

		// set via $context['html_headers'] see picasagallerybrowser.php	
		
		echo '
		<div class="titlebg" style="padding: 5px 5px 5px 10px;">'; // Header
		if (empty($modSettings['gallery_picasa_fixed_album']))
		{
			echo $txt['picasa_account'];
			
			echo '<a href="';
			if (empty($modSettings['gallery_picasa_default_album']))
			{
				echo $PicasaUserURL;
			}
			else
			{
               echo $PicasaUserURL,FormatParmForUrl($PicasaUserURL,'ListAlbums',$UsingOldSearchFriendlyURLs),"1";
			}
			echo '">',$PicasaID,'</a> - ';
		}
		echo $txt['picasa_album'],$PicasaAlbum;
		echo '
		</div>'; // end Header
		echo '
		<div class="windowbg" style="padding:4px;"> 
			<div style="line-height: 1.3em;">';						  // gallery and line style
		echo '	
				<div id="highslide-container"></div>';
			
		if (count($PicasaPhotos) == 0)
		{
			echo '
				<br /><b>',$txt['no_picasa_photos'],'</b><br />';
		}
		else
		{
			echo '
				<table width="100%" cellspacing="1" cellpadding="1" border="0" class="bordercolor" style="clear: both; table-layout: fixed;">';
			echo '
					<tr>';
			$colcount=0;
			foreach($PicasaPhotos as $photo)
			{
				$colcount++;
				if ($colcount>$PicasaCols)
				{
					$colcount=1;
					echo '
					</tr>
					<tr>';
				}
				echo '
						<td class="windowbg2" valign="middle" align="center">
							<a href="',$photo["url"],'?imgmax=640" id="image',$photo["id"],'"  class="highslide" onclick="return hs.expand(this)" >
								<img  src="',$photo["thumbnail144"],'" alt="',$txt['picasa_thumbnail'],'" title="',$txt['picasa_thumbnail'],'"  />
							</a><br />
							<div class="smalltext">
						',$photo["description"],'
							</div>
							<div class="highslide-caption" id="imgtag',$photo["id"],'">
								<div align="center">
								',$photo["description"],'<br />
									<b>	<a href="',$PicasaServerURL,'/',$PicasaID,'/',$PicasaAlbum,'">| ',$txt['gallery_picasa_view_album'],' |</a>
									<a href="',$PicasaServerURL,'/',$PicasaID,'/',$PicasaAlbum,'#',$photo["gid"],'">| ',$txt['gallery_picasa_view_photo'],' |</a>
									</b> 
									<br />
									',$txt['gallery_picasa_filename'],'<i>',$photo["title"],'</i> ',$txt['gallery_picasa_size'],' <i>', $photo['size'] ,'Kb</i> ',$txt['gallery_picasa_dimension'],' <i>',$photo['width'],'x',$photo['height'],'</i>
									<br />',$txt['gallery_picasa_uploaded'],' <i>',strftime("%b %d %Y %X",$photo['timestamp']),'</i> ',$txt['gallery_picasa_taken'],' <i>',strftime("%b %d %Y %X",$photo['taken']),'</i>
								</div>
							</div>
						</td>';
			}

			// add empty cells
			for ($colcount=$colcount;$colcount<$PicasaCols;$colcount++)
			{
				echo '
						<td class="windowbg2">
						</td>';
			}
			echo '
					</tr>
				</table>';
		}
		echo '
			</div>
		</div>';  // end of gallery and line style
        echo   '<div class="titlebg" style="padding: 5px 5px 5px 10px;">'; // footer
        echo   "<table width='100%'><tr><td><i>",$MaxImages," ",$txt['gallery_picasa_images']," ",$max_page," ",$txt['gallery_picasa_pages'],"</i></td>";
        echo   "<td align='right'>";
        if ($max_page>1) {
            $pagearound=4;
            
            $pagestart = $PicasaPage-$pagearound;
            if ($pagestart<0) 
            {
                $pagearound-=$pagestart;
                $pagestart=0;
            }
            $pageend = $PicasaPage + $pagearound -1;
            if ($pageend>$max_page) 
            {
                $pagestart -= $pageend-$max_page;
                if ($pagestart<0) $pagestart=0;
                $pageend=$max_page;
            }
            if ($PicasaPage>1 ) {
                echo "<a href='";
                echo $PicasaUserURL;
                echo FormatParmForUrl($PicasaUserURL,'album',$UsingOldSearchFriendlyURLs);
                echo $PicasaAlbum;
                echo FormatParmForUrl($PicasaUserURL,'pageid',$UsingOldSearchFriendlyURLs,true);
                echo $PicasaPage-1;
                echo "'>";
                echo '&lt;-</a>';
            }
            if ($pagestart>0) {
                echo "<a href='";
                echo $PicasaUserURL;
                echo FormatParmForUrl($PicasaUserURL,'album',$UsingOldSearchFriendlyURLs);
                echo $PicasaAlbum;
                echo FormatParmForUrl($PicasaUserURL,'pageid',$UsingOldSearchFriendlyURLs,true);
                echo "1";
                echo "'>";
                echo '1</a>..';
            }
            for($i=$pagestart;$i<$pageend;$i++)
            {
                echo "|";
                if(($PicasaPage-1) == $i)
                {
                    echo "[",($i+1),"]";
                }
                else
                {
                    echo "<a href='";
                    echo $PicasaUserURL;
                    echo FormatParmForUrl($PicasaUserURL,'album',$UsingOldSearchFriendlyURLs);
                    echo $PicasaAlbum;
                    echo FormatParmForUrl($PicasaUserURL,'pageid',$UsingOldSearchFriendlyURLs,true);
                    echo ($i+1);
                    echo "'>";
                    echo ($i+1),'</a>';
                };
            };
            echo "|";
            if ($pageend<$max_page) {
                echo "..<a href='";
                echo $PicasaUserURL;
                echo FormatParmForUrl($PicasaUserURL,'album',$UsingOldSearchFriendlyURLs);
                echo $PicasaAlbum;
                echo FormatParmForUrl($PicasaUserURL,'pageid',$UsingOldSearchFriendlyURLs,true);
                echo $max_page;
                echo "'>";
                echo $max_page,'</a>';
            }
            if ($PicasaPage<$max_page) {
                echo "<a href='";
                echo $PicasaUserURL;
                echo FormatParmForUrl($PicasaUserURL,'album',$UsingOldSearchFriendlyURLs);
                echo $PicasaAlbum;
                echo FormatParmForUrl($PicasaUserURL,'pageid',$UsingOldSearchFriendlyURLs,true);
                echo $PicasaPage+1;
                echo "'>";
                echo '-&gt;</a>';
            }
        }
        echo "</td></tr></table></div>";
	} 
	else
	{
		$PicasaID = $modSettings['gallery_picasa_account'];
		echo '<div class="titlebg" style="padding: 5px 5px 5px 10px;">'; // Header
		echo $txt['picasa_account'],$PicasaID;
		echo '</div>'; // End Header
		echo '
		<div class="windowbg" style="padding:4px; ">
			<div style="line-height: 1.3em;">'; // Album list and line style

		if (count($PicasaAlbums) == 0) {
			echo '
				<br /><b>',$txt['no_picasa_albums'],'</b><br />';
		}
		else
		{ 
			echo '
				<table width="100%" cellspacing="1" cellpadding="1" border="0" class="bordercolor" style="clear: both; table-layout: fixed;">
			';
			echo '
					<tr>';
			$colcount=0;
			foreach($PicasaAlbums as $album) {
				$timestamp	= $album['timestamp'];
				$title 		= $album['title'];
				$thumbnail 	= $album['thumbnail'];
				$slug		= $album['slug'];
					
				$colcount++;
				if ($colcount>$PicasaCols)
				{
					$colcount=1;
					echo '
					</tr>
					<tr>';
				}
				echo '
						<td class="windowbg2" valign="middle" align="center">
							<a href="';
				echo $PicasaUserURL ;
                echo FormatParmForUrl($PicasaUserURL,'album',$UsingOldSearchFriendlyURLs);
				echo $slug;
				echo '">';
				echo '
							<img  src="',$thumbnail,'" alt="',$txt['picasa_album_thumbnail'],'" title="',$txt['picasa_album_thumbnail'],'"  />
							</a>
							<br />',$title,'<br />
						</td>';
			} 
			// add empty cells
			for ($colcount=$colcount;$colcount<$PicasaCols;$colcount++)
			{
				echo '
						<td class="windowbg2" >
						</td>';
			}
			echo '
					</tr>
				</table>';
		}
		echo '
			</div>
		</div>'; // end of Album list and line style
        echo   '<div class="titlebg" style="padding: 5px 5px 5px 10px;">'; // footer
        echo   "<table width='100%'><tr><td><i>",$MaxImages," ";
        echo $txt['gallery_picasa_albums'];
        echo " ",$max_page," ",$txt['gallery_picasa_pages'],"</i></td>";
        echo   "<td align='right'>";
        if ($max_page>1) {
            $pagearound=4;
            
            $pagestart = $PicasaPage-$pagearound;
            if ($pagestart<0) 
            {
                $pagearound-=$pagestart;
                $pagestart=0;
            }
            $pageend = $PicasaPage + $pagearound -1;
            if ($pageend>$max_page) 
            {
                $pagestart -= $pageend-$max_page;
                if ($pagestart<0) $pagestart=0;
                $pageend=$max_page;
            }
            if ($PicasaPage>1 ) {
                echo "<a href='";
                echo $PicasaUserURL;
                echo FormatParmForUrl($PicasaUserURL,'pageid',$UsingOldSearchFriendlyURLs);
                echo $PicasaPage-1;
                echo "'>";
                echo '&lt;-</a>';
            }
            if ($pagestart>0) {
                echo "<a href='";
                echo $PicasaUserURL;
                echo FormatParmForUrl($PicasaUserURL,'pageid',$UsingOldSearchFriendlyURLs);
                echo "1";
                echo "'>";
                echo '1</a>..';
            }
            for($i=$pagestart;$i<$pageend;$i++)
            {
                echo "|";
                if(($PicasaPage-1) == $i)
                {
                    echo "[",($i+1),"]";
                }
                else
                {
                    echo "<a href='";
                    echo $PicasaUserURL;
                    echo FormatParmForUrl($PicasaUserURL,'pageid',$UsingOldSearchFriendlyURLs);
                    echo ($i+1);
                    echo "'>";
                    echo ($i+1),'</a>';
                };
            };
            echo "|";
            if ($pageend<$max_page) {
                echo "..<a href='";
                echo $PicasaUserURL;
                echo FormatParmForUrl($PicasaUserURL,'pageid',$UsingOldSearchFriendlyURLs);
                echo $max_page;
                echo "'>";
                echo $max_page,'</a>';
            }
            if ($PicasaPage<$max_page) {
                echo "<a href='";
                echo $PicasaUserURL;
                echo FormatParmForUrl($PicasaUserURL,'pageid',$UsingOldSearchFriendlyURLs);
                echo $PicasaPage+1;
                echo "'>";
                echo '-&gt;</a>';
            }
        }
        echo "</td></tr></table></div>";
	}
	echo '
	</div>';
}
?>