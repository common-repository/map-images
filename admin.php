<?php

//Add hook for admin initialization menu
add_action( 'admin_menu', 'mapimages_menu' );

//mapimages_menu function
//This function adds an option menu for the Map Images plugin, and it also sets up the settings page.
function mapimages_menu()
{
	add_options_page( 'Map Images Options', 'Map Images', 'manage_options', 'mapimagesadmin.php', 'mapimages_plugin_options' );

	//call register settings function
	add_action( 'admin_init', 'register_mapimagessettings' );
}

//register_mapimagessettings function
//This function registers the mapimages_options variable that will store the settings in the WordPress database.
//It also configures the settings sections and settings fields for the WordPress options page generator.
function register_mapimagessettings()
{
	register_setting('mapimages_option_group', 'mapimages_options', 'mapimages_check_value');

	//Generic Plugin settings
	add_settings_section(
		'mapimages_section_plugin',
		'Plugin Settings',
		'mapimages_printplugin_section_info',
		'mapimages_menu'
	);

	//The last element of the settings_field array is an array used by the html form element generator function.
	//For a text element
	//	array('field_name','text','default value', 'additional note')
	//For a select element
	//	array('field_name','select',number of options,'option1','option2','option...', 'additional note')

	//Maps In Post settings
	add_settings_section(
		'mapimages_section_inpost',
		'Maps In Post Settings',
		'mapimages_inpost_section_info',
		'mapimages_menu'
	);

	add_settings_field(
		'plugin_locationinpost', 
		'Plugin Location In Post', 
		'mapimages_setting_string', 
		'mapimages_menu',
		'mapimages_section_inpost',
		array('plugin_locationinpost','select',4,'AFTER','BEFORE','AT SHORTCODE', 'OFF', 'shortcode = [mapimages]')
	);


	//Maps On Page settings
	add_settings_section(
		'mapimages_section_onpage',
		'Maps On Page Settings',
		'mapimages_onpage_section_info',
		'mapimages_menu'
	);

	add_settings_field(
		'plugin_onpage', 
		'Plugin On Page', 
		'mapimages_setting_string', 
		'mapimages_menu',
		'mapimages_section_onpage',
		array('plugin_onpage','select',2,'OFF','ON', 'shortcode = [mapimages_onpage]')
	);

	add_settings_field(
		'plugin_numberofpoststoprocess', 
		'Number of posts to process', 
		'mapimages_setting_string', 
		'mapimages_menu',
		'mapimages_section_onpage',
		array('plugin_numberofpoststoprocess','select',4,'10','20','50','All', 'More posts may cause a slower load time.')
	);

	//Map settings
	add_settings_section(
		'mapimages_section_map',
		'Map Settings',
		'mapimages_map_section_info',
		'mapimages_menu'
	);

	add_settings_field(
		'plugin_mapwidth', 
		'Plugin Map Width', 
		'mapimages_setting_string', 
		'mapimages_menu',
		'mapimages_section_map',
		array('plugin_mapwidth','text','300')
	);

	add_settings_field(
		'plugin_mapheight', 
		'Plugin Map Width', 
		'mapimages_setting_string', 
		'mapimages_menu',
		'mapimages_section_map',
		array('plugin_mapheight','text','300')
	);

	//Google Maps API settings
	add_settings_section(
		'mapimages_section_googlemaps',
		'Google Maps API Settings',
		'mapimages_printgoogle_section_info',
		'mapimages_menu'
	);

	add_settings_field(
		'googlemaps_key', 
		'Google Maps Key', 
		'mapimages_setting_string', 
		'mapimages_menu',
		'mapimages_section_googlemaps',
		array('googlemaps_key','text')
	);

	add_settings_field(
		'googlemaps_animation', 
		'Google Maps Animation', 
		'mapimages_setting_string', 
		'mapimages_menu',
		'mapimages_section_googlemaps',
		array('googlemaps_animation','select',2,'BOUNCE','DROP')
	);

	add_settings_field(
		'googlemaps_maptype', 
		'Google Maps MapType', 
		'mapimages_setting_string', 
		'mapimages_menu',
		'mapimages_section_googlemaps',
		array('googlemaps_maptype','select',4,'ROADMAP','SATELLITE','HYBRID','TERRAIN')
	);

	add_settings_field(
		'googlemaps_fitallimages', 
		'Fit All Images on Google Map', 
		'mapimages_setting_string', 
		'mapimages_menu',
		'mapimages_section_googlemaps',
		array('googlemaps_fitallimages','select',3,'Yes','No, use first image location and default zoom','No, use default location and zoom')
	);

	add_settings_field(
		'googlemaps_defaultcenter', 
		'Google Maps Default Center', 
		'mapimages_setting_string', 
		'mapimages_menu',
		'mapimages_section_googlemaps',
		array('googlemaps_defaultcenter','text','28.111771 , -80.700138')
	);

	add_settings_field(
		'googlemaps_defaultzoom', 
		'Google Maps Default Zoom', 
		'mapimages_setting_string', 
		'mapimages_menu',
		'mapimages_section_googlemaps',
		array('googlemaps_defaultzoom','text','12','0 = the whole world<br />21 = individual buildings')
	);


}

