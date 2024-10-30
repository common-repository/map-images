<?php  
/* 
Plugin Name: Map Images
Plugin URI: http://www.danmorella.com/wordpress/?p=749
Description: Maps gps tagged images on a google map.
Version: 1.4.2 
Stable tag: 1.4.2
Author: Dan Morella
Author URI: http://www.danmorella.com
*/  
/*  Copyright 2014  Dan Morella  (email : wpplugins@danmorella.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//If being accessed by the administrator, include the admin.php file.
if ( is_admin() )
{
	require_once dirname( __FILE__ ) . '/admin.php';
}

//Get the map images options from the WordPress database.
$mapimages_options = get_option('mapimages_options');

//mapimages_gpsDecimal function
//Convert GPS degrees to decimals
function mapimages_gpsDecimal($deg, $min, $sec, $hem) 
{
	if(strpos($deg,"/") !== false)
	{
       	$deg = explode("/",$deg);
		$deg = $deg[0]/$deg[1];
	}
	if(strpos($min,"/") !== false)
	{
       	$min = explode("/",$min);
		$min = $min[0]/$min[1];
	}
	if(strpos($sec,"/") !== false)
	{
       	$sec = explode("/",$sec);
		$sec = $sec[0]/$sec[1];
	}

	$d = $deg + ((($min/60) + ($sec/3600)));
	return ($hem=='S' || $hem=='W') ? $d*=-1 : $d;
}

//mapimages_createGoogleMaps function
//This function returns the code for a new google map object.
function mapimages_createGoogleMaps($id)
{
	//Include the map images options
	global $mapimages_options;

	//new content for the google maps
	$additionalContent = "";

	//create the div where the google map will reside
	$additionalContent .= "\n <div id=\"map_canvas".$id."\" style=\"width:".$mapimages_options["plugin_mapwidth"]."px; height:".$mapimages_options["plugin_mapheight"]."px;\"></div>";
	$additionalContent .= "\n ";

	//create the java script 
	$additionalContent .= "\n <script>";

	//add a function to the mapimages_inpostJavaScriptArray that will create a new google map using the Google Maps API
	$additionalContent .= "\n   mapimages_inpostJavaScriptArray.push(function(){var map".$id." = new google.maps.Map(document.getElementById(\"map_canvas".$id."\"), mapOptions".$id."); return map".$id.";});";

	//push the name of the new google map variable to the mapimages_inpostMapNameArray
	$additionalContent .= "\n   mapimages_inpostMapNameArray.push('map".$id."');";

	//create the javascript object with the map options for this google map
	$additionalContent .= "\n   var mapOptions".$id." = {";
	$additionalContent .= "\n     center: new google.maps.LatLng(".$mapimages_options["googlemaps_defaultcenter"]."),";
	$additionalContent .= "\n     zoom: ".$mapimages_options["googlemaps_defaultzoom"].",";
	$additionalContent .= "\n     mapTypeId: google.maps.MapTypeId.".$mapimages_options["googlemaps_maptype"]."";
	$additionalContent .= "\n   };";

	//create a new array for all the markers that will be added to the google map.
	$additionalContent .= "\n  currentMarkerArray = new Array();";
	$additionalContent .= "\n  currentInfoWindowArray = new Array();";

	return $additionalContent;
}

//mapimages_generatemarkers function
//This function extracts all the images from a piece of content and generates the markers
function mapimages_generatemarkers($content,$tempURL)
{
	//Include the global page map count
	global $pageMapCount;
	//Include the map images options
	global $mapimages_options;

	$imageList = array();

	//new content for the google maps
	$additionalContent = "";

	//split the current post by all image tags and loop through them
	$tempContent = explode("<img",$content);

	if(count($tempContent) > 1)
	{
		for($j=0;$j<(count($tempContent)-1);$j++)
		{
			//locate the url of the image
			$tempLocation = strripos($tempContent[$j],"href=\"");

			//if the url exists, then parse the image
			if($tempLocation !== false)
			{
				//store the URL in a variable
				$tempContent[$j] = substr($tempContent[$j],$tempLocation+6);
				$tempLocation = strpos($tempContent[$j],"\"");
				$tempContent[$j] = substr($tempContent[$j],0,$tempLocation);

				//get the exif data from the image.  If the image is local, get the full URL so all images can be processed the same.
				if(array_key_exists("HTTPS",$_SERVER) && trim($_SERVER['HTTPS']) != "")
				  $imageHeader = "https://";
				else
				  $imageHeader = "http://";
				$tempImageURL = str_replace($imageHeader.$_SERVER['SERVER_NAME'],$_SERVER["DOCUMENT_ROOT"],$tempContent[$j]);

				$exif_data = exif_read_data($tempImageURL);

				//if the exif GPS data exists, add a marker to the map.
				if($exif_data != false && (array_key_exists('GPSLongitude',$exif_data) || array_key_exists('GPSLatitude',$exif_data) || array_key_exists('GPSLongitudeRef',$exif_data) || array_key_exists('GPSLatitudeRef',$exif_data)))
				{
					//extract the longitude and latitude from the exif data
					$egeoLong = $exif_data['GPSLongitude'];
					$egeoLat = $exif_data['GPSLatitude'];
					$egeoLongR = $exif_data['GPSLongitudeRef'];
					$egeoLatR = $exif_data['GPSLatitudeRef'];

					//convert the degree data to decimal for google maps api
					$geoLong = mapimages_gpsDecimal($egeoLong[0], $egeoLong[1], $egeoLong[2], $egeoLongR);
					$geoLat = mapimages_gpsDecimal($egeoLat[0], $egeoLat[1], $egeoLat[2], $egeoLatR);

					//create the new point using the gps data
					$additionalContent .= "\n  var tmpPoint = new google.maps.LatLng(".$geoLat.", ".$geoLong.")";

					//add the new marker to the currentMarkerArray so it can be added to the google map once it has been created.
					$additionalContent .= "\n   currentMarkerArray.push(new google.maps.Marker({";
					$additionalContent .= "\n     draggable:false,";
					$additionalContent .= "\n     animation: google.maps.Animation.".$mapimages_options["googlemaps_animation"].",";
					$additionalContent .= "\n     position: tmpPoint";
					$additionalContent .= "\n   }));";

					$additionalContent .= "\n   var contentString = '<img src=\"".$tempContent[$j]."\" width=\"".($mapimages_options["plugin_mapwidth"]*.3)."\"/><br /><a href=\"".$tempURL."\">Go To Post</a>';";


					$additionalContent .= "\n   currentInfoWindowArray.push( new google.maps.InfoWindow({";
					$additionalContent .= "\n     content: contentString";
					$additionalContent .= "\n   }));";
					//$additionalContent .= "\n   currentURLArray.push('".$tempURL."');";

					$imageList[] = $tempContent[$j];
				}
			}
		}
	}

	return array($additionalContent,$imageList);
}


$pageMapCount = 0;
//mapimages_pageshortcodehandler function
//This function returns the code for a new google map object.
function mapimages_pageshortcodehandler($attributes) 
{
	//Include the global page map count
	global $pageMapCount;
	//Include the map images options
	global $mapimages_options;

	//grab last X number of posts (based on configuration settings).
	if($mapimages_options["plugin_numberofpoststoprocess"] == "All")
               	$mapimages_options["plugin_numberofpoststoprocess"] = -1;

	$args = array(
			'posts_per_page'   => $mapimages_options["plugin_numberofpoststoprocess"],
			'offset'           => 0,
			'category'         => '',
			'orderby'          => 'post_date',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'post',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'post_status'      => 'publish',
			'suppress_filters' => true );

	$postContentForProcessing = get_posts( $args );

	$additionalContent = mapimages_createGoogleMaps("pageMap".$pageMapCount);
	$pageMapCount ++;

	$imageList = array();
	for($i=0;$i<count($postContentForProcessing);$i++)
	{
		$tempURL = get_permalink($postContentForProcessing[$i]->ID);
		$returnValue = mapimages_generatemarkers($postContentForProcessing[$i]->post_content, $tempURL);
		$additionalContent .= $returnValue[0];
		$imageList = array_merge($imageList, $returnValue[1]);
	}

	//if no images are found with GPS data, then just return
	if(count($imageList) == 0)
		return "";

	//push the currentMarkerArray to the mapimages_inpostMarkerMapArray
	$additionalContent .= "\n   mapimages_inpostMarkerMapArray.push(currentMarkerArray);";
	$additionalContent .= "\n   mapimages_inpostInfoWindowMapArray.push(currentInfoWindowArray);";

	$additionalContent .= "\n </script>";

	return $additionalContent;
}

function mapimages_inpost_excerpt($content)
{
	//Include the WordPress post variable
	global $post;

	$newContent = mapimages_inpost($post->post_content);

	return $newContent;
}

//mapimages_inpost function
//This function parses all images in a post and adds the google map with markers, then returns the new post
function mapimages_inpost($content)
{
	//Include the WordPress post variable
	global $post;
	//Include the map images options
	global $mapimages_options;

	$additionalContent = mapimages_createGoogleMaps($post->ID);

	$tempURL = get_permalink($post->ID);
	$returnValue = mapimages_generatemarkers($content, $tempURL);
	$additionalContent .= $returnValue[0];

	//push the currentMarkerArray to the mapimages_inpostMarkerMapArray
	$additionalContent .= "\n   mapimages_inpostMarkerMapArray.push(currentMarkerArray);";
	$additionalContent .= "\n   mapimages_inpostInfoWindowMapArray.push(currentInfoWindowArray);";

	$additionalContent .= "\n </script>";

	//if no images are found, just return with no google map.
	if(count($returnValue[1]) == 0 && $mapimages_options["plugin_locationinpost"] == "AT SHORTCODE")
	{
		return preg_replace("/\[mapimages\]/","",$content,1);
	}
	else if(count($returnValue[1]) == 0)
	{
		return $content;
	}
	else if($mapimages_options["plugin_locationinpost"] == "AT SHORTCODE")
	{
		return preg_replace("/\[mapimages\]/",$additionalContent,$content,1);
	}
	else if($mapimages_options["plugin_locationinpost"] == "BEFORE")
	{
		return $additionalContent." ".$content;
	}
	else if($mapimages_options["plugin_locationinpost"] == "AFTER")
	{
		return $content." ".$additionalContent;
	}
	else
	{
//echo "<script>";
//echo "alert('f');";
//echo "</script>";
		return null;
	}
}

//mapimages_inpostHead function
//This function creates all the java script variables that will hold the google map objects for the page.
function mapimages_inpostHead()
{
	//Include the map images options
	global $mapimages_options;

	echo "\n<script>";

	//This varaible holds the google map objects for all posts on the current page
	echo "\n    var mapimages_inpostJavaScriptArray = new Array();";

	//This variable holds the marker arrays for each of the google maps on the current page	
	echo "\n    var mapimages_inpostMarkerMapArray = new Array();";

	//This variable holds the URL arrays for the each of the google maps on the current page
	echo "\n    var mapimages_inpostInfoWindowMapArray = new Array();";

	//This variable holds the google map names for all the posts on the current page
	echo "\n    var mapimages_inpostMapNameArray = new Array();";

	//This function creates all the google map objects, adds the markers, and loads them.
	echo "\n    function mapimages_inpostInitialize()";
	echo "\n    {";
	echo "\n      for(i=0;i<mapimages_inpostJavaScriptArray.length;i++)";
	echo "\n      {";
	echo "\n        temp=mapimages_inpostJavaScriptArray[i]();";

	if($mapimages_options["googlemaps_fitallimages"] == "Yes")
	{
		//The zoom level of the google map is dynamically updated to show all markers in the space provided.
		echo "\n        var bounds = new google.maps.LatLngBounds();";
		echo "\n        for(j=0;j<mapimages_inpostMarkerMapArray[i].length;j++)";
		echo "\n        {";
		echo "\n          mapimages_inpostMarkerMapArray[i][j].setMap(temp);";
		echo "\n          bounds.extend(mapimages_inpostMarkerMapArray[i][j].getPosition());";
		echo "\n          google.maps.event.addListener(mapimages_inpostMarkerMapArray[i][j],'click', (function(i,j,temp) { return function() { mapimages_inpostInfoWindowMapArray[i][j].open(temp,mapimages_inpostMarkerMapArray[i][j]); } })(i,j,temp) );";
//		echo "\n          google.maps.event.addListener(mapimages_inpostMarkerMapArray[i][j],'click', (function(i,j) { return function() { window.location.href = mapimages_inpostInfoWindowMapArray[i][j]; } })(i,j) );";
		echo "\n        }";
		echo "\n        temp.fitBounds(bounds);";
	}
	else if($mapimages_options["googlemaps_fitallimages"] == "No, use first image location and default zoom")
	{
		//The center of the map is updated to the first image location.
		echo "\n        for(j=0;j<mapimages_inpostMarkerMapArray[i].length;j++)";
		echo "\n        {";
		echo "\n          mapimages_inpostMarkerMapArray[i][j].setMap(temp);";
		echo "\n          google.maps.event.addListener(mapimages_inpostMarkerMapArray[i][j],'click', (function(i,j,temp) { return function() { mapimages_inpostInfoWindowMapArray[i][j].open(temp,mapimages_inpostMarkerMapArray[i][j]); } })(i,j,temp) );";
//		echo "\n          google.maps.event.addListener(mapimages_inpostMarkerMapArray[i][j],'click', (function(i,j) { return function() { window.location.href = mapimages_inpostInfoWindowMapArray[i][j]; } })(i,j) );";
		echo "\n        }";
		echo "\n        temp.setCenter(mapimages_inpostMarkerMapArray[i][0].getPosition());";
	}
	else if($mapimages_options["googlemaps_fitallimages"] == "No, use default location and zoom")
	{
		//No additional change to bounds or center is required.
		echo "\n        for(j=0;j<mapimages_inpostMarkerMapArray[i].length;j++)";
		echo "\n        {";
		echo "\n          mapimages_inpostMarkerMapArray[i][j].setMap(temp);";
		echo "\n          google.maps.event.addListener(mapimages_inpostMarkerMapArray[i][j],'click', (function(i,j,temp) { return function() { mapimages_inpostInfoWindowMapArray[i][j].open(temp,mapimages_inpostMarkerMapArray[i][j]); } })(i,j,temp) );";
//		echo "\n          google.maps.event.addListener(mapimages_inpostMarkerMapArray[i][j],'click', (function(i,j) { return function() { window.location.href = mapimages_inpostInfoWindowMapArray[i][j]; } })(i,j) );";
		echo "\n        }";
	}

	echo "\n      }";
	echo "\n    }";

	//once the page has loaded completely begin creating and displaying the google maps
	echo "\n    if (window.attachEvent) {window.attachEvent('onload', mapimages_inpostInitialize);}";
	echo "\n    else if (window.addEventListener) {window.addEventListener('load', mapimages_inpostInitialize, false);}";
	echo "\n    else {document.addEventListener('load', mapimages_inpostInitialize, false);} ";

	echo "\n</script>";
}

//add the google maps api code
function add_mapimages_scripts_method() 
{
        //Include the map images options
        global $mapimages_options;

	wp_enqueue_script('GoogleMapsAPI', 'https://maps.googleapis.com/maps/api/js?key='.$mapimages_options["googlemaps_key"].'&sensor=false', null, null, false);
}
add_action( 'wp_enqueue_scripts', 'add_mapimages_scripts_method' );


if($mapimages_options["plugin_locationinpost"] != "OFF")
{
	//add the code for this plugin to process images in posts
	add_filter( 'the_content', 'mapimages_inpost', 5);

	//add the code for this plugin to process images in posts
	add_filter( 'get_the_excerpt', 'mapimages_inpost_excerpt', 5);
}

	//add the code to declare all javascript variables and allow processing after the page has finished loading
	add_action('wp_head', 'mapimages_inpostHead');

if($mapimages_options["plugin_onpage"] == "ON")
{
	//tell wordpress to register the mapimages shortcode
	add_shortcode("mapimages_onpage", "mapimages_pageshortcodehandler");
}

?>
