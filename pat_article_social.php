<?php
/*
 * pat_article_social (formerly pat_article_tweet) Textpattern CMS plugin
 * @author:  © Patrick LEFEVRE, all rights reserved. <patrick[dot]lefevre[at]gmail[dot]com>
 * @link:    http://pat-article-social.cara-tm.com
 * @type:    Public
 * @prefs:   no
 * @order:   5
 * @version: 0.4.9
 * @license: GPLv2
*/

// TO DO: MORE OPTIONS FOR FB OPEN GRAPH. BACK-OFFICE CHART VISUALISATION OF SHARES BY INDIVIDUAL ARTICLES.

if (txpinterface == 'admin')
{
	register_callback('_pat_article_social_prefs', 'prefs', '', 1);
	register_callback('_pat_article_social_cleanup', 'plugin_lifecycle.pat_article_social', 'deleted');
}

global $refs, $twcards;
// List of social networks that support share count.
$refs = array('facebook', 'twitter', 'google', 'pinterest', 'linkedin', 'buffer', 'reddit', 'dribbble');
// List of Twitter Card types.
$twcards = array('summary', 'summary_large_image', 'photo', 'gallery', 'product');

/**
 * Generate meta tag for social websites
 *
 * @param  array  Tag attributes
 * @return string HTML meta or link tags
 */
function pat_article_social_meta($atts)
{

	global $prefs, $pretext, $thisarticle, $twcards;

	extract(lAtts(array(
		'type'		=> array(),
		'card' 		=> 'summary',
		'image'		=> NULL,
		'user'		=> NULL,
		'creator'	=> NULL,
		'label1' 	=> NULL,
		'data1' 	=> NULL,
		'label2' 	=> NULL,
		'data2' 	=> NULL,
		'fb_type' 	=> 'website',
		'fb_api' 	=> NULL,
		'fb_admins' 	=> NULL,
		'locale' 	=> $prefs['language'],
		'fb_author' 	=> NULL,
		'fb_publisher'	=> NULL,
		'g_author' 	=> NULL,
		'g_publisher'	=> NULL,
		'title' 	=> $prefs['sitename'],
		'description' 	=> page_title(array()),
		'lenght' 	=> 200,
	), $atts));


	if ( $type && !gps('txpreview') ) {

		// Create an array of social services from list 
		$type = explode(',', $type);
		// Format lang code
		phpversion() >= '5.3.0' ? $locale = preg_replace_callback( '(^([a-z]{2})(.*)?([a-z]{2}))i', function($m){return "$m[1]_".strtoupper($m[3]);}, $locale ) : '';
		// Get URI
		$current = _pat_article_social_get_uri();
		// Check image
		$image ? $image : $image = _pat_article_social_image();
		// Sanitize
		$description = preg_replace('/(([&-a-z0-9;])?(#[a-z0-9;])?)[a-z0-9]+;/i', '', strip_tags($description) );
		// Social Networks often limit description to 200 characters
		$description = _pat_article_social_trim($description, $lenght);

		foreach ($type as $service) {

			switch( strtolower($service) ) {

			case 'twitter':
				if( false === _pat_article_social_occurs($card, $twcards) )
					return trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'card')), E_USER_WARNING);

				$img = $thisarticle['article_image'];
				$list = explode(',', $img);
				count($list) > 1 ? $card = 'gallery' : '';
				$tags = '<meta name="twitter:card" content="'.$card.'">'.n;
				$tags .= _pat_article_social_validate_user($user, 'site');
				$tags .= _pat_article_social_validate_user($creator, 'creator');
				if (count($list) > 0 && $card == 'gallery') {
					$i = 0;
					foreach($list as $pic) {
						$tags .= '<meta name="twitter:image'.$i.'" content="'._pat_article_social_image($pic).'">'.n;
						++$i;
					}
				} else {
					$tags .= ($image ? '<meta property="twitter:image'.($card == 'summary_large_image' ? ':src' : '').'" content="'._pat_article_social_image($image).'">'.n : '');
				}
				$tags .= '<meta property="twitter:image'.($card == 'summary_large_image' ? ':src' : '').'" content="'._pat_article_social_image($image).'">'.n;
				$tags .= ($label1 ? '<meta name="twitter:label1" content="'.$label1.'">'.n : '');
				$tags .= ($data1 ? '<meta name="twitter:data1" content="'.$data1.'">'.n : '');
				$tags .= ($label2 ? '<meta name="twitter:label2" content="'.$label2.'">'.n : '');
				$tags .= ($data2 ? '<meta name="twitter:data2" content="'.$data2.'">'.n : '');
				$tags .= <<<EOF
<meta property="twitter:url" content="{$current()}">
<meta property="twitter:title" content="{$title}">
<meta name="twitter:description" content="$description">
EOF;
			break;


			case 'facebook':
	$tags = <<<EOF
<meta property="og:locale" content="$locale">
<meta property="og:site_name" content="{$prefs['sitename']}">
<meta property="og:title" content="{$title}">
<meta property="og:description" content="$description">
<meta property="og:url" content="{$current()}">

EOF;
	$tags .= ($image ? '<meta property="og:image" content="'.$image.'">'.n : '');
	$tags .= ($pretext['id'] ? '<meta property="og:type" content="article">' : '<meta property="og:type" content="website">').n;
	$tags .= ($fb_api ? '<meta property="fb:app_id" content="'.$fb_api.'">'.n : '');
	$tags .= ($fb_admins ? '<meta property="fb:admins" content="'.$fb_admins.'">'.n : '');
	$tags .= ($fb_author ? '<meta property="article:author" content="https://www.facebook.com/'.$fb_author.'">'.n : '');
	$tags .= ($fb_publisher ? '<meta property="article:publisher" content="https://www.facebook.com/'.$fb_publisher.'">' : ''); 
			break;


			case 'google':
	$tags = <<<EOF
<meta itemprop="name" content="{$prefs['sitename']}">
<meta itemprop="title" content="{$title}">
<meta itemprop="description" content="$description">
<meta itemprop="url" content="{$current()}">

EOF;
	
	$tags .= ($image ? '<meta itemprop="image" content="'.$image.'">'.n : '');
	$tags .= ($g_author ? '<link rel="author" href="https://plus.google.com/'.$g_author.'">'.n : '');
	$tags .= ($g_publisher ? '<link rel="publisher" href="https://plus.google.com/'.$g_publisher.'">'.n : '');
			break;

			}
		}

	return $tags;
	}

	return '';
}


