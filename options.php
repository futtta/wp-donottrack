<?php
	$plugin_dir = basename(dirname(__FILE__)).'/languages';
	load_plugin_textdomain( 'wp-donottrack', false, $plugin_dir );

        $dnt_plugin_url = defined('WP_PLUGIN_URL') ? trailingslashit(WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__))) : trailingslashit(get_bloginfo('wpurl')) . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__));

add_action('admin_menu', 'dnt_create_menu');

// todo: get all options (in array) to replace all seperate get_option-calls

function dnt_create_menu() {
        $hook=add_options_page( 'WP DoNotTrack settings', 'WP DoNotTrack', 'manage_options', 'dnt_settings_page', 'dnt_settings_page');
        add_action( 'admin_init', 'register_dnt_settings' );
        add_action( 'admin_print_scripts-'.$hook, 'dnt_admin_scripts' );
        add_action( 'admin_print_styles-'.$hook, 'dnt_admin_styles' );
}

function register_dnt_settings() {
	register_setting( 'dnt-settings-group', 'listmode' );
	register_setting( 'dnt-settings-group', 'whitelist' );
	register_setting( 'dnt-settings-group', 'blacklist' );
	register_setting( 'dnt-settings-group', 'ifdnt' );
	register_setting( 'dnt-settings-group', 'agressive' );
}

function dnt_admin_scripts() {
	global $dnt_plugin_url;
	wp_enqueue_script('jqzrssfeed',$dnt_plugin_url.'external/js/jquery.zrssfeed.min.js',array('jquery'),null,true);
	wp_enqueue_script('jqcookie',$dnt_plugin_url.'external/js/jquery.cookie.min.js',array('jquery'),null,true);
}

function dnt_admin_styles() {
        global $dnt_plugin_url;
        wp_enqueue_style('zrssfeed',$dnt_plugin_url.'external/js/jquery.zrssfeed.css');
}

