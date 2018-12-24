<?php

add_action( 'admin_menu', 'wp_donottrack_add_admin_menu' );
add_action( 'admin_init', 'wp_donottrack_settings_init' );

function wp_donottrack_add_admin_menu() {
	add_options_page( 'Options for WP DoNotTrack', 'WP DoNotTrack', 'manage_options', 'wp-donottrack', 'wp_donottrack_options_page_render' );
}

function wp_donottrack_options_page_render() {
	?>
	<form action='options.php' method='post'>
		<h2><?php echo __( "Options for WP DoNotTrack", "wp-donottrack" ) ?></h2>

		<?php
		settings_fields( 'wp-donottrack' );
		do_settings_sections( 'wp-donottrack' );
		submit_button();
		?>
	</form>
	<?php
	wp_donottrack_settings_jquery();
}

function wp_donottrack_settings_init() {
	register_setting( 'wp-donottrack', 'wp_donottrack_settings' );

	add_settings_section(
		'wp_donottrack_scope_section',
		__( 'Who do you want to stop tracking for?', 'wp-donottrack' ),
		'wp_donottrack_scope_section_callback',
		'wp-donottrack' );

	add_settings_field(
		'wp_donottrack_scope',
		__( 'Visitor profile', 'wp-donottrack' ),
		'wp_donottrack_scope_render',
		'wp-donottrack',
		'wp_donottrack_scope_section' );

	add_settings_section(
		'wp_donottrack_level_section',
		__( 'Normal, forced or Superclean mode?', 'wp-donottrack' ),
		'wp_donottrack_level_section_callback',
		'wp-donottrack' );

	add_settings_field(
		'wp_donottrack_level',
		__( 'Operating mode', 'wp-donottrack' ),
		'wp_donottrack_level_render',
		'wp-donottrack',
		'wp_donottrack_level_section' );

	add_settings_section(
		'wp_donottrack_list_section',
		__( 'Blacklist or Whitelist mode?', 'wp-donottrack' ),
		'wp_donottrack_list_section_callback',
		'wp-donottrack' );

	add_settings_field(
		'wp_donottrack_listmode',
		__( 'List mode', 'wp-donottrack' ),
		'wp_donottrack_listmode_render',
		'wp-donottrack',
		'wp_donottrack_list_section' );

	add_settings_section(
		'wp_donottrack_thirdparty_section',
		__( '3rd Party Plugins and Libraries', 'wp-donottrack' ),
		'wp_donottrack_thirdparty_section_callback',
		'wp-donottrack' );

  add_settings_field(
    'wp_donottrack_thirdparty_googleanalytics',
    __( 'Google Analytics', 'wp-donottrack' ),
    'wp_donottrack_thirdparty_googleanalytics_render',
    'wp-donottrack',
    'wp_donottrack_thirdparty_section' );

	if( is_plugin_active( "addthis/addthis.php" ) ) {
		add_settings_field(
			'wp_donottrack_thirdparty_addthis',
			__( 'addthis', 'wp-donottrack' ),
			'wp_donottrack_thirdparty_addthis_render',
			'wp-donottrack',
			'wp_donottrack_thirdparty_section' );
	}

	if( is_plugin_active( "add-to-any/add-to-any.php" ) ) {
		add_settings_field(
			'wp_donottrack_thirdparty_addtoany',
			__( 'add-to-any', 'wp-donottrack' ),
			'wp_donottrack_thirdparty_addtoany_render',
			'wp-donottrack',
			'wp_donottrack_thirdparty_section' );
	}
}

function wp_donottrack_settings_validate() {
}

function wp_donottrack_scope_section_callback() {
	echo __( 'Recent versions of all major browsers (<a href="http://www.wired.com/epicenter/2011/04/chrome-do-not-track/all/1" target="_blank">except Chrome</a>) allow users to opt out of tracking, in which case WP DoNotTrack can test for navigator.doNotTrack to conditionally stop 3rd party tracking. Alternatively WP DoNotTrack can act on the presence of a dont_track_me=1 cookie.', 'wp-donottrack' );
}

