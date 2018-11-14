<?php
/*
Plugin Name: WP DoNotTrack
Plugin URI: http://blog.futtta.be/wp-donottrack/
Description: Stop plugins and themes from inserting 3rd party tracking code and cookies.
Author: Frank Goossens (futtta)
Version: 0.9.0
Author URI: http://blog.futtta.be/
Text Domain: wp-donottrack
Domain Path: /languages
*/

$debug = false;

if( !$debug ) {
	$dnt_version = "0.9.0";
	$dnt_file = "donottrack-min.js";
	$aop_file = "aop-min.js";
} else {
	$dnt_version = rand()/1000;
	$dnt_file = "donottrack.js";
	$aop_file = "aop.js";
}

$dnt_lang_dir = basename( dirname( __FILE__ ) ) . '/languages';
load_plugin_textdomain( 'wp-donottrack', null, $dnt_lang_dir );

$dnt_plugin_url = plugins_url( $dnt_file, __FILE__ );

if( is_ssl() ) {
	dnt_plugin_url = str_replace( "http:", "https:", $dnt_plugin_url );
	$ssl = true;
}

$wp_donottrack_js = $dnt_plugin_url . "?dntver=" . $dnt_version;

if( is_admin() ) {
	require_once( dirname( __FILE__ ) . '/options.php' );
}

$options = wp_donottrack_get_option();

function wp_donottrack_get_host() {
	return parse_url( get_bloginfo( 'wpurl' ), PHP_URL_HOST );
}

function wp_donottrack_get_white_list( $wantArray = false ) {
	$host = wp_donottrack_get_host();
	$options = wp_donottrack_get_option();

	$whitelist = trim( $options['whitelist'], ',' );
	$whitelist = str_replace( ' ', '', $whitelist );
	$whitelist = array_filter( explode( ',', $whitelist ) );

	if( !in_array( $host, $whitelist ) ) {
		$whitelist[] = $host;
	}

	if( !$wantArray ) {
		$whitelist = json_encode( $whitelist );
	}

	return $whitelist;
}

function wp_donottrack_get_black_list( $wantArray = false ) {
	$options = wp_donottrack_get_option();

	$blacklist = trim( $options['blacklist'], ',' );
	$blacklist = str_replace( ' ', '', $blacklist );
	$blacklist = array_filter( explode( ',', $blacklist ) );

	if( !$wantArray ) {
		$blacklist = json_encode( $blacklist );
	}

	return $blacklist;
}

function wp_donottrack_config( $noEcho ) {
	$options = wp_donottrack_get_option();

	switch( $options['listmode'] ) {
		case "1":
			$listmode = "whitelist";
			$whitelist = wp_donottrack_get_white_list();
			$blacklist = "[]";
			break;
		case "2":
			$listmode = "blacklist";
			$blacklist = wp_donottrack_get_black_list();
			$whitelist = "[]";
			break;
	}

	$result = "<script type=\"text/javascript\">var wpdnt_config={scope:\"" . $options['scope'] . "\",listmode:\"" . $options['listmode'] . "\",black:" . $blacklist . ",white:" . $whitelist . "};</script>";

	if( $noEcho ) {
		return $result;
	} else {
		echo $result;
	}
}

function wp_donottrack_init() {
	global $wp_donottrack_js;
	wp_enqueue_script( 'wp-donottrack', $wp_donottrack_js );
}

function wp_donottrack_footer( $noEcho ) {
	$options = wp_donottrack_get_option();
	if( $options['scope'] === "1" ) {
		$prependJS = "if((navigator.doNotTrack===\"yes\") || (navigator.msDoNotTrack==\"1\") || (navigator.doNotTrack==\"1\") || (document.cookie.indexOf(\"dont_track_me=1\")!==-1) || (document.cookie.indexOf(\"civicAllowCookies=no\")!==-1)) { \n";
		$appendJS = " \n }";
	} else {
		$prependJS = "";
		$appendJS = "";
	}

	$result = "<script type=\"text/javascript\">" . $prependJS . "aop_around(document.body, 'appendChild'); aop_around(document.body, 'insertBefore'); " . $appendJS . "</script>";

	if( $noEcho ) {
		return $result;
	} else {
		echo $result;
	}
}

function wp_donottrack_ob_setup(){
	ob_start( 'wp_donottrack_ob_filter' );
}