function dnt_settings_page() {
global $defBlack, $defWhite, $wpHost, $ssl;
?>
<div class="wrap">
<h2><?php _e("WP DoNotTrack Settings","wp-donottrack") ?></h2>
<div style="float:left;width:75%;">
<p><?php _e("<a href=\"http://blog.futtta.be/tag/donottrack/\" target=\"_blank\">WP DoNotTrack</a> stops plugins and themes from adding javascript-initiated 3rd party tracking code to your site. It helps to improve the overall quality of WordPress, enhancing <a href=\"http://blog.futtta.be/2011/02/17/why-your-wordpress-blog-needs-donottrack/\" target=\"_blank\">not only its privacy, but also performance and security</a>! You can find more information <a href=\"http://wordpress.org/extend/plugins/wp-donottrack/faq/\" target=\"_blank\">in the FAQ on wordpress.org</a>.","wp-donottrack") ?></p>
<p><?php _e("You can modify WP DoNotTrack's behaviour by changing the following settings:","wp-donottrack") ?></p>
<form method="post" action="options.php" id="wp-donottrack-form">
    <?php settings_fields( 'dnt-settings-group' ); ?>
    <table class="form-table">
         <tr valign="top">
                <th scope="row"><?php _e("Stop 3rd party tracking for all visitors?","wp-donottrack") ?></th>
                <td>
                        <fieldset>
                                <legend class="screen-reader-text"><span>Who do you want to stop tracking for?</span></legend>
                                <label title="Only stop tracking for people with the DoNotTrack browser setting (does not work in Chrome) or based on the presence of a cookie."><input type="radio" name="ifdnt" value="1" <?php if (get_option('ifdnt','1')==="1") echo "checked" ?> /><?php _e("Stop tracking for people who have their <a href=\"http://wordpress.org/extend/plugins/wp-donottrack/faq/\">browser configured to do so or based on the presence of a cookie</a>.","wp-donottrack") ?></label><br />
				<label title="Privacy for all!"><input type="radio" name="ifdnt" value="0" <?php if (get_option('ifdnt','0')!=="1") echo "checked" ?> /><?php _e("Disable tracking for all my visitors!","wp-donottrack") ?></label><br />
                        </fieldset>
                        <span class="description"><?php _e( "Recent versions of all major browsers (<a href=\"http://www.wired.com/epicenter/2011/04/chrome-do-not-track/all/1\" target=\"_blank\">except Chrome</a>) allow users to opt out of tracking, in which case WP DoNotTrack can test for navigator.doNotTrack to conditionally stop 3rd party tracking. Alternatively WP DoNotTrack can act on the presence of a dont_track_me=1 cookie.", "wp-donottrack" )  ?></span>
                </td>
         </tr>
         <tr valign="top">
                <th scope="row"><?php _e("Do you want to run in normal, forced or SuperClean mode?","wp-donottrack") ?></th>
                <td>
                        <fieldset>
                                <legend class="screen-reader-text"><span>Normal, forced or Superclean mode?</span></legend>
                                <label title="Normal"><input type="radio" name="agressive" value="0" <?php if (get_option('agressive','1')==="0") echo "checked" ?> /><?php _e("Normal (least invasive)","wp-donottrack") ?></label><br />
                                <label title="Forced"><input type="radio" name="agressive" value="1" <?php if (get_option('agressive','1')==="1") echo "checked" ?> /><?php _e("Forced (default)","wp-donottrack") ?></label><br />
                                <label class="notdntcompatible" title="SuperClean"<?php if (get_option('ifdnt','0')==="1") echo "style=\"display:none\"" ?>><input type="radio" name="agressive" value="2" <?php if (get_option('agressive','1')==="2") echo "checked" ?> /><?php _e("SuperClean (most invasive)","wp-donottrack") ?></label><br />
                        </fieldset>
                        <span class="description"><?php _e( "\"Normal\" gently asks WordPress to add the WP DoNotTrack javascript to the HTML to check elements being added to your page. \"Forced\" adds the JavaScript with output buffering instead, to stop optimizing plugins (e.g. Autoptimize and W3 Total Cache) from loading WP DoNotTrack javascript too late. \"SuperClean\" uses the output buffering to also filter image, iframe and script tags in your HTML.", "wp-donottrack" )  ?></span>
                </td>
         </tr>
	 <tr valign="top">
	 	<th scope="row"><?php _e("Do you want to run WP DoNotTrack in black- or whitelist mode?","wp-donottrack") ?></th>
		<td>
			<fieldset>
				<legend class="screen-reader-text"><span>Activate DoNotTrack</span></legend>
				<label title="whitelist"><input type="radio" name="listmode" value="1" <?php if (get_option('listmode','0')==="1") echo "checked" ?> /><?php _e("Whitelist","wp-donottrack") ?></label><br />
				<label title="blacklist (default)"><input type="radio" name="listmode" value="0" <?php if (get_option('listmode','0')!=="1") echo "checked" ?> /><?php _e("Blacklist","wp-donottrack") ?></label>
			</fieldset>
			<span class="description"><?php _e( "Blacklist-mode is easier to set up, but less secure as it won't stop code from being added later. Whitelist-mode is more future-proof, but you'll have to explicitely identify the 3rd parties that are allowed to add elements to the DOM.", "wp-donottrack" )  ?></span>
		</td>
         </tr>
         <tr valign="top" id="whitelistdiv" <?php if (get_option('listmode','0')!=="1") echo "style=\"display:none\"" ?>>
                <th scope="row"><?php _e( "Whitelist:", "wp-donottrack" ); ?></label></th>
                <td>
		<?php
			$whitelist=get_option('whitelist',$defWhite);
                	if(strripos($whitelist,$wpHost)===false) {
                        	if ($whitelist!="") {
					$whitelist=$whitelist.",".$wpHost;
				} else {
					$whitelist=$wpHost;
				}
                        }
		?>
                        <input type="text" name="whitelist" value="<?php echo(trim($whitelist,',')); ?>" size="80" class="regular-text code" /><br />
                        <span class="description"><?php _e( "Comma-seperated list of the domains you want to allow to be added to your blog (your blog will be auto-whitelisted).", "wp-donottrack" )  ?></span>
                </td>
        </tr>
         <tr valign="top" id="blacklistdiv" <?php if (get_option('listmode','0')==="1") echo "style=\"display:none\"" ?>>
                <th scope="row"><?php _e( "Blacklist:", "wp-donottrack" ); ?></label></th>
                <td>
                        <input type="text" name="blacklist" value="<?php echo trim(get_option('blacklist',$defBlack),','); ?>" size="80" class="regular-text code" /><br />
                        <span class="description"><?php _e( "Comma-seperated list of the domains you want to exclude from being added to your blog", "wp-donottrack" )  ?></span>
                </td>
        </tr>
    </table>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('input:radio[name=listmode]').change(function() {
	  if (jQuery('input:radio[name=listmode]:checked').val()==="1") {
		jQuery("#blacklistdiv").hide();
		jQuery("#whitelistdiv").show(800);
	  } else {
		jQuery("#whitelistdiv").hide();
		jQuery("#blacklistdiv").show(800);
	  }
	})

	jQuery('input:radio[name=ifdnt]').change(function() {
	  if (jQuery('input:radio[name=ifdnt]:checked').val()==="1") {
	  	if (jQuery('input:radio[name=agressive]:checked').val()==="2") {
			jQuery("input:radio[name=agressive]")[1].checked=true;
		}
		jQuery(".notdntcompatible").hide(800);
	  } else {
		jQuery(".notdntcompatible").show(800);
	  }
	});
})
</script>
</div>
<div style="float:right;width:25%" id="dnt_admin_feed">
        <div style="margin-left:10px;margin-top:-5px;">
                <h3>
                        <?php _e("futtta about","wp-donottrack") ?>
                        <select id="feed_dropdown" >
                                <option value="1"><?php _e("WP DoNotTrack","wp-donottrack") ?></option>
                                <option value="2"><?php _e("WordPress","wp-donottrack") ?></option>
                                <option value="3"><?php _e("Web Technology","wp-donottrack") ?></option>
                        </select>
                </h3>
                <div id="futtta_feed">You might want to add googleapis.com to your whitelist for this rss-widget to work :-)</div>
        </div>
</div>

<script type="text/javascript">
	var feed = new Array;
	feed[1]="http://feeds.feedburner.com/futtta_wp-donottrack";
	feed[2]="http://feeds.feedburner.com/futtta_wordpress";
	feed[3]="http://feeds.feedburner.com/futtta_webtech";
	cookiename="wp-donottrack_feed";

	jQuery(document).ready(function() {
		jQuery("#feed_dropdown").change(function() { show_feed(jQuery("#feed_dropdown").val()) });

		feedid=jQuery.cookie(cookiename);
		if(typeof(feedid) !== "string") feedid=1;

		show_feed(feedid);
	})

	function show_feed(id) {
  		jQuery('#futtta_feed').rssfeed(feed[id], {
			<?php if ($ssl) echo "ssl: true,"; ?>
    			limit: 4,
			date: true,
			header: false
  		});
		jQuery("#feed_dropdown").val(id);
		jQuery.cookie(cookiename,id,{ expires: 365 });
	}
</script>

</div>
<?php } ?>