function wp_donottrack_scope_render() {
	$options = wp_donottrack_get_option();
	?>
	<fieldset>
		<label title="<?php echo __( 'Only stop tracking for people with the DoNotTrack browser setting (does not work in Chrome) or based on the presence of a cookie.', 'wp-donottrack' ); ?>"><input type='radio' name='wp_donottrack_settings[scope]' <?php checked( $options['scope'], '1' ); ?> value='1'><?php echo __( 'Stop tracking for people who have their <a href="http://wordpress.org/extend/plugins/wp-donottrack/faq/">browser configured to do so or based on the presence of a cookie</a>.', 'wp-donottrack' ); ?></label></br>
		<label title="<?php echo __( 'Privacy for all!', 'wp-donottrack' ); ?>"><input type='radio' name='wp_donottrack_settings[scope]' <?php checked( $options['scope'], '0' ); ?> value='0'><?php echo __( 'Disable tracking for all my visitors!', 'wp-donottrack' ); ?></label><br />
	</fieldset>
	<?php
}

function wp_donottrack_level_section_callback() {
	echo __( '"Normal" gently asks WordPress to add the WP DoNotTrack javascript to the HTML to check elements being added to your page. "Forced" adds the JavaScript with output buffering instead, to stop optimizing plugins (e.g. Autoptimize and W3 Total Cache) from loading WP DoNotTrack javascript too late. "SuperClean" uses the output buffering to also filter image, iframe and script tags in your HTML.', 'wp-donottrack' );
}

function wp_donottrack_level_render() {
	$options = wp_donottrack_get_option();
	?>
	<fieldset>
		<label title="<?php echo __( 'Normal', 'wp-donottrack' ); ?>"><input type='radio' name='wp_donottrack_settings[level]' <?php checked( $options['level'], 0 ); ?> value='0'><?php echo __( 'Normal (least invasive)', 'wp-donottrack' ); ?></label></br>
		<label title="<?php echo __( 'Forced', 'wp-donottrack' ); ?>"><input type='radio' name='wp_donottrack_settings[level]' <?php checked( $options['level'], 1 ); ?> value='1'><?php echo __( 'Forced (default)', 'wp-donottrack' ); ?></label><br />
		<label class="wp_donottrack_superclean" title="<?php echo __( 'SuperClean', 'wp-donottrack' ); ?>"><input type='radio' name='wp_donottrack_settings[level]' <?php checked( $options['level'], 2 ); if( $options['scope'] === '1' ) echo "style=\"display:none\""; ?> value='2' ><?php echo __( 'SuperClean (most invasive)', 'wp-donottrack' ); ?></label><br />
	</fieldset>
	<?php
}

function wp_donottrack_list_section_callback() {
	echo __( "Blacklist-mode is easier to set up, but less secure as it won't stop code from being added later. Whitelist-mode is more future-proof, but you'll have to explicitely identify the 3rd parties that are allowed to add elements to the DOM.", "wp-donottrack" );
}

function wp_donottrack_listmode_render() {
	$options = wp_donottrack_get_option();
	?>
	<fieldset>
		<label title="<?php echo __( 'Blacklist', 'wp-donottrack' ); ?>"><input type='radio' name='wp_donottrack_settings[listmode]' <?php checked( $options['listmode'], 0 ); ?> value='0'><?php echo __( "Blacklist (all listed domains will be blocked)", "wp-donottrack" ); ?></label></br>
		<label title="<?php echo __( 'Whitelist', 'wp-donottrack' ); ?>"><input type='radio' name='wp_donottrack_settings[listmode]' <?php checked( $options['listmode'], 1 ); ?> value='1'><?php echo __( "Whitelist (all BUT the listed domains will be blocked)", "wp-donottrack" ); ?></label><br />
	</fieldset>
	<div id="blacklistdiv" <?php if( $options['listmode'] !== '0' ) echo "style=\"display:none\""; ?>>
		<label title="<?php echo __( 'Blacklist', 'wp-donottrack' ); ?>" for="wp_donottrack_blacklist"><input id="wp_donottrack_blacklist" type="text" name="wp_donottrack_settings[blacklist]" value="<?php echo $options['blacklist'] ?>" size="80" class="regular-text code" /></label><br />
		<span class="description"><?php echo __( "Comma-seperated list of the domains you want to exclude from being added to your blog", "wp-donottrack" ) ?></span>
	</div>
	<div id="whitelistdiv" <?php if( $options['listmode'] !== '1' ) echo "style=\"display:none\""; ?>>
		<label title="<?php echo __( 'Whitelist', 'wp-donottrack' ); ?>" for="wp_donottrack_whitelist"><input id="wp_donottrack_whitelist" type="text" name="wp_donottrack_settings[whitelist]" value="<?php echo $options['whitelist'] ?>" size="80" class="regular-text code" /></label><br />
		<span class="description"><?php echo __( "Comma-seperated list of the domains you want to allow to be added to your blog (your blog will be auto-whitelisted)", "wp-donottrack" ) ?></span>
	</div>
	<?php
}