/**
 * Validate Twitter accounts
 *
 * @param  $entry  $att
 * @return string  string  User account  Meta content attribute
 */
function _pat_article_social_validate_user($entry, $attribute = NULL)
{

	if (!$entry)
		$out = ' ';
	// Check if account is well formated
	if ( preg_match("/\@[a-z0-9_]+/i", $entry) )
		$out = ($attribute ? '<meta name="twitter:'.$attribute.'" content="'.$entry.'">'.n : $entry);

	return $out ? $out : trigger_error( gTxt('invalid_attribute_value', array('{name}' => 'user or creator')), E_USER_WARNING );
}


/**
 * Display article image
 *
 * @param int $pic  image ID
 * @return string URI  Full article image URI
 */
function _pat_article_social_image($pic = NULL)
{

	global $thisarticle;

	// Individual image or not?
	if (false == $pic)
		$img = $thisarticle['article_image'];
	else
		$img = $pic;

	if (intval($img)) {

		if ( $rs = safe_row('*', 'txp_image', 'id = ' . intval($img)) ) {
			$img = imagesrcurl($rs['id'], $rs['ext']);
		} else {
			$img = null;
		}

	}

	return $img;
}


/**
 * Display current URL
 *
 * @param
 * @return String  URI
 */
function _pat_article_social_get_uri()
{
	$uri= @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
	$uri .= $_SERVER["REQUEST_URI"];

	return $uri;
}


/** Trims text to a space then adds ellipses
  * @param string $input text to trim
  * @param int $length in characters to trim to
  * @param bool $strip_html strip html tags if present
  * @return string
  */
function _pat_article_social_trim($input, $length, $strip_html = true)
{
	// Strip tags, if desired
	if ($strip_html)
		$input = strip_tags($input);
  
	// No need to trim, already shorter than trim length
	if ( strlen($input) <= $length )
		return $input;
  
	// Find last space within length
	$space = strrpos( substr($input, 0, $length), ' ' );
	$shrink = substr($input, 0, $space).'...';

	return $shrink;
}


/**
 * Generate links for social websites
 *
 * @param  array   Tag attributes
 * @return String  Link with encoded article body in arguments
 */
