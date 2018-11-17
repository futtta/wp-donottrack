=== WP DoNotTrack ===
Contributors: futtta, optimizingmatters, dim-0
Tags: privacy, donottrack, tracking, plugin, theme, security, performance, cookie law, cookies, dnt
Requires at least: 3.2
Tested up to: 4.9.8
Stable tag: 0.9.1

WP DoNotTrack stops plugins/ themes from adding tracking code or cookies, protecting visitor privacy and providing performance and security benefits.

== Description ==
WP DoNotTrack stops [plugins and themes from adding 3rd party tracking code and cookies to your blog](http://blog.futtta.be/2011/02/17/why-your-wordpress-blog-needs-donottrack/) to protect both your visitor's privacy, your own security (in the admin-pages) and offering performance gains (limiting requests executed in the browser to render your pages).

This plugin can be useful if you want to:

* make your WordPress blog/ site honour visitors who request not to be tracked, even if the 3rd parties you include do not (conditional privacy)
* stop tracking by 3rd parties for all your visitors (absolute privacy)
* protect your blog from rogue plugins that dynamically add malicious code to your wp-admin pages (security)
* limit the number of external servers that are called from your blog (performance)
* make your blog more compliant with the EU Cookie Law as implemented in a.o. the UK and Holland (with other EU countries to follow) using conditional privacy

WP DoNotTrack uses [jQuery AOP](http://code.google.com/p/jquery-aop/) to catch and inspect elements (images, iframes and scripts) that are about to be added to the DOM and renders these harmless if the black- or whitelist say so. You can block 3rd party tracking for all you visitors, or just for those that have navigator.doNotTrack set to "1" or based on a browser cookie.

The "forced" and "SuperClean" modes use WordPress's output buffering to change the HTML slightly ("forced") or thoroughly ("SuperClean"). SuperClean uses [Simple HTML DOM Parser](http://simplehtmldom.sourceforge.net/) to filter unwanted 3rd party code from the HTML.

Feedback is welcome; see [info in the faq](http://wordpress.org/extend/plugins/wp-donottrack/faq/) for bug reports/ feature requests and feel free to [rate and/or report on compatibility on wordpress.org](http://wordpress.org/extend/plugins/wp-donottrack/).

== Installation ==

Just install form your Wordpress "Plugins|Add New" screen and all will be well. Manual installation is very straightforward as well:

1. Upload the zip-file and unzip it in the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure on the admin-page

== Frequently Asked Questions ==

= How does WP DoNotTrack help me comply with the EU Cookie Law? =
Most solutions for Cookie Law compliance focus on alerting the user of the fact that cookies are needed and on allowing or disallowing 1st party cookies (i.e. from your own site) from being set. WP DoNotTrack takes a different (and complementary) approach, with the ability to act on user preference as configured in the browser or an opt-out cookie being present (see below) to conditionally stop javascript-initiated 3rd party tracking.

= Which browsers support conditional tracking based on browser setting? =
Both Internet Explorer 9 and Firefox 9 and up offer full-fledged support for "DoNotTrack" (both in HTTP header & javascript). Apple's [Safari has this option hidden in the "Develop" menu](http://osxdaily.com/2011/04/16/safari-in-mac-os-x-lion-adds-do-not-track-support-heres-how-to-enable-it/) and [Opera 12 (in beta now) also offers DNT-support](http://my.opera.com/chooseopera/blog/2012/04/26/opera-12-beta-available-now). In fact, only Google Chrome does not have this feature at all (Google, after all, sells personalized advertising, so this touches their core business), although [support is supposed to land before the end of the year](http://news.cnet.com/8301-30685_3-57383362-264/chrome-to-support-do-not-track-privacy-feature/).

= How can I make my users choose not to be tracked and/or integrate with Civic Cookie Control? =
Starting with version 0.8.0, WP DoNotTrack supports conditional tracking based on cookies. There currently are two approaches to do this:

* display a message asking your user to allow (or OK) tracking. If the user does not want to allow this, simply set a cookie (in JavaScript or PHP) with name "dont_track_me" and value "1" and WP DoNotTrack will pick up on that much in the same way it works with the browsers DNT-flag.
* alternatively, WP DoNotTrack can also rely on the [Civic Cookie Control](http://www.civicuk.com/cookie-law/index) cookie as implemented by the [Cookie Control WordPress plugin](http://wordpress.org/extend/plugins/cookie-control/).

= Should I run WP DoNotTrack in black- or whitelist mode? =
Although whitelist is more robust and future-proof, it might break things in both frontend and wp-admin. If you don't want to test extensively and you're not sure to begin with, start out going blacklist first.

= How should I create my blacklist? =
* Check what requests the browser makes when not logged in (look at a couple of different pages) and add the hostname of URL's you deem unfit to the blacklist
* Disable any caching- or javascript-aggregating plugin while testing/ making changes
* Try an external service like [webpagetest.org](http://www.webpagetest.org/) to make sure you're seeing a real anonymous user's view

= How should I create a whitelist? =
* Whitelisting is somewhat more impacting, as it'll stop anything you don't explicitely allow. Be sure to study requests for both visitors and logged in users in wp-admin. In general you'd want to allow known hosts such as e.g. google-analytics.com. 
* Make sure to test extensively after enabling the whitelist and tweak until everything works to your satisfaction.
* Disable any caching- or javascript-aggregating plugin while testing/ making changes
* Try an external service like [webpagetest.org](http://www.webpagetest.org/) to make sure you're seeing a real anonymous user's view

= Can WP DoNotTrack stop all 3rd party tracking? =
Yes, but No. When running in Normal or Forced mode, WP DoNotTrack stops most javascript-initiated 3rd party code inclusion. When running in SuperClean Mode, WP DoNotTrack will also filter the HTML. There are, however, circumstances in which tracking is unavoidable or invisible to WP DoNotTrack: 

* some widgets (in the broad sense) only work with tracking enabled (e.g. Facebook Like button or Google Analytics tracking)
* some trackers use Flash, which is simply out of reach of what WP DoNotTrack currently can do
* when using [AMP](https://www.ampproject.org/), you're not supposed to have any other javascript code embedded. Therefore, once AMP is enabled, this plugin won't add its own code to the website (which renders it unfunctional). At least, AMP is supposed to anonymize the IPs by default.

= Why would I need "forced" mode? =
Javascript (and CSS/ HTML) optimizing plugins such as W3 Total Cache and Autoptimize change the way JavaScript is loaded (by combining, minimizing and loading at the end of the page), which can break or limit WP DoNotTrack's functionality.

= Why use "SuperClean" mode? = 
By default DoNotTrack is used to stop javascript trackers, which typically only add the tracking stuff to your pages when executed in the browser. Some widgets/ plugins/ themes might also add tracking to the HTML (as a hidden image, iframe or with javascript). "SuperClean" mode checks the HTML before it is sent to the browser to stop that kind of tracking. Be warned that SuperClean is pretty invasive functionality, so do test extensively!

= Why can't I select "SuperClean" mode? =
Superclean is not yet available if you're only enabling WP DoNotTrack for people who configured their browsers to do so (conditional filtering based on the donottrack-header) or who opted out with a cookie. This might become available in the future, but there's caching plugins to take into account when combining conditional filtering with SuperClean.

= Any bugs/ issues should I know about? =
* After installing or when making changes to the WP DoNotTrack configuration, you might have to clear the caches of caching plugins you might be using (e.g. WP Super Cache or W3 Total Cache). Consider it "best practice" to disable caching & javascript-aggregating plugins while testing new black- or whitelists
* CloudFlare seems to interfere with the way the plugin gets loaded and the way it functions. You can solve the problem by disabling "Rocket Loader" and/or "Auto Minify" alltogether.
* Not a bug, but still an known issue; as WP DoNotTrack is also active in the wp-admin-pages, it will -when in whitelist mode- impact plugins that (for whatever reason) pull in javascript from elsewhere in their option-pages.

= I found a bug/ I would like a feature to be added! =
Just tell me, I like the feedback and in general I'll reply within a couple of hours. Use the [Contact-page on my blog](http://blog.futtta.be/contact/), [leave a comment in a post about DoNotTrack](http://blog.futtta.be/tag/donottrack/) or [post about it on the wordpress.org plugin forum](http://wordpress.org/tags/wp-donottrack?forum_id=10#postform)

= How you can help =
* Explain people (in real life and/or on your blog, on Facebook, on Twitter ...) that you disabled 3rd party tracking on your blog.
* Tell me about bugs you think you've found and if you can't find any, [confirm it works with your version of WP on wordpress.org](http://wordpress.org/extend/plugins/wp-donottrack/)
* Ask me for a feature you would like to see added (cfr. contact info above)
* [Rate my plugin on wordpress.org](http://wordpress.org/extend/plugins/wp-donottrack/).

== Changelog ==

= 0.9.1 =
* consolidated Google Analytics options
* added IP address anonymization for Google Global Site Tag
* added support for being run as mu-plugin

= 0.9.0 =
* rework of options.php to meet the regular WP settings API
* explicit checkboxes for (local) thirdparty plugins/libraries are included (analytics.js added)
* split the plugin's JavaScript code from the jQuery AOP source code, so that they can be maintained separately
* rework of wp-donottrack.php (spelling, structure, avoiding global variables, etc.)
* added IP address anonymization for Google Universal Analytics
* updated external sources ([jQuery AOP](https://github.com/gonzalocasas/jquery-aop) and [PHP Simple HTML DOM Parser](https://github.com/yardenac/simplehtmldom))

= 0.8.8 =
* even better bailing-out if amp (thanks to Patrick Sletvold for [reporting on GitHub](https://github.com/futtta/wp-donottrack/issues/1))

= 0.8.7 =
* no wp donottrack when in amp (as one should not do JS in amp-pages)

= 0.8.6 =
* improvement: better support for WP installs that do not have /wp-content/
* correction in changelog for 0.8.5: JS will also be inlined when in "forced" mode

= 0.8.5 =
* improvement: when in superclean or forced mode, the js by default will be inlined (you can override by doing an add_filter for wp_donottrack_inline_js, returning false)
* confirm working with WordPress 4.0

= 0.8.4 =
* bugfix: fix notice as reported by [Josef Seidl](http://www.blog-it-solutions.de/)
* confirm working with WordPress 3.9

= 0.8.3 =
* bugfix: wp donottrack did not act on trackers in admin-screens (there's no output buffering in /wp-admin/*, so revert to "normal" mode type of enforcement in that case). hat tip to [iltrev from minimoblog.it](http://minimoblog.it/) for reporting!
* bugfix in options.php to [stop php warnings from being generated](http://wordpress.org/support/topic/plugin-wp-donottrackmini-bugsfix)
* cleaned up code to avoid having to juggle with json and arrays that much.

= 0.8.2 =
* fix: the HTTPS-check in versions prior to 0.8.2 did not function correctly in Microsoft's IIS, as reported by Geoff Beaumont of [integrious.co.uk](http://www.integrious.co.uk/)

= 0.8.1 =
* fix: 0.8.0 broke Google AdSense, reported by [Perun](http://perun.net/) due to a regex failing horribly.

= 0.8.0 =
* new: conditional filtering based on presence of the "[Civic Cookie Control](http://www.civicuk.com/cookie-law/index)" cookie (thus providing integration with the [Cookie Control WordPress plugin](http://wordpress.org/extend/plugins/cookie-control/))
* new: can alternatively also act on a cookie with name "dont_track_me" and value "1"
* new: "forced" mode now default
* bugfix: re-introduced the bugfix for whitelist mode that was rollbacked in 0.7.2
* bugfix: [conflict with prototype](http://zurahn.wordpress.com/2009/01/11/prototypejs-breaks-javascript-foreach-loops/) which caused wysiwyg editing of [Wysija newsletter](http://wordpress.org/extend/plugins/wysija-newsletters/) templates to break

= 0.7.2 =
* quick rollback to previous version of donottrack(-min).js, due to bugs in 0.7.1-version, hope to sort problems out and push 0.7.3 one of the following days. sorry for the trouble!

= 0.7.1 =
* misc. bugfixes for SuperClean mode (based on initial feedback from [Paul Martinus](https://consummatumest.com/))
* bugfix for whitelist mode (where an URL which is not on the whitelist is still allowed due to the querystring containing a whitelisted URL)

= 0.7.0 =
* non-default "SuperClean" mode to parse HTML to stop tracking from within, do [provide me with feedback](http://blog.futtta.be/contact/) if you're using this!
* improved support for conditional tracking (to cover differences in browser implementation of navigator.doNotTrack)
* tested with wordpress 3.4 (beta)

= 0.6.1 =
* fix for mixed HTTP/HTTPS resources in HTTPS admin-page (as reported by [Paul Martinus](https://consummatumest.com/))

= 0.6.0 =
* non-default "force" mode to solve incompatibilities with html/js/css optimizers such as Autoptimize, W3 Total Cache (which rearrange javascript)

= 0.5.2 =
* add document.body insertBefore to the list of methods being watched
* add lockerz.com to default blacklist (the popular addtoany [now integrates with this social network, with no way to disable it](http://blog.futtta.be/?p=7092))

= 0.5.1 =
* also look for iframe's being added to the DOM by javascript
* bugfix: make WP DoNotTrack behave when in https
* updated readme.txt ([FAQ: "Can WP DoNotTrack stop all tracking?"](http://wordpress.org/extend/plugins/wp-donottrack/faq/))

= 0.5.0 =
* choose between white- and blacklist (default)
* define the content of you white- or blacklist (default: quantserve.com & media6degrees.com)
* have WP DoNotTrack stop tracking only when browser sends DNT-flag or always (default)

= 0.1.0 =
* Initial version
