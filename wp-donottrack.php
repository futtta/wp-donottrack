<?php
/*
Plugin Name: WP DoNotTrack
Plugin URI: http://blog.futtta.be/wp-donottrack/
Description: Stop plugins and themes from inserting 3rd party tracking code and cookies.
Author: Frank Goossens (futtta)
Version: 0.9.1
Author URI: http://blog.futtta.be/
Text Domain: wp-donottrack
Domain Path: /languages
*/

$debug = false;
$wpdnt_version = "0.9.1";

$lang_dir = basename( dirname( __FILE__ ) ) . '/languages';
load_plugin_textdomain( 'wp-donottrack', null, $lang_dir );

if( is_admin() ) {
	require_once( dirname( __FILE__ ) . '/options.php' );
}

$options = wp_donottrack_get_option();

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
      add_action( 'wp', 'wp_donottrack_ob_setup', 10, 0 );
    } else {
      add_action( 'wp_print_scripts', 'wp_donottrack_config' );
      add_action( 'init', 'wp_donottrack_init' );
      add_action( 'admin_footer', 'wp_donottrack_footer' );
    }
  } else {
    add_action( 'wp_print_scripts', 'wp_donottrack_config' );
    add_action( 'init', 'wp_donottrack_init' );
    add_action( 'wp_footer', 'wp_donottrack_footer' );
  }
}

function wp_donottrack_get_files() {
	global $debug;

	if( $debug ) {
		$files = array(
			"wpdnt" => "donottrack.js",
			"aop"   => "external/js/jquery-aop/aop.js" );
	} else {
		$files = array(
			"wpdnt" => "donottrack-min.js",
			"aop"		=> "external/js/jquery-aop/aop-min.js" );
	}

	$files['htmldom'] = "external/php/simplehtmldom/simple_html_dom.php";

	return $files;
}

function wp_donottrack_get_file_url( $file, $version = false ) {
	global $debug, $wpdnt_version;

	$plugin_url = plugins_url( $file, __FILE__ );

  if( is_ssl() ) {
    $plugin_url = str_replace( "http:", "https:", $plugin_url );
  }

	if( $version ){
		if( $debug ) {
			$plugin_version = $wpdnt_version;
		} else {
			$plugin_version = rand() / 1000;
		}
		$plugin_url = $plugin_url . "?dntver=" . $plugin_version;
	}

	return $plugin_url;
}

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

function wp_donottrack_config( $noEcho = false ) {
	$options = wp_donottrack_get_option();
	$third_party = json_encode( $options['thirdparty'] );

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

	$result = "<script type=\"text/javascript\">var wpdnt_config={scope:\"" . $options['scope'] . "\",listmode:\"" . $options['listmode'] . "\",blacklist:" . $blacklist . ",whitelist:" . $whitelist . ",thirdparty:" . $third_party . "};</script>\n";

	if( $noEcho ) {
		return $result;
	} else {
		echo $result;
	}
}

function wp_donottrack_init() {
	$files = wp_donottrack_get_plugin_files();
	$plugin_url = wp_donottrack_get_file_url( $files['wpdnt'], true );
	wp_enqueue_script( 'wp-donottrack', $plugin_url );
}

function wp_donottrack_footer( $noEcho = false ) {
	$options = wp_donottrack_get_option();
	if( $options['scope'] === "1" ) {
		$prependJS = "if(navigator.doNotTrack===\"yes\"||navigator.msDoNotTrack==\"1\"||navigator.doNotTrack==\"1\"||document.cookie.indexOf(\"dont_track_me=1\")!==-1)||document.cookie.indexOf(\"civicAllowCookies=no\")!==-1){\n";
		$appendJS = "\n}";
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

function wp_donottrack_get_file_content( $file_name ) {
	$file_path = plugin_dir_path( __FILE__ ) . $file_name;
  return file_get_contents( $file_path );
}

function wp_donottrack_ob_filter( $html ){
	global $debug;

	$options = wp_donottrack_get_option();
	$plugin_files = wp_donottrack_get_plugin_files();
	$wpdnt_config = wp_donottrack_config( true );

	if( apply_filters( 'wp_donottrack_inline_js', true ) ) {
		$wpdnt_setup = "<script type=\"text/javascript\">" . wp_donottrack_get_file_content( $plugin_files['aop'] ) . "</script>\n";
    $wpdnt_setup .= "<script type=\"text/javascript\">" . wp_donottrack_get_file_content( $plugin_files['wpdnt'] ) . "</script>";
	} else {
		$wpdnt_setup = "<script type=\"text/javascript\" src=\"" . wp_donottrack_get_file_url( $plugin_files['aop'] ) . "\"></script>\n";
		$wpdnt_setup .= "<script type=\"text/javascript\" src=\"" . wp_donottrack_get_file_url( $plugin_files['wpdnt'] ) . "\"></script>\n";
	}
	$wpdnt_body = wp_donottrack_footer( true );

	$html = str_replace( "</head>", $wpdnt_config . $wpdnt_setup . "\n</head>", $html );
	$html = preg_replace( "/(<body\b[^>]*>)/", "$1\n" . $wpdnt_body, $html );

	if( $options['level'] === "2" ) {
		require_once( dirname( __FILE__ ) . $plugin_files['htmldom'] );
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
						$in_src = "http:" . $insourced->src;
					} else {
						$in_src = $insourced->src;
					}
					$in_host = parse_url( $in_src, PHP_URL_HOST );

					if( $white ) {
						$intruder_found = true;
						foreach( $white as $url ) {
							if( strpos( $in_host, $url ) !== false ) {
								$intruder_found = false;
								break;
							}
						}
						if( ( $insourced->src ) && ( $intruder_found ) ) {
							if( $debug ) {
								$debug_output .= "\nNot in whitelist, zapped " . $insourced->outertext;
							}
							$insourced->outertext = "";
						}
					} else {
						foreach( $black as $url ) {
							if( strpos( $in_host, $url ) !== false ) {
								if( $debug ) {
									$debug_output .= "\n" . $url . " is blacklisted, zapping " . $insourced->outertext;
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

	if( $debug && $debug_output ) {
		$html = $html . "\n\n<!-- WP DoNotTrack SuperClean debug output: " . $debug_output . "\n -->";
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
?>