function pat_article_social($atts)
{

	global $thisarticle, $dribbble_data, $real, $shot, $user, $token;

	extract(lAtts(array(
		'site'		 => 'permalink',
		'tooltip' 	 => NULL,
		'input_tooltip'  => NULL,
		'title'		 => NULL,
		'via'		 => NULL,
		'dribbble_data'  => 'followers',
		'shot' 		 => NULL,
		'page' 		 => NULL,
		'instagram' 	 => NULL,
		'user' 		 => NULL,
		'token' 	 => NULL,
		'content' 	 => 'excerpt',
		'image' 	 => NULL,
		'class'		 => NULL,
		'icon' 		 => false,
		'width' 	 => '16',
		'height' 	 => '16',
		'count' 	 => false,
		'real' 		 => false,
		'zero' 		 => false,
		'unit' 		 => 'k',
		'delay' 	 => 3,
		'fallback' 	 => true,
	), $atts));

	if ( $site && !gps('txpreview') ) {

		// Check article's content
		if( in_array($content, array('title', 'excerpt', 'body')) )
			$extract = $thisarticle[$content];
		else
			trigger_error( gTxt('invalid_attribute_value', array('{name}' => 'content')), E_USER_WARNING );

		$url = permlink( array() );
		// Sanitize
		$text = $thisarticle['title'].'. '.preg_replace('/(([&-a-z0-9;])?(#[a-z0-9;])?)[a-z0-9]+;/i', '', strip_tags($extract) );
		// Limit content lenght
		$minus = strlen($via)+7;
		// Twitter shorten urls: http://bit.ly/ILMn3F
		$words = ($via ? 'via '._pat_article_social_validate_user($via).': ' : '').urlencode( substr($text, 0, 115-$minus) ).'...';

		switch( strtolower($site) ) {

			case 'twitter':
				$link = '<a title="'.$tooltip.'" href="https://twitter.com/share?url='.$url.'&amp;text='.$words.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="twitter-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 16 16" xml:space="preserve"><path d="M16 3c-0.6 0.3-1.2 0.4-1.9 0.5 0.7-0.4 1.2-1 1.4-1.8-0.6 0.4-1.3 0.7-2.1 0.8C12.9 1.9 12 1.5 11.1 1.5c-1.8 0-3.3 1.5-3.3 3.3 0 0.3 0 0.5 0.1 0.8C5.2 5.4 2.7 4.1 1.1 2.1 0.8 2.6 0.7 3.2 0.7 3.8c0 1.1 0.6 2.1 1.5 2.7C1.6 6.5 1.1 6.3 0.6 6.1v0c0 1.6 1.1 2.9 2.6 3.2C3 9.4 2.7 9.5 2.4 9.5c-0.2 0-0.4 0-0.6-0.1 0.4 1.3 1.6 2.3 3.1 2.3-1.1 0.9-2.5 1.4-4.1 1.4-0.3 0-0.5 0-0.8 0C1.5 14 3.2 14.5 5 14.5c6 0 9.3-5 9.3-9.3L14.4 4.7C15 4.3 15.6 3.7 16 3z"/></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode( permlink(array()) ), '_pat_article_social_get_twitter', $delay, $zero ) : '').($fallback ? <strong>T</strong>' : '').'</a>';
			break;


			case 'facebook':
				$link = '<a href="http://www.facebook.com/sharer.php?u='.$url.'&amp;t='.urlencode( title(array()) ).'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="facebook-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 11.861 23.303"><path d="M11.861,8.323H7.594V6.243c0,0-0.239-1.978,1.144-1.978c1.563,0,2.811,0,2.811,0V0H6.763c0,0-4.005-0.017-4.005,4.005c0,0.864-0.004,2.437-0.01,4.318H0v3.434h2.741c-0.016,5.46-0.035,11.545-0.035,11.545h4.888V11.757h3.226L11.861,8.323z"/></svg>' : '').'<b>'.$title.'</b>'.($count && $zero ? '  <span>'._pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode( permlink(array()) ), '_pat_article_social_get_facebook', $delay, $zero ).'</span>' : '').($fallback ? '<strong>F</strong>' : '').'</a>';
			break;


			case 'google':
				$link = '<a href="https://plus.google.com/share?url='.$url.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="gplus-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 89.6 88"><path d="M4.8 21.9c0 7.5 2.5 12.9 7.4 16 4 2.6 8.7 3 11.1 3 0.6 0 1.1 0 1.4 0 0 0-0.8 5 2.9 10l-0.2 0C21 50.9 0 52.2 0 69.5c0 17.6 19.3 18.5 23.2 18.5 0.3 0 0.5 0 0.5 0C23.7 88 24 88 24.5 88c2.5 0 8.9-0.3 14.9-3.2C47.1 81 51 74.5 51 65.4c0-8.8-6-14.1-10.3-17.9 -2.7-2.3-5-4.4-5-6.3 0-2 1.7-3.5 3.8-5.4 3.4-3.1 6.7-7.5 6.7-15.8 0-7.3-0.9-12.2-6.8-15.3 0.6-0.3 2.8-0.5 3.8-0.7C46.4 3.6 51 3.1 51 0.5V0H28C27.8 0 4.8 0.9 4.8 21.9zM41.9 67c0.4 7-5.6 12.2-14.6 12.9 -9.2 0.7-16.7-3.5-17.2-10.5 -0.2-3.4 1.3-6.7 4.2-9.3 2.9-2.7 7-4.3 11.4-4.6 0.5 0 1-0.1 1.5-0.1C35.7 55.4 41.5 60.4 41.9 67zM35.9 17.1c2.3 7.9-1.1 16.2-6.6 17.8 -0.6 0.2-1.3 0.3-1.9 0.3 -5 0-9.9-5-11.7-12 -1-3.9-0.9-7.3 0.2-10.6 1.2-3.2 3.2-5.4 5.8-6.1 0.6-0.2 1.3-0.3 1.9-0.3C29.6 6.2 33.5 8.7 35.9 17.1zM74.6 34.4v-15h-9.5v15h-15v9.5h15v15h9.5v-15h15v-9.5H74.6z"/></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode( permlink(array()) ), '_pat_article_social_get_google', $delay, $zero ) : '').($fallback ? '<strong>G+</strong>' : '').'</a>';
			break;


			case 'pinterest':
				$link = '<a href="http://pinterest.com/pin/create/button/?url='.$url.'&amp;description='.$words.'&amp;media='._pat_article_social_image($image).'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="pinterest-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 16.53 21.25"><path d="M6.796,14.03c-0.558,2.926-1.24,5.73-3.258,7.195c-0.623-4.422,0.915-7.743,1.629-11.269C3.949,7.907,5.313,3.781,7.882,4.798c3.159,1.25-2.737,7.622,1.222,8.418c4.135,0.83,5.82-7.173,3.258-9.776c-3.703-3.758-10.78-0.085-9.91,5.295c0.211,1.315,1.57,1.714,0.543,3.531C0.624,11.739-0.083,9.87,0.008,7.377c0.146-4.079,3.666-6.936,7.195-7.331c4.463-0.5,8.652,1.639,9.23,5.837c0.652,4.739-2.014,9.873-6.787,9.504C8.353,15.287,7.81,14.646,6.796,14.03z"/></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_pinterest', $delay, $zero ) : '').($fallback ? '<strong>P</strong>' : '').'</a>';
			break;


			case 'tumblr':
				$link = '<a href="http://www.tumblr.com/share/quote?quote='.$words.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="tumblr-icon" x="0px" y="0px" width="'.$width.'" height="'.$height.'" viewBox="0 0 56 92"><path d="M56,86.982c-3.885,1.852-7.402,3.152-10.547,3.898C42.303,91.625,38.895,92,35.236,92c-4.154,0-7.828-0.535-11.023-1.594c-3.191-1.066-5.912-2.58-8.172-4.54c-2.256-1.969-3.82-4.061-4.689-6.274c-0.871-2.215-1.305-5.425-1.305-9.629v-32.27H0V24.678c3.568-1.176,6.631-2.855,9.176-5.051c2.547-2.189,4.59-4.826,6.131-7.9c1.541-3.07,2.6-6.979,3.182-11.727h12.926v23.256h21.574v14.438H31.414v23.594c0,5.333,0.279,8.756,0.842,10.273c0.555,1.512,1.596,2.722,3.109,3.626c2.018,1.22,4.314,1.83,6.902,1.83c4.604,0,9.18-1.517,13.732-4.544V86.982z"/></svg>' : '').'<b>'.$title.'</b>'.($fallback ? '<strong>T</strong>' : '').'</a>';
			break;


			case 'pocket':
				$link = '<a href="http://getpocket.com/edit?url='.$url.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="pocket-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 96 96"><path d="M8.4 10.5c2.7-1 5.7-0.9 8.6-1 21 0.1 42 0.1 63 0 3.7 0 7.8 0.2 10.5 3 3.1 3 3.1 7.5 3.1 11.5 -0.1 8.6 0.2 17.2-0.2 25.8 -0.6 11.4-6 22.5-14.7 29.9 -16.2 13.8-42.1 14.7-59 1.7C9.9 74 4.1 62.2 2.9 50.2 2 40.8 2.6 31.4 2.4 22 2.2 17.4 3.8 12.2 8.4 10.5zM21.4 40.6c3.1 5.4 8.3 9.1 12.4 13.7 4.1 3.9 7.5 8.9 12.7 11.4 4.3 1.5 7.6-2.4 10.2-5.1 4.9-5.4 10.2-10.4 15.3-15.7 2.2-2.3 4.7-5.5 3.2-8.8 -1.2-3.9-6.6-5.2-9.6-2.5 -6.4 4.9-10.9 11.9-17.4 16.7 -6.6-5.1-11.2-12.5-18.1-17.2C25.4 29.9 18.8 35.5 21.4 40.6z"/><path class="inner" d="M21.4 40.6c-2.6-5 4-10.6 8.6-7.6 6.9 4.7 11.5 12.1 18.1 17.2 6.5-4.8 11.1-11.7 17.4-16.7 3.1-2.7 8.4-1.4 9.6 2.5 1.5 3.3-1 6.5-3.2 8.8 -5.1 5.2-10.4 10.3-15.3 15.7 -2.6 2.7-5.9 6.6-10.2 5.1 -5.2-2.5-8.5-7.5-12.7-11.4C29.7 49.7 24.4 46 21.4 40.6z"/></svg>' : '').'<b>'.$title.'</b>'.($fallback ? '<strong>P</strong>' : '').'</a>';
			break;


			case 'instapaper':
				$link = '<a href="http://www.instapaper.com/hello2?url='.$url.'&amp;title='.urlencode( title(array()) ).'&amp;description='.$words.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="instapaper-icon" x="0px" y="0px" width="'.$width.'" height="'.$height.'" viewBox="0 0 199.22 359.29"><path d="M133.221,52.92c0.01,84.05,0,168.1,0,252.15c-0.07,3.949,0.85,8.08,3.59,11.07c5,5.729,12.42,8.56,19.6,10.42c12.21,3.43,24.939,4.43,37.57,4.85c2.6-0.11,5.299,1.84,5.289,4.59c0.26,6,0.19,12.02,0.051,18.01c0.16,3.35-3.23,5.34-6.25,4.99c-61.021,0.029-122.05-0.05-183.07,0.029c-2.8-0.17-6.21,0.561-8.37-1.68c-1.62-1.67-1.22-4.199-1.35-6.3c0.11-4.97-0.14-9.94,0.13-14.899c-0.02-2.92,2.85-4.961,5.61-4.75c14.34-0.16,28.84-1.471,42.54-5.94c5.9-2.069,11.9-5.03,15.59-10.28c3.09-4.43,2.62-10.07,2.64-15.189c-0.03-82.34-0.01-164.69-0.01-247.04c-0.05-1.81-0.16-3.8-1.44-5.21c-3.39-3.89-8.16-6.14-12.79-8.18c-14.19-5.84-29.28-8.99-44.36-11.55c-2.83-0.6-6.98-0.85-7.64-4.37c-0.56-5.2-0.09-10.46-0.27-15.69c-0.05-2.78-0.11-6.48,3-7.69C6.45-0.51,9.76-0.13,13-0.21C70.34-0.18,127.67-0.2,185.01-0.2c3.86,0.14,7.84-0.45,11.631,0.52c2.969,1.29,2.799,4.97,2.819,7.68c-0.181,5.29,0.34,10.62-0.33,15.89c-0.729,3.18-4.45,3.56-7.11,3.99c-13.02,1.83-26.02,4.28-38.47,8.6c-5.22,1.81-10.399,3.94-15.01,7.02C135.41,45.59,132.82,48.96,133.221,52.92z"/></svg>' : '').'<b>'.$title.'</b>'.($fallback ? '<strong>I</strong>' : '').'</a>';
			break;


			case 'linkedin':
				$link = '<a href="http://www.linkedin.com/shareArticle?mini=true&amp;url='.$url.'&amp;title='.urlencode( title(array()) ).'&amp;source='.urlencode( site_slogan(array()) ).'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="linkedin-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 90 88.7"><path d="M20.4 9.6c0 5.3-3.9 9.6-10.4 9.6C3.9 19.2 0 14.9 0 9.6 0 4.2 4.1 0 10.3 0 16.5 0 20.3 4.2 20.4 9.6zM0.5 88.7V26.8h19.2v61.9H0.5zM31.3 46.6c0-7.7-0.3-14.2-0.5-19.7h16.7l0.9 8.6h0.4c2.5-4.1 8.7-10 19.1-10C80.5 25.4 90 33.9 90 52.2v36.6H70.8V54.4c0-8-2.8-13.4-9.7-13.4 -5.3 0-8.5 3.7-9.9 7.2 -0.5 1.3-0.6 3-0.6 4.8v35.7H31.3V46.6z"/></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode( permlink(array()) ), '_pat_article_social_get_linkedin', $delay, $zero ) : '').($fallback ? '<strong>L</strong>' : '').'</a>';
			break;


			case 'buffer':
				$link = '<a href="http://bufferapp.com/add?id=fd854fd5d145df9c&amp;url='.$url.'&amp;text='.urlencode( site_slogan(array()) ).'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="buffer-icon" x="0px" y="0px" width="'.$width.'" height="'.$height.'" viewBox="0 0 979 1000" preserveAspectRatio="xMinYMin meet"><path d="M0 762q11-15 31.5-25.5t49-20 40.5-15.5q19 0 33.5 4.5t33.5 15 25 12.5q47 21 260 119 19 4 35.5 0t39.5-17.5 24-14.5q20-9 76.5-34.5t87.5-39.5q4-2 41.5-21t60.5-24q13-2 27.5 1t23.5 7.5 23 13 18 10.5 15.5 6 18.5 8 11 11q3 4 4 14-10 13-31 24t-51 22-40 16q-43 20-128.5 61.5t-128.5 61.5q-7 3-21 11.5t-23.5 13-25.5 11-27.5 7-29.5-1.5l-264-123q-6-3-32-14t-51.5-22-53.5-24-46.5-23.5-21.5-16.5q-4-4-4-13zm0-268q11-15 31.5-25t50-20 41.5-15q19 0 34 4.5t34.5 15 25.5 13.5q42 19 126.5 58t127.5 59q19 5 37 0.5t39-17 25-14.5q68-32 160-72 11-5 31.5-16.5t38.5-19.5 36-11q16-3 31.5 1t37.5 17 23 13q5 3 15.5 6.5t18 8 11.5 10.5q3 5 4 14-10 14-31.5 25.5t-52.5 22.5-41 16q-48 23-135.5 65t-122.5 59q-7 3-26 14t-29 15-32.5 10-35.5 0q-214-101-260-122-6-3-44-19t-69.5-30-61.5-29.5-34-22.5q-4-4-4-14zm0-267q10-15 31.5-26.5t52.5-22.5 41-16l348-162q30 0 53.5 7t56.5 26 40 22q39 18 117 54.5t117 54.5q4 2 36.5 15t54.5 24 27 20q3 4 4 13-9 13-26 22.5t-43.5 19-34.5 13.5q-47 22-140 66.5t-139 66.5q-6 3-20 11t-23 12.5-25 10.5-27 6-28-1q-245-114-256-119-4-2-63-27.5t-102-46.5-48-30q-4-4-4-13z"/></svg>' : '').'<b>'.$title.'</b>'.($count  ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_linkedin', $delay, $zero ) : '').($fallback ? '<strong>B</strong>' : '').'</a>';
			break;


			case 'reddit':
				$link = '<a href="http://www.reddit.com/submit?url='.$url.'&amp;title='.$text.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="reddit-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 512 512" xml:space="preserve"><path class="inner" d="M480.5 251c0-27.7-22.2-50.2-49.5-50.2-13 0-24.7 5-33.6 13.3-32.4-22.8-76.1-37.8-124.9-40.6l21.9-73.2 67.1 13.5c2.3 22.7 21.2 40.4 44.3 40.4 0.1 0 0.1 0 0.2 0 0.1 0 0.1 0 0.2 0 24.6 0 44.5-20.2 44.5-45.1S430.7 64 406.1 64c-0.1 0-0.1 0-0.2 0 0 0-0.1 0-0.1 0-17.2 0-32 9.8-39.5 24.3l-89.7-18-30.8 103-2.5 0.1c-50.3 2.2-95.5 17.4-128.7 40.7-8.8-8.3-20.6-13.3-33.6-13.3-27.3 0-49.5 22.5-49.5 50.2 0 19.6 11 36.5 27.1 44.8-0.8 4.9-1.2 9.8-1.2 14.8C57.5 386.4 146.4 448 256 448s198.5-61.6 198.5-137.5c0-5-0.4-9.9-1.1-14.8C469.5 287.4 480.5 270.5 480.5 251zM65.8 271.1c-6.6-4.5-10.9-12.1-10.9-20.8 0-13.8 11.1-25.1 24.7-25.1 5.6 0 10.8 1.9 15 5.1C81.1 242.2 71.1 256 65.8 271.1zM389.3 109.1c0-9.2 7.4-16.8 16.5-16.8s16.5 7.5 16.5 16.8c0 9.2-7.4 16.8-16.5 16.8S389.3 118.4 389.3 109.1zM158.5 288.4c0-17.6 14.2-31.8 31.8-31.8s31.8 14.2 31.8 31.8c0 17.6-14.2 31.8-31.8 31.8S158.5 306 158.5 288.4zM256 400c-47.6-0.2-76-28.5-77.2-29.7l12.6-12.4c0.2 0.2 23.7 24.2 64.6 24.4 40.3-0.2 64.2-24.2 64.5-24.4l12.6 12.4C331.9 371.5 303.6 399.8 256 400zM322.3 320.2c-17.6 0-31.8-14.2-31.8-31.8 0-17.6 14.2-31.8 31.8-31.8s31.8 14.2 31.8 31.8C354.1 306 339.8 320.2 322.3 320.2zM446.4 271.5c-5.4-15.3-15.6-29.4-29.3-41.4 4.2-3.3 9.5-5.2 15.2-5.2 13.9 0 25.1 11.4 25.1 25.5C457.5 259.2 453.1 266.9 446.4 271.5z"/></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_reddit', $delay, $zero, $real ) : '').($fallback ? '<strong>R</strong>' : '').'</a>';
			break;


			case 'dribbble':
				$link = '<a href="https://dribbble.com/'.$page.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" class="dribbble-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 430.1 430.1" xml:space="preserve"><path class="inner" d="M215.1 0C96.3 0 0 96.3 0 215.1c0 118.8 96.3 215 215.1 215 118.8 0 215.1-96.3 215.1-215C430.1 96.3 333.8 0 215.1 0zM346.8 111.5c21 26.6 34.1 59.6 35.8 95.7-24.3-5.2-47.2-7.7-68.6-7.7v0h-0.2c-17.2 0-33.4 1.6-48.6 4.3-3.7-9.1-7.5-17.8-11.2-26.1C287.9 162.8 320.5 141.6 346.8 111.5zM215.1 47.3c39.6 0 75.9 13.8 104.6 36.9-22 26.3-51 45.4-82.4 58.9-22-42.6-43.3-73.1-57.9-91.7C190.9 48.7 202.8 47.3 215.1 47.3zM140.9 64.8c11.6 13.8 35 44 59.9 91.3-50.6 15.1-101.7 18.6-132.5 18.6-0.9 0-1.7 0-2.6 0h0c-5.2 0-9.7-0.1-13.4-0.2C64.3 126.3 97.3 86.4 140.9 64.8zM47.3 215.1c0-0.8 0-1.6 0.1-2.4 4.8 0.2 10.9 0.3 18.3 0.3h0c33.7-0.2 92.6-3 152.3-21.9 3.3 7.1 6.5 14.5 9.7 22.2-39.9 13.3-71.2 34.6-94.5 55.7C110.9 289.4 95.8 309.5 86.9 323 62.2 293.8 47.3 256.2 47.3 215.1zM215.1 382.9c-37.3 0-71.8-12.3-99.7-33.1 5.9-9.8 18.7-28.5 38.9-47.9 20.8-20 49.6-40.5 87.2-52.8 12.8 35.8 24.3 76.7 33.1 122.8C256.1 378.9 236 382.9 215.1 382.9zM310 353.1c-8.5-41.7-19.2-79.2-31-112.7 10.9-1.6 22.3-2.4 34.4-2.4h0.4 0 0c20 0 42 2.5 65.9 7.9C371.5 290.1 345.8 328.4 310 353.1z"/></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_dribbble', $delay, $zero ) : '').($fallback ? '<strong>D</strong>' : '').'</a>';
			break;


			case 'stumbleupon':
				$link = '<a href="http://www.stumbleupon.com/submit?url='.$url.'&amp;title='.$extract.'" title="'.$tooltip.'" class="social-link"'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="stumbleupon-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 100 76" xml:space="preserve"><path d="M55.3 28.3l6.8 3.3 10.3-3.3v-6C72.4 10 62.3 0 50 0S27.6 10 27.6 22.3v31.3c0 2.9-2.4 5.2-5.3 5.2s-5.3-2.4-5.3-5.2V40.5H0v13.1C0 66 10 76 22.4 76c12.3 0 22.4-10 22.4-22.3V22.3c0-2.9 2.4-5.2 5.3-5.2s5.3 2.4 5.3 5.2V28.3zM82.9 40.5v13.1c0 2.9-2.4 5.2-5.3 5.2s-5.3-2.4-5.3-5.2V40.3l-10.3 3.3 -6.8-3.3v13.4C55.3 66 65.3 76 77.6 76 90 76 100 66 100 53.7V40.5H82.9z"/></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_stumbleupon', $delay, $zero ) : '').($fallback ? '<strong>S</strong>' : '').'</a>';
			break;


			case 'delicious':
				$link = '<a href="http://del.icio.us/post?url='.$url.'" title="'.$tooltip.'" class="social-link"'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="stumbleupon-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" enable-background="new 0 0 32 32" viewBox="0 0 32 32" xml:space="preserve"><g><rect fill="#ffffff" height="16" width="16"/><rect fill="#dddddd" height="16" width="16" x="16" y="16"/><rect height="16" width="16" y="16"/><rect fill="#3274d1" height="16" width="16" x="16"/></g><g/><g/><g/><g/><g/><g/></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_delicious', $delay, $zero ) : '').($fallback ? '<strong>D</strong>' : '').'</a>';
			break;


			case 'instagram':
				$link = '<a href="https://instagram.com/'.$instagram.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_target">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="instagram-icon" x="0" y="0" width="'.$width.'" height="'.$height.'"  enable-background="new 0 0 30 30" viewBox="0 0 30 30" xml:space="preserve"><path d="M26 0C26 0 3.5 0 3.5 0 1.6 0 0 1.6 0 3.5L0 3.5c0 0 0 23 0 23C0 28.4 1.6 30 3.5 30c0 0 23 0 23 0 1.9-0.1 3.5-1.6 3.5-3.5 0 0 0-23 0-23C30 1.6 27.9 0.1 26 0zM15 9.5c3 0 5.5 2.5 5.5 5.5 0 3-2.5 5.5-5.5 5.5 -3 0-5.5-2.5-5.5-5.5C9.5 12 12 9.5 15 9.5zM26 24c0 1.1-0.9 2-2 2H6c-1.1 0-2-0.9-2-2V12.5h2.4C6.1 13.3 6 14.1 6 15c0 5 4 9 9 9 5 0 9-4 9-9 0-0.9-0.1-1.7-0.4-2.5H26l0 11.5C26 24 26 24 26 24zM26 5v3h0c0 0.5-0.4 1-1 1V9H22c-0.5 0-1-0.4-1-1H21V5h0C21 5 21 5 21 5c0-0.6 0.4-1 1-1h3v0C25.6 4 26 4.5 26 5c0 0 0 0 0 0H26z"/></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_instagram', $delay, $zero ) : '').($fallback ? '<strong>I</strong>' : '').'</a>';
			break;


			case 'permalink':
				global $pretext, $plugins;

				// Deal with smd_short_url plugin if exists.
				$rs = safe_row("name, status", "txp_plugin", 'name="smd_short_url" and status="1"');
				if ($rs)
					$url = hu.$pretext['id'];

				$link = '<span class="link-container"><a href="'.$url.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" onclick="toggle(\'show-link\');return false"><strong>&#128279;</strong>'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="permalink-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 512 512"><path d="M482.3 210.8L346.5 346.5c-37.5 37.5-98.2 37.5-135.7 0l-45.2-45.2 45.3-45.2 45.3 45.3c12.5 12.5 32.8 12.5 45.3 0L437 165.5c12.5-12.5 12.5-32.8 0-45.3l-45.2-45.2c-12.5-12.5-32.8-12.5-45.2 0l-48.5 48.5c-22.5-13.2-48-18.9-73.3-17.2l76.5-76.5c37.5-37.5 98.3-37.5 135.8 0l45.3 45.3C519.8 112.5 519.8 173.3 482.3 210.8zM213.9 388.6L165.5 437c-12.5 12.5-32.8 12.5-45.2 0L75 391.8c-12.5-12.5-12.5-32.7 0-45.2l135.8-135.7c12.5-12.5 32.8-12.5 45.3 0l45.3 45.3 45.3-45.2 -45.2-45.2c-37.5-37.5-98.2-37.5-135.7 0L29.8 301.3c-37.5 37.5-37.5 98.3 0 135.8L75 482.3c37.5 37.5 98.3 37.5 135.8 0l76.5-76.5C262 407.4 236.5 401.8 213.9 388.6z"/></svg>' : '').'<b>'.$title.'</b></a>'.n.'<input type="text" value="'.$url.'" '.($input_tooltip ? 'title="'.$input_tooltip.'" ' : '').'id="show-link" onclick="this.setSelectionRange(0,9999);return false" readonly></span>'.n.'<script>function toggle(e){var l=document.getElementById(e);l.style.display="block"==l.style.display?"none":"block"}</script>';
			break;

		}

		return $link;
	}

	return trigger_error( gTxt('invalid_attribute_value', array('{name}' => 'site')), E_USER_WARNING );
}