function wp_donottrack_ob_filter( $html ){
	global $wp_donottrack_js, $dnt_file, $debug;

	$options = wp_donottrack_get_option();

	$dnt_config = wp_donottrack_config(1);

	if( apply_filters( 'wp_donottrack_inline_js', true ) ) {
		$dnt_file_path = WP_PLUGIN_DIR . "/" . basename( dirname( __FILE__ ) ) . "/" . $dnt_file;
		$dnt_file_contents = file_get_contents($dnt_file_path);
		$dnt_setup = "<script type=\"text/javascript\">" . $dnt_file_contents . "</script>";
	} else {
		$dnt_setup = "<script type=\"text/javascript\" src=\"" . $wp_donottrack_js . "\"></script>";
	}
	$dnt_onBody = wp_donottrack_footer(1);

	$html = str_replace( "</head>", $dnt_config . $dnt_setup . "\n</head>", $html );
	$html = preg_replace( "/(<body\b[^>]*>)/", "$1\n" . $dnt_onBody, $html );

	if( $options['level'] === "2" ) {
		require_once( dirname( __FILE__ ) . "/external/php/simplehtmldom/simple_html_dom.php" );
		$dom = str_get_html( $html, true, true, "UTF-8", false, "\n" );

		if( method_exists( $dom, "find" ) ) {
			if( $dom->find( 'html' ) ) {
				if( $options['listmode'] === "1" ) {
					$white = wp_donottrack_get_white_list( true );
				} else {
					$black = wp_donottrack_get_black_list( true );
				}

				foreach( $dom->find('img[src], script[src], iframe[src]') as $insourced ) {
					if( strpos( $insourced->src, "//" ) === 0 ) {
						$inSrc = "http:" . $insourced->src;
					} else {
						$inSrc = $insourced->src;
					}
					$inHost = parse_url( $inSrc, PHP_URL_HOST );

					if( $white ) {
						$intruder_found = true;
						foreach( $white as $url ) {
							if( strpos( $inHost, $url ) !== false ) {
								$intruder_found = false;
								break;
							}
						}
						if( ( $insourced->src ) && ( $intruder_found ) ) {
							if( $debug ) {
								$debugOutput .= "\nNot in whitelist, zapped " . $insourced->outertext;
							}
							$insourced->outertext = "";
						}
					} else {
						foreach( $black as $url ) {
							if( strpos( $inHost, $url ) !== false ) {
								if( $debug ) {
									$debugOutput .= "\n" . $url . " is blacklisted, zapping " . $insourced->outertext;
								}
								$insourced->outertext = "";
								break;
							}
						}
					}
				}
				$html = $dom->save();
			}
		}
		$dom->clear();
		unset( $dom );
	}

	if( $debug && $debugOutput ) {
		$html = $html . "\n\n<!-- WP DoNotTrack SuperClean debug output: " . $debugOutput . "\n -->";
	}

	return $html;
}

/* behold the future, you code-peeper you;
function wp_donottrack_csp() {
	$options = wp_donottrack_get_option();
	if( $options['listmode'] === "1" ) {
		$whitelist = wp_donottrack_get_white_list( true );
		$csp = "default-src 'self' 'unsafe-inline' ";

		if( is_array( $whitelist ) ) {
			foreach( $whitelist as $white ) {
				$csp .= " *." . $white;
			}
		}

		// old-style options inline-script for firefox
		$csp .= "; options inline-script;";

		header( "X-Content-Security-Policy: " . $csp );
		header( "Content-Security-Policy: " . $csp );

		// needed for chrome, but safari 5 (latest version on windows) might be broken?!
		header( "X-WebKit-CSP: " . $csp );
	}
}

add_action( 'init', 'wp_donottrack_csp', 10, 0 );
*/

/** is_amp, but I shouldn't have to do this, should I? */
if( !function_exists( "is_amp" ) ) {
	function is_amp() {
		if( ( strpos( $_SERVER['REQUEST_URI'], '?amp' ) !== false ) || ( strpos( $_SERVER['REQUEST_URI'], '/amp/' ) !== false ) ) {
			return true;
		} else {
			return false;
		}
	}
}

if( !is_amp() ) {
	if( $options['level'] !== "0" ) {
		if( !is_admin() ) {
			add_action('wp', 'wp_donottrack_ob_setup', 10, 0);
		} else {
			add_action('wp_print_scripts', 'wp_donottrack_config');
			add_action('init', 'wp_donottrack_init');
			add_action('admin_footer', 'wp_donottrack_footer');
		}
	} else {
		add_action('wp_print_scripts', 'wp_donottrack_config');
		add_action('init', 'wp_donottrack_init');
		add_action('wp_footer', 'wp_donottrack_footer');
	}
}

?>