function wp_donottrack_thirdparty_section_callback() {
	echo __( "WP DoNotTrack can try to assist in anonymizing different 3rd party plugins and libraries, if they are included to this WordPress instance. Please bear in mind, that success may heavily depend on the order, in which the plugins and libraries are loaded (which this plugin does not take control of) and the recognition of external configuration. Anonymization therefore can not be guaranteed and has to be evaluated before productive usage.", "wp-donottrack" );
}

function wp_donottrack_thirdparty_addthis_render() {
	$options = wp_donottrack_get_option();
	?>
	<label title="<?php echo __( 'Use AddThis plugin without cookies', 'wp-donottrack' ) ?>"><input type='checkbox' name='wp_donottrack_settings[thirdparty][addthis]' <?php checked( $options['thirdparty']['addthis'], 1 ); ?> value='1'><?php echo __( 'Instruct the <a href="https://wordpress.org/plugins/addthis/" target="_blank">AddThis plugin</a> to not use cookies.', "wp-donottrack" ) ?></label>
	<?php
}

function wp_donottrack_thirdparty_addtoany_render() {
	$options = wp_donottrack_get_option();
	?>
	<label title="<?php echo __( 'Instruct Add-To-Any plugin to not use 3rd party cookies', 'wp-donottrack' ) ?>"><input type='checkbox' name='wp_donottrack_settings[thirdparty][add-to-any]' <?php checked( $options['thirdparty']['add-to-any'], 1 ); ?> value='1'><?php echo __( 'Tell the <a href="https://wordpress.org/plugins/add-to-any/" target="_blank">Add-To-Any plugin</a> to disable 3rd party cookies.', "wp-donottrack" ) ?></label>
	<?php
}

function wp_donottrack_thirdparty_googleanalytics_render() {
	$options = wp_donottrack_get_option();
	?>
	<label title="<?php echo __( 'IP address anonymization for Google Analytics', 'wp-donottrack' ) ?>"><input type='checkbox' name='wp_donottrack_settings[thirdparty][googleanalytics]' <?php checked( $options['thirdparty']['googleanalytics'], 1 ); ?> value='1'><?php echo __( 'Instruct Google Analytics (<a href="https://developers.google.com/analytics/devguides/collection/gtagjs/" target="_blank">Global Site Tag/gtag.js</a>, <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/" target="_blank">Universal Analytics/analytics.js</a> and <a href="https://developers.google.com/analytics/devguides/collection/gajs/" target="_blank">Classical Google Analytics/ga.js</a>) to anonymize IP addresses.', "wp-donottrack" ) ?></label>
	<?php
}

function wp_donottrack_settings_jquery() {
	?>
	<script type="text/javascript">
		jQuery( document ).ready( function() {
			jQuery( 'input:radio[name="wp_donottrack_settings[listmode]"]' ).change( function() {
				if( jQuery( 'input:radio[name="wp_donottrack_settings[listmode]"]:checked' ).val() === "1") {
					jQuery( "#blacklistdiv" ).hide( 800 );
					jQuery( "#whitelistdiv" ).show( 800 );
				} else {
					jQuery( "#whitelistdiv" ).hide( 800 );
					jQuery( "#blacklistdiv" ).show( 800 );
				}
			} );

			jQuery( 'input:radio[name="wp_donottrack_settings[scope]"]' ).change( function() {
				if( jQuery( 'input:radio[name="wp_donottrack_settings[scope]"]:checked' ).val() === "1") {
					if( jQuery( 'input:radio[name="wp_donottrack_settings[level]"]:checked' ).val() === "2") {
						jQuery( 'input:radio[name="wp_donottrack_settings[level]"]' )[1].checked = true;
					}
					jQuery( ".wp_donottrack_superclean" ).hide( 800 );
				} else {
					jQuery( ".wp_donottrack_superclean" ).show( 800 );
				}
			} );
		} )
	</script>
	<?php
}
?>