/**
 * Read or create a file with content
 *
 * @param $file, $url, $type, $delay, $zero
 * @return String  File's content
 */
function _pat_article_social_get_content($file, $url = NULL, $type, $delay, $zero)
{

	global $path_to_site, $pat_article_social_dir;

	// Proper path & file with extension.
	$file = $path_to_site.'/'.$pat_article_social_dir.'/'.$file.'.txt';

	// Times.
	$current_time = time();
	$expire_time = (int)$delay * 60 * 60;

	// Grab content file or create it.
	if ( file_exists($file) && ($current_time - $expire_time <= filemtime($file)) ) {
		// Reading file.
		$out = @file_get_contents($file);
	} else {
		// Check what kind of datas.
		if ( function_exists($type) || $delay == 0)
			$out = $type($url);
		else
			$out = $type;
		// Write or create file.
		file_put_contents($file, $out);

	}

	return $zero ? tag($out, 'span') : ( (int)$out > 0 ? tag($out, 'span') : '' );
}


/**
 * Get social counts.
 *
 * @param  String Integer URLs  Share counts
 * @return integer
 */

// Twitter
function _pat_article_social_get_twitter($url)
{
	$json = json_decode( @file_get_contents('http://urls.api.twitter.com/1/urls/count.json?url='.$url), true );

	if ( isset($json['count']) ) 
		$tw = intval($json['count']);
	else
		$tw = 0;

	return $tw;
}
// Facebook
function _pat_article_social_get_facebook($url)
{
	$src = json_decode( @file_get_contents('http://graph.facebook.com/'.$url) );
	$src->shares ? $fb_count = $src->shares : $fb_count = 0;

	return $fb_count;
}
// G+
function _pat_article_social_get_google($url)
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, "https://clients6.google.com/rpc");
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get", "id":"p", "params":{"nolog":true, "id":"'.$url.'", "source":"widget", "userId":"@viewer", "groupId":"@self"}, "jsonrpc":"2.0","key":"p", "apiVersion":"v1"}]');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array('Content-type: application/json') );
	$curl_results = curl_exec ($curl);
	curl_close ($curl);
	$json = json_decode($curl_results, true);
	$g_count = intval($json[0]['result']['metadata']['globalCounts']['count']);
	$g_count ? $g_count : $g_count = 0;

	return $g_count;
}
// Pinterest
function _pat_article_social_get_pinterest($url)
{
	$pinfo = json_decode(preg_replace('/^receiveCount\((.*)\)$/', "\\1", @file_get_contents('http://api.pinterest.com/v1/urls/count.json?callback=receiveCount&url='.$url)));

	if ( isset($pinfo->count) ) return $pinfo->count;
}
// LinkedIn
function _pat_article_social_get_linkedin($url)
{
	$linfo = json_decode( @file_get_contents('https://www.linkedin.com/countserv/count/share?url='.$url.'&amp;format=json') );

	return ( isset($linfo->count) ? $linfo->count : 0 );
}
// Buffer
function _pat_article_social_get_buffer($url)
{
	$binfo = json_decode( @file_get_contents('https://api.bufferapp.com/1/links/shares.json?url='.$url) );

	if ( isset($binfo->shares) ) return $binfo->shares;
}
// Reddit
function _pat_article_social_get_reddit($url)
{
	global $real;

	$score = $up = $down = 0;

	$content = json_decode( @file_get_contents('http://www.reddit.com/api/info.json?url='.$url) );
	if($content) {
		$score = (int) $content->data->children[0]->data->score;
		$up = (int) $content->data->children[0]->data->up;
		$down = (int) $content->data->children[0]->data->down;
	}
	if ($real)
		$score = $score + $up - $down;

	return $score;
}
// Dribbble
function _pat_article_social_get_dribbble($url)
{
	global $dribbble_data, $shot;

	if( false === _pat_article_social_occurs($dribbble_data, array('followers', 'likes', 'comments', 'shots')) )
		return trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'dribbble_data')), E_USER_WARNING);

	$data = $dribbble_data.'_count';
	$counter = 0;

	$content = json_decode( @file_get_contents('http://api.dribbble.com/shots/'.$shot) );

	if ($content) 
		$counter = (int) $content->player->{$data};
	
	return $counter;
}
// Stumbleupon
function _pat_article_social_get_stumbleupon($url)
{
	$json = json_decode( @file_get_contents('http://www.stumbleupon.com/services/1.01/badge.getinfo?url='.$url), true );

	return isset($json['result']['views']) ? intval($json['result']['views']) : 0;
}
// Delicious
function _pat_article_social_get_delicious($url) {
	$json = json_decode( @file_get_contents_curl('http://feeds.delicious.com/v2/json/urlinfo/data?url='.$url), true );

	return isset($json[0]['total_posts']) ? intval($json[0]['total_posts']) : 0;
}
// Instagram
function _pat_article_social_get_instagram() {

	global $user, $token;

	$json = json_decode( @file_get_contents('https://api.instagram.com/v1/users/'.$user.'/?access_token='.$token) );

	return $json->data->counts->followed_by;

}


