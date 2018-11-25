if( is_filter_active() ) {
	// for document.write, has to be sanitized differently from others
	aop.around( { target: document, method: 'write' }, function( invocation ) {
		if( invocation.arguments[0].search( /img|script|iframe/i ) !== -1 ) {
			if( sanitize( invocation.arguments[0] ) === true ) {
				invocation.arguments[0] = invocation.arguments[0].replace( /</g,'<!-- ' ).replace( />/g,' -->' );
			}
		}
		return invocation.proceed();
	} );

	// for dom-methods insertBefore and appendChild on parent of first script and/or head
	scriptParent = document.getElementsByTagName( 'script' )[0].parentNode;

	if( scriptParent.tagName.toLowerCase !== "head" ) {
		head = document.getElementsByTagName( 'head' )[0];

		aop_around( head, "insertBefore" );
		aop_around( head, "appendChild" );
	}

	aop_around( scriptParent, "insertBefore" );
	aop_around( scriptParent, "appendChild" );

	wp_donottrack_3rd_party();
}

function is_filter_active( ) {
	var active = ( wpdnt_config.scope !== "1" || navigator.doNotTrack === "yes" || navigator.msDoNotTrack === "1" || navigator.doNotTrack === "1" || document.cookie.indexOf( "dont_track_me=1" ) !== -1 || document.cookie.indexOf( "civicAllowCookies=no" ) !== -1 );
	return active;
}

function aop_around( myTarget, myMethod ) {
	aop.around( { target: myTarget, method: myMethod }, function( invocation ) {
		if( typeof( invocation.arguments[0].src ) === 'string' &&	( invocation.arguments[0].tagName.toLowerCase() === 'script' || invocation.arguments[0].tagName.toLowerCase() === 'img' || invocation.arguments[0].tagName.toLowerCase() === 'iframe' ) && invocation.arguments[0].src !== 'javascript:void(0)' && sanitize( invocation.arguments[0].src ) === true ) {
			invocation.arguments[0].src = 'javascript:void(0)';
		}
		return invocation.proceed();
	} );
}

function sanitize( source ) {
	var regex = new RegExp( '(?:f|ht)tp(?:s)?\://([^/]+)', 'im' );
	var replace = false;

	try {
		souce = source.match( regex )[1].toString();
	} catch( e ) {
		return replace;
	}

	switch( wpdnt_config.listmode ) {
		case "blacklist":
			// replace = true, if the URL matches any of the blacklist elements
			replace = wpdnt_config.blacklist.some( function( value ) {
				return typeof( value ) === "string" && source.indexOf( value.toLowerCase() ) !== -1;
			} );
			break;
		case "whitelist":
			// replace = false, if the URL matches any of the whitelist elements
			replace = !wpdnt_config.whitelist.some( function( value ) {
				return typeof( value ) === "string" && source.indexOf( value.toLowerCase() ) !== -1;
			} );
			break;
		default:
			replace = false;
	}
	return replace;
}

function wp_donottrack_3rd_party() {
	// AddToAny: https://wordpress.org/plugins/add-to-any/
	if( wpdnt_config.thirdparty['add-to-any'] === true ) {
		var a2a_config = a2a_config || {};
		a2a_config.no_3p = 1;
	}

	// AddThis: https://wordpress.org/plugins/addthis/
	if( wpdnt_config.thirdparty['addthis'] === true ) {
		var addthis_config = addthis_config || {};
		addthis_config.data_use_cookies = false;
	}

	// Google Analytics
	if( wpdnt_config.thirdparty['googleanalytics'] === true ) {

		// Classical Google Analytics: https://developers.google.com/analytics/devguides/collection/gajs/
		var _gaq = _gaq || [];
		_gaq.push( ['_gat._anonymizeIp'] );

		// Universal Analytics: https://developers.google.com/analytics/devguides/collection/analyticsjs/
		if( typeof ga === "function" ) {
			ga( 'set', 'anonymizeIp', true );
		}

		// Global Site Tag: https://developers.google.com/analytics/devguides/collection/gtagjs/
		function wp_donottrack_return_args() { return ( arguments ); }
		window.dataLayer = window.dataLayer || [];
		for( i = 0; i < window.dataLayer.length; i++ ) {
			if( window.dataLayer[i][0] == 'config' || window.dataLayer[i][0] == 'event' ) {
				if( window.dataLayer[i].length == 2 ) {
					window.dataLayer[i] = wp_donottrack_return_args( window.dataLayer[i][0], window.dataLayer[i][1], { 'anonymize_ip': true } );
				} else if( window.dataLayer[i].length > 2 ) {
					window.dataLayer[i][2].anonymize_ip = true;
				}
			}
		}
	}
}
