<?php
/*
SMF Flickr Gallery Browser Mod
Version 0.6
November 2010
P.J.Lawrence
*/

function template_main()
{
	// SMF Globals
	global $modSettings,$txt,$settings;
	
	// Flickr Browser Globals
	global $FlickrSetID,$FlickrSetTitle,$UsingOldSearchFriendlyURLs,$FlickrCols,$FlickrUserURL;
	global $FlickrPhotos,$FlickrSets,$FlickrErrorMessage,$FlickrPhotoURL;
	global $FlickrWarningMessage;
    global $FlickrPage,$MaxImages,$max_page,$DisplayGroups;
	
	$HighSlideWebPath=$settings['default_theme_url'].'/flickrhighslide';       // Web path to Highslide java code
	
	echo '
	<div class="tborder">
		<div class="catbg" style="padding: 5px 5px 5px 10px;">',$txt['flickr_table_title'],'</div>';
	
	if (!empty($FlickrErrorMessage))
	{
		echo $FlickrErrorMessage;
	}
	elseif (!empty($FlickrSetID))
	{
		$FlickrID = $modSettings['gallery_flickr_account'];
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

		// set via $context['html_headers'] see flickrgallerybrowser.php	
		
		echo '
		<div class="titlebg" style="padding: 5px 5px 5px 10px;">'; // Header
		if (empty($modSettings['gallery_flickr_fixed_setgroup']))
		{
			echo $txt['flickr_account'];
			
			echo '<a href="';
			if (empty($modSettings['gallery_flickr_default_setgroup']))
			{
				echo $FlickrUserURL;
			}
			else
			{
			    echo $FlickrUserURL,FormatParmForUrl($FlickrUserURL,'ListSets',$UsingOldSearchFriendlyURLs),"1";
			}
			echo '">',$FlickrID,'</a> - ';
		}
        if ($DisplayGroups)
           echo $txt['flickr_group'];
        else
            echo $txt['flickr_set'];
		if (empty($FlickrSetTitle))
		{
			echo $FlickrSetID;
		}
		else
		{
			echo $FlickrSetTitle;
		}
		echo '
		</div>'; // end Header
		echo '
		<div class="windowbg" style="padding:4px;"> 
			<div style="line-height: 1.3em;">';						  // gallery and line style
		echo '	
				<div id="highslide-container"></div>';
			
		if (count($FlickrPhotos) == 0)
		{
            $MaxImages=0;
			echo '
				<br /><b>',$txt['no_flickr_photos'],'</b><br />';
		}
		else
		{
			echo '
				<table width="100%" cellspacing="1" cellpadding="1" border="0" class="bordercolor" style="clear: both; table-layout: fixed;">';
			echo '
					<tr>';
			$colcount=0;
			foreach($FlickrPhotos as $photo)
			{
				$colcount++;
				if ($colcount>$FlickrCols)
				{
					$colcount=1;
					echo '
					</tr>
					<tr>';
				}
				echo '
						<td class="windowbg2" valign="middle" align="center">
							<a href="',$photo["url"],'?imgmax=640" id="image',$photo["id"],'"  class="highslide" onclick="return hs.expand(this)" >
								<img  src="',$photo["thumbnail144"],'" alt="',$txt['flickr_thumbnail'],'" title="',$txt['flickr_thumbnail'],'"  />
							</a><br />
							<div class="smalltext">
						',$photo["description"],'
							</div>
							<div class="highslide-caption" id="imgtag',$photo["id"],'">
								<div align="center">
								',$photo["description"],'<br />
									<b>	
									<a href="',$FlickrPhotoURL,'sets/',$FlickrSetID,'">| ',$txt['gallery_flickr_view_set'],' |</a>
									<a href="',$FlickrPhotoURL,$photo["gid"],'">| ',$txt['gallery_flickr_view_photo'],' |</a>
									</b> 
									<br />
									',$txt['gallery_flickr_filename'],' <i>',$photo["title"],'</i> ',$txt['gallery_flickr_dimension'],' <i>',$photo['width'],'x',$photo['height'],'</i>
									<br />',$txt['gallery_flickr_uploaded'],' <i>',strftime("%b %d %Y %X",$photo['timestamp']),'</i> ',$txt['gallery_flickr_taken'],' <i>',$photo['taken'],'</i>
								</div>
							</div>
						</td>';
			}

			// add empty cells
			for ($colcount=$colcount;$colcount<$FlickrCols;$colcount++)
			{
				echo '
						<td class="windowbg2">
						</td>';
			}
			echo '
					</tr>';           
            echo '
				</table>';
		}
		echo '
			</div>
		</div>';  // end of gallery and line style
        echo   '<div class="titlebg" style="padding: 5px 5px 5px 10px;">'; // footer
        echo   "<table width='100%'><tr><td><i>",$MaxImages," ",$txt['gallery_flickr_images']," ",$max_page," ",$txt['gallery_flickr_pages'],"</i></td>";
        echo   "<td align='right'>";
        if ($max_page>1) {
            $pagearound=4;
            
            $pagestart = $FlickrPage-$pagearound;
            if ($pagestart<0) 
            {
                $pagearound-=$pagestart;
                $pagestart=0;
            }
            $pageend = $FlickrPage + $pagearound -1;
            if ($pageend>$max_page) 
            {
                $pagestart -= $pageend-$max_page;
                if ($pagestart<0) $pagestart=0;
                $pageend=$max_page;
            }
            if ($FlickrPage>1 ) {
                echo "<a href='";
                echo $FlickrUserURL;
                if ($DisplayGroups) 
                    echo FormatParmForUrl($FlickrUserURL,'groupid',$UsingOldSearchFriendlyURLs);
                else
                    echo FormatParmForUrl($FlickrUserURL,'setid',$UsingOldSearchFriendlyURLs);
                echo $FlickrSetID;
                echo FormatParmForUrl($FlickrUserURL,'pageid',$UsingOldSearchFriendlyURLs,true);
                echo $FlickrPage-1;
                echo "'>";
                echo '&lt;-</a>';
            }
            if ($pagestart>0) {
                echo "<a href='";
                echo $FlickrUserURL;
                if ($DisplayGroups) 
                    echo FormatParmForUrl($FlickrUserURL,'groupid',$UsingOldSearchFriendlyURLs);
                else
                    echo FormatParmForUrl($FlickrUserURL,'setid',$UsingOldSearchFriendlyURLs);
                echo $FlickrSetID;
                echo FormatParmForUrl($FlickrUserURL,'pageid',$UsingOldSearchFriendlyURLs,true);
                echo "1";
                echo "'>";
                echo '1</a>..';
            }
            for($i=$pagestart;$i<$pageend;$i++)
            {
                echo "|";
                if(($FlickrPage-1) == $i)
                {
                    echo "[",($i+1),"]";
                }
                else
                {
                    echo "<a href='";
                    echo $FlickrUserURL;
                    if ($DisplayGroups) 
                        echo FormatParmForUrl($FlickrUserURL,'groupid',$UsingOldSearchFriendlyURLs);
                    else
                        echo FormatParmForUrl($FlickrUserURL,'setid',$UsingOldSearchFriendlyURLs);
                    echo $FlickrSetID;
                    echo FormatParmForUrl($FlickrUserURL,'pageid',$UsingOldSearchFriendlyURLs,true);
                    echo ($i+1);
                    echo "'>";
                    echo ($i+1),'</a>';
                };
            };
            echo "|";
            if ($pageend<$max_page) {
                echo "..<a href='";
                echo $FlickrUserURL;
                if ($DisplayGroups) 
                    echo FormatParmForUrl($FlickrUserURL,'groupid',$UsingOldSearchFriendlyURLs);
                else
                    echo FormatParmForUrl($FlickrUserURL,'setid',$UsingOldSearchFriendlyURLs);
                echo $FlickrSetID;
                echo FormatParmForUrl($FlickrUserURL,'pageid',$UsingOldSearchFriendlyURLs,true);
                echo $max_page;
                echo "'>";
                echo $max_page,'</a>';
            }
            if ($FlickrPage<$max_page) {
                echo "<a href='";
                echo $FlickrUserURL;
                if ($DisplayGroups) 
                    echo FormatParmForUrl($FlickrUserURL,'groupid',$UsingOldSearchFriendlyURLs);
                else
                    echo FormatParmForUrl($FlickrUserURL,'setid',$UsingOldSearchFriendlyURLs);
                echo $FlickrSetID;
                echo FormatParmForUrl($FlickrUserURL,'pageid',$UsingOldSearchFriendlyURLs,true);
                echo $FlickrPage+1;
                echo "'>";
                echo '-&gt;</a>';
            }
        }
        echo "</td></tr></table></div>";
	} 
	else
	{
		$FlickrID = $modSettings['gallery_flickr_account'];
		echo '<div class="titlebg" style="padding: 5px 5px 5px 10px;">'; // Header
		echo $txt['flickr_account'],$FlickrID;
		echo '</div>'; // End Header
		echo '
		<div class="windowbg" style="padding:4px; ">
			<div style="line-height: 1.3em;">'; // Set list and line style

		if (count($FlickrSets) == 0) {
            $MaxImages=0;
            if ($DisplayGroups) {
                echo '
                    <br /><b>',$txt['no_flickr_groups'],'</b><br />';
            }
            else {
                echo '
                    <br /><b>',$txt['no_flickr_sets'],'</b><br />';
            }
		}
		else
		{ 
			echo '
				<table width="100%" cellspacing="1" cellpadding="1" border="0" class="bordercolor" style="clear: both; table-layout: fixed;">
			';
			echo '
					<tr>';
			$colcount=0;
			foreach($FlickrSets as $set) {
				$title 		= $set['title'];
				$thumbnail 	= $set['thumbnail'];
				$slug		= $set['slug'];
					
				$colcount++;
				if ($colcount>$FlickrCols)
				{
					$colcount=1;
					echo '
					</tr>
					<tr>';
				}
				echo '
						<td class="windowbg2" valign="middle" align="center">
							<a href="';
				echo $FlickrUserURL;
                if ($DisplayGroups) 
                    echo FormatParmForUrl($FlickrUserURL,'groupid',$UsingOldSearchFriendlyURLs);
                else
                    echo FormatParmForUrl($FlickrUserURL,'setid',$UsingOldSearchFriendlyURLs);
				echo $slug;
				echo '">';
				echo '
							<img  src="',$thumbnail,'" alt="',$txt['flickr_set_thumbnail'],'" title="',$txt['flickr_set_thumbnail'],'"  />
							</a>
							<br />',$title,'<br />
						</td>';
			} 
			// add empty cells
			for ($colcount=$colcount;$colcount<$FlickrCols;$colcount++)
			{
				echo '
						<td class="windowbg2" >
						</td>';
			}
            
            // end table
			echo '
					</tr>';
            
            // end tabel
            echo '        
				</table>';
		}
		echo '
			</div>
		</div>'; // end of Set list and line style
        echo   '<div class="titlebg" style="padding: 5px 5px 5px 10px;">'; // footer
        echo   "<table width='100%'><tr><td><i>",$MaxImages," ";
        if ($DisplayGroups) {
            echo $txt['gallery_flickr_groupson'];
        }
        else {
            echo $txt['gallery_flickr_setson'];
        }
        echo " ",$max_page," ",$txt['gallery_flickr_pages'],"</i></td>";
        echo   "<td align='right'>";
        if ($max_page>1) {
            $pagearound=4;
            
            $pagestart = $FlickrPage-$pagearound;
            if ($pagestart<0) 
            {
                $pagearound-=$pagestart;
                $pagestart=0;
            }
            $pageend = $FlickrPage + $pagearound -1;
            if ($pageend>$max_page) 
            {
                $pagestart -= $pageend-$max_page;
                if ($pagestart<0) $pagestart=0;
                $pageend=$max_page;
            }
            if ($FlickrPage>1 ) {
                echo "<a href='";
                echo $FlickrUserURL;
                echo $FlickrSetID;
                echo FormatParmForUrl($FlickrUserURL,'pageid',$UsingOldSearchFriendlyURLs);
                echo $FlickrPage-1;
                echo "'>";
                echo '&lt;-</a>';
            }
            if ($pagestart>0) {
                echo "<a href='";
                echo $FlickrUserURL;
                echo FormatParmForUrl($FlickrUserURL,'pageid',$UsingOldSearchFriendlyURLs);
                echo "1";
                echo "'>";
                echo '1</a>..';
            }
            for($i=$pagestart;$i<$pageend;$i++)
            {
                echo "|";
                if(($FlickrPage-1) == $i)
                {
                    echo "[",($i+1),"]";
                }
                else
                {
                    echo "<a href='";
                    echo $FlickrUserURL;
                    echo FormatParmForUrl($FlickrUserURL,'pageid',$UsingOldSearchFriendlyURLs);
                    echo ($i+1);
                    echo "'>";
                    echo ($i+1),'</a>';
                };
            };
            echo "|";
            if ($pageend<$max_page) {
                echo "..<a href='";
                echo $FlickrUserURL;
                echo FormatParmForUrl($FlickrUserURL,'pageid',$UsingOldSearchFriendlyURLs);
                echo $max_page;
                echo "'>";
                echo $max_page,'</a>';
            }
            if ($FlickrPage<$max_page) {
                echo "<a href='";
                echo $FlickrUserURL;
                echo FormatParmForUrl($FlickrUserURL,'pageid',$UsingOldSearchFriendlyURLs);
                echo $FlickrPage+1;
                echo "'>";
                echo '-&gt;</a>';
            }
        }
        echo "</td></tr></table></div>";
	}
	if (!empty($FlickrWarningMessage))
	{
		echo $FlickrWarningMessage;
	}
	echo '
	</div>';
}
?>