/**
 * Sum of share counts
 *
 * @param  $atts array
 * @return String  HTML tag
 */

function pat_article_social_sum($atts)
{

	global $prefs, $path_to_site, $pat_article_social_dir, $thisarticle, $refs;

	extract(lAtts(array(
		'site'		=> NULL,
		'lang'		=> $prefs['language'],
		'zero' 		=> false,
		'unit'		=> 'k',
		'delay'		=> 3,
		'showalways' 	=> 0,
		'text'		=> false,
		'plural'	=> 's',
		'alternative' 	=> '',
		'class' 	=> 'shares',
	), $atts));

	if ( $site && !gps('txpreview') ) {

		($lang == 'fr-fr') ? $space = '&thinsp;' : '';

		$list = explode( ',', strtolower($site) );
		$n = count($list);

		foreach ( $list as $el ) {
			if ( false === _pat_article_social_occurs($el, $refs) )
				return trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'site')), E_USER_WARNING);
		}

		$sum = 0;

		for ($i = 0; $i < $n; ++$i)
			if ( file_exists($path_to_site.'/'.$pat_article_social_dir.'/'.$thisarticle['thisid'].'-'.$list[$i].'.txt') ) {
				$sum += @file_get_contents( $path_to_site.'/'.$pat_article_social_dir.'/'.$thisarticle['thisid'].'-'.$list[$i].'.txt' );
			}

		// Check to render a zero value
		$zero ? '' : ($sum > 0 ? '' : $sum = false);

	return ( $showalways || $sum > 0) ? tag('<b>'.$text.( ($sum > 1 && $text) ? $plural.$space.':' : '') ).' </b>'._pat_format_count($sum, $unit, $lang), 'span', ' class="'.$class.'"') : ( $zero ? tag('<b>'.$text.( ($sum > 1 && $text) ? $plural.$space.':' : '') ).' </b>'._pat_format_count($sum, $unit, $lang), 'span', ' class="'.$class.'"') : tag('<b>'.$alternative.'</b>', 'span', ' class="'.$class.'"') );

	} else {
		return;
	}

	return trigger_error( gTxt('invalid_attribute_value', array('{name}' => 'site or cache directory')), E_USER_WARNING );
}