//mapimages_settings_string function
//This is a generic function that generates the html form fields for each of the settings described above.
function mapimages_setting_string($input)
{
	//Grab the current value of the settings from the WordPress database
	$options = get_option('mapimages_options');

	if($input[1] == 'text')
	{
		//create a generic html text field, if the value is not already set, then populate it with the default value from the settings_field array
		echo "<input id='mapimages_".$input[0]."' name='mapimages_options[".$input[0]."]' type='text' size='40' value='".(array_key_exists($input[0],$options) ? $options[$input[0]]:$input[2])."' />";
		if($input[3])
			echo "<br />".$input[3];
	}
	else if($input[1] == 'select')
	{
		//create a generic select drop down with the options from the settings_field array
		echo "<select id='mapimages_".$input[0]."' name='mapimages_options[".$input[0]."]'>";
		$i=0;
		for($i;$i<$input[2];$i++)
		{
			echo "<option value='".$input[($i+3)]."' ".($input[($i+3)] == $options[$input[0]] ? "SELECTED":"").">";
			echo $input[($i+3)];
			echo "</option>";
		}
		echo "</select>";
		if($input[($i+3)])
			echo "<br />".$input[($i+3)];
	}
}

//mapimages_check_value function
//This function will validate the input for the options to ensure valid data
function mapimages_check_value($input)
{
	return $input;
}

//mapimages_plugin_options function
//This function provides the action and layout for the options page.
function mapimages_plugin_options()
{
	//If the user is not an administator, they cannot manage the options.
	if ( !current_user_can( 'manage_options' ) )
	{
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	echo '<div class="wrap">';
	echo '<form method="post" action="options.php">';

	//Declare the settings that will be displayed on this page
	settings_fields( 'mapimages_option_group' );

	//Display the settings for this page.
	do_settings_sections('mapimages_menu');

	//Display a submit button
	submit_button();
	echo '</form>';
	echo '</div>';
}

//mapimages_printgoogle_section_info function
//This function displays the header just above the Google Maps API settings
function mapimages_printgoogle_section_info($input)
{
	echo "In order for Google Maps to work properly, a Google Maps API Key must be obtained.  The key is free for a limited number of map generations, 25,000 requests/day at the time of this writing (March 19, 2013).  For more information on obtaining an API Key, visit Google's developer resources (<a href='https://developers.google.com/maps/documentation/javascript/tutorial'>https://developers.google.com/maps/documentation/javascript/tutorial</a>).<br /><br />Enter your Google Maps API settings below:";
}


//mapimages_inpost_section_info function
//This function displays the header just above the In Post settings
function mapimages_inpost_section_info($input)
{
	echo "The settings below are related to in post mapping functionality.";
}

//mapimages_onpage_section_info function
//This function displays the header just above the On Page settings
function mapimages_onpage_section_info($input)
{
	echo "The settings below are related to page mapping functionality.";
}

//mapimages_map_section_info function
//This function displays the header just above the Map settings
function mapimages_map_section_info($input)
{
	echo "The settings below are for all maps (on page and in post).";
}

//mapimages_printplugin_section_info function
//This function displays the header just above the generic plugin settings
function mapimages_printplugin_section_info($input)
{
	echo 'Enter your Map Image Plugin settings below:';
}

?>