/**
 * Check values from a list
 *
 * @param  $el $array	String	Array
 * @return boolean
 */
function _pat_article_social_occurs($el, $array)
{
	// $refs is an array outside this func.
	if($array === $refs)
		global $array;

	return in_array($el, $array);
}


/**
 * Format count results
 *
 * @param  $number $unit $lang
 * @return String rounding up (e.g. 3K)
 */

function _pat_format_count($number, $unit, $lang)
{
	($lang == 'fr-fr') ? $separator = ',' : $separator = '.';

	if($number >= 1000)
		return number_format( round($number/1000, 1, PHP_ROUND_HALF_UP), 1, $separator, '' ).$unit;
	else
		return $number;
}


/**
 * Plugin prefs: entry for cache dir.
 *
 */

function _pat_article_social_prefs()
{
	global $textarray;

	$textarray['pat_article_social_dir'] = 'Cache directory';

	if ( !safe_field ('name', 'txp_prefs', "name='pat_article_social_dir'") )
		safe_insert('txp_prefs', "prefs_id=1, name='pat_article_social_dir', val='cache', type=1, event='admin', html='text_input', position=21");

	safe_repair('txp_plugin');
}


/**
 * Delete cache dir in prefs & all files in it.
 *
 */
function _pat_article_social_cleanup()
{
	global $path_to_site, $pat_article_social_dir;

	array_map( 'unlink', glob("'.$path_to_site.'/'.$pat_article_social_dir.'/'*.txt") );
	safe_delete('txp_prefs', "name='pat_article_social_dir'");
}
