<?php
/**
 * pat_article_social (formerly pat_article_tweet) Textpattern CMS plugin
 * @author:  © Patrick LEFEVRE, all rights reserved. <patrick[dot]lefevre[at]gmail[dot]com>
 * @link:    http://pat-article-social.cara-tm.com
 * @type:    Admin+Public
 * @prefs:   no
 * @order:   5
 * @version: 0.6.2
 * @license: GPLv2
*/

/**
 * This plugin tags registry
 *
 */
if (class_exists('\Textpattern\Tag\Registry')) {
	Txp::get('\Textpattern\Tag\Registry')
		->register('pat_article_social_meta')
		->register('pat_article_social')
		->register('pat_article_social_sum')
		->register('share')
		->register('twttr')
		->register('fb')
		->register('instagram')
		->register('gplus')
		->register('gist')
		->register('bq')
		->register('amp_social_script');
}


/**
 * TXP admin side callbacks
 *
 */
if (txpinterface == 'admin')
{

	global $pat_article_social_gTxt;

	register_callback('pat_article_social_prefs', 'prefs', '', 1);
	register_callback('pat_article_social_cleanup', 'plugin_lifecycle.pat_article_social', 'deleted');

	// Default plugin Textpack.
	$pat_article_social_gTxt = array(
		'pat_article_social_dir' => 'Cache directory',
		'pat_article_social_twttr' => 'Default blockquote markup for twttr short tag',
		'pat_article_social_delay' => 'Caching delay for the social counting values',
	);
}

/**
 * Plugin's Globals
 *
 */
global $refs, $twcards;

// List of social networks that support share count.
$refs = array('facebook', 'twitter', 'google', 'pinterest', 'Linkedin', 'buffer', 'reddit', 'dribbble', 'stumbleupon', 'delicious', 'instagram');
// List of Twitter Card types.
$twcards = array('summary', 'summary_large_image', 'product');

/**
 * Generate meta tag for social websites
 *
 * @param  array  Tag attributes
 * @return string HTML meta tags
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
		'locale' 	=> $prefs['language'],
		'fb_api' 	=> NULL,
		'fb_admins' 	=> NULL,
		'fb_type' 	=> 'website',
		'fb_author' 	=> NULL,
		'g_author' 	=> NULL,
		'fb_publisher'	=> NULL,
		'g_publisher'	=> NULL,
		'title' 	=> $prefs['sitename'],
		'description' 	=> page_title(array()),
		'lenght' 	=> 200,
	), $atts));


	if ( $type && !gps('txpreview') ) {

		// Create an array of social services from list.
		$type = explode(',', $type);
		// Format the lang code
		$locale = _pat_locale($locale);
		// Get URI.
		$current = _pat_article_social_get_uri;
		// Check image.
		$image ? $image : $image = _pat_article_social_image();
		// Sanitize.
		$description = str_replace(array('\r\n', '\r'), '\n', $description);
		// Social Networks often limit description to 200 characters.
		$description = strip_tags(_pat_article_social_trim($description, $lenght));
		// Remove some URLs into text content.
		$description = preg_replace('/(([&-a-z0-9;])?(#[a-z0-9;])?)[a-z0-9]+;/i', '', $description);



		foreach ($type as $service) {

			switch( strtolower($service) ) {

			case 'twitter':
				if( false == _pat_article_social_occurs($card, $twcards) )
					return trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'card')), E_USER_WARNING);

				$img = $thisarticle['article_image'];
				$img ? $card = 'summary_large_image' : '';
				$tags .= '<meta name="twitter:card" content="'.$card.'">'.n;
				$tags .= _pat_article_social_validate_user($user, 'site');
				$tags .= _pat_article_social_validate_user($creator, 'creator');
				$tags .= '<meta name="twitter:image'.($card == 'summary_large_image' ? ':src' : '').'" content="'._pat_article_social_image($image).'">'.n;
				$tags .= <<<EOF
<meta name="twitter:url" content="{$current()}">
<meta name="twitter:title" content="{$title}">
<meta name="twitter:description" content="{$description}">

EOF;

				$tags .= ($label1 ? '<meta name="twitter:label1" content="'.$label1.'">'.n : '');
				$tags .= ($data1 ? '<meta name="twitter:data1" content="'.$data1.'">'.n : '');
				$tags .= ($label2 ? '<meta name="twitter:label2" content="'.$label2.'">'.n : '');
				$tags .= ($data2 ? '<meta name="twitter:data2" content="'.$data2.'">'.n : '');
			break;


			case 'facebook':
	$tags .= <<<EOF
<meta property="og:rich_attachment" content="true">
<meta property="og:locale" content="$locale">
<meta property="og:site_name" content="{$prefs['sitename']}">
<meta property="og:title" content="{$title}">
<meta property="og:description" content="$description">
<meta property="og:url" content="{$current()}">

EOF;
	$tags .= '<meta property="og:updated_time" content="'.($thisarticle['posted'] ? date('Y-m-d H:i:s', $thisarticle['posted']) : $prefs['lastmod']).'">'.n;
	$tags .= ($image ? '<meta property="og:image" content="'.$image.'">'.n._pat_article_social_image_size($thisarticle['article_image'], 'facebook') : '');
	$tags .= ($pretext['id'] ? '<meta property="og:type" content="article">' : '<meta property="og:type" content="website">').n;
	$tags .= ($fb_api ? '<meta property="fb:app_id" content="'.$fb_api.'">'.n : '');
	$tags .= ($fb_admins ? '<meta property="fb:admins" content="'.$fb_admins.'">'.n : '');
	$tags .= ($fb_author ? '<meta property="article:author" content="https://www.facebook.com/'.$fb_author.'">'.n : '');
	$tags .= ($fb_publisher ? '<meta property="article:publisher" content="https://www.facebook.com/'.$fb_publisher.'">'.n : '');
	$tags .= '<meta property="article:section" content="'.section(array('title'=>1)).'">'.n;
	$tags .= '<meta property="article:published_time" content="'.posted(array('format'=>'iso8601')).'">'.n;
	$tags .= (modified(array()) ? '<meta property="article:modified_time" content="'.modified(array('format'=>'iso8601')).'">' : '').n;
			break;


			case 'google':
	$tags .= <<<EOF
<meta itemprop="name" content="{$prefs['sitename']}">
<meta itemprop="description" content="$description">
<meta itemprop="url" content="{$current()}">

EOF;
	$tags .= ($image ? '<meta itemprop="image" content="'.$image.'">'.n : '');
	$tags .= ($g_author ? '<link rel="author" href="https://plus.google.com/'.$g_author.'">'.n : '');
	$tags .= ($g_publisher ? '<link rel="publisher" href="https://plus.google.com/'.$g_publisher.'">'.n : '');
			break;

			}

		}

	return '<!-- Open Graph Meta tags - pat-article-social -->'.n.$tags;

	}

	return;
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
	if ( preg_match("/\@[a-z0-9_]+/i", $entry) )
		$out = ( $attribute ? '<meta name="twitter:'.$attribute.'" content="'.$entry.'">'.n : str_replace('@', '', $entry) );

	return $out ? $out : trigger_error( gTxt('invalid_attribute_value', array('{name}' => 'user or creator')), E_USER_WARNING );
}


/**
 * Display article image
 *
 * @param  int $pic  Image ID
 * @return string    Full article image URI
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

		if ( $rs = safe_row('*', 'txp_image', 'id = ' . intval($img)) )
			$img = imagesrcurl($rs['id'], $rs['ext']);
		else
			$img = null;

	}

	return $img;
}


/**
 * Display width and height of an image
 *
 * @param  integer $id   Image id
 * @return string  $out  HTML tags
 */
function _pat_article_social_image_size($id, $type)
{

	$out = '';

	if ( intval($id) ) {

		if ( $rs = safe_row('id, w, h', 'txp_image', 'id = "'.$id.'"') )

			switch ($type) {

				case 'facebook':
					$out .= '<meta property="og:image:width" content="'.$rs['w'].'">'.n.'<meta property="og:image:height" content="'.$rs['h'].'">'.n;
				break;

				case 'twitter':
					$out .= '<meta name="twitter:image:width" content="'.$rs['w'].'">'.n.'<meta name="twitter:image:height" content="'.$rs['h'].'">'.n;
				break;
			}

	}


	return $out;
}


/**
 * Display current URL
 *
 * @param
 * @return String  URI
 */
function _pat_article_social_get_uri()
{
	return PROTOCOL.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
}


/** 
 * Trims text to a space then adds ellips
 * @param string $input      text to trim
 * @param int    $length     in characters to trim to
 * @param bool   $strip_html strip html tags if present
 * @param boll   $no_dot     remove last dot if present
 * @return string
 */
function _pat_article_social_trim($input, $length, $strip_html = true) {

	// Sanitize input.
	$input = preg_replace('/\s+/S', " ", $input);

	// Strip tags, if desired.
	if ($strip_html)
		$input = strip_tags($input);

	// Trim if longer than trim length.
	if ( strlen($input) > $length )

		// Special signs found?
		if ( in_array( substr($input, $length, 1), array(' ', '.', '!', '?') ) )
			// Remove last special sign, add hellips.
			return substr( trim( $input, '.' ), 0, $length - 1).'...';
		else
			// No special signs, add hellips.
			return substr($input, 0, $length).'...';

	else
		// No need to trim, already shorter than trim length.
		return $input;
}


/**
 * Convert into proper local code
 *
 * @param string  $locale   ISO code
 * @param boolean $stripped Convert to underscore
 * @return string           Formatted ISO code
 */
function _pat_locale($locale, $striped = NULL)
{
	if (true != $striped)
		return str_replace('-', '_', $locale);
	else
		return substr($locale, 0, 2);
}


/**
 * Helper tag to display share links
 *
 * @param  array Tag attributes
 * @return MTML  markup Share links
 */
function share($atts)
{
	global $prefs, $thisaticle;

	extract(lAtts(array(
		'text'		 => NULL,
		'tooltip'	 => NULL,
	), $atts));

	if ($text)
		return pat_article_social(array('site'=>'facebook','tooltip'=>$tooltip,'content'=>_pat_article_social_trim($text, 40),'icon'=>1,'count'=>0,'class'=>'facebook','with_title'=>0)).pat_article_social(array('site'=>'google','tooltip'=>$tooltip,'content'=>_pat_article_social_trim($text, 60),'icon'=>1,'count'=>0,'class'=>'google','with_title'=>0)).pat_article_social(array('site'=>'twitter','tooltip'=>$tooltip,'content'=>_pat_article_social_trim($text, 116),'icon'=>1,'count'=>0,'class'=>'twitter','with_title'=>0));
	else
		trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'text')), E_USER_WARNING);

}


/**
 * Display a blockquote with social links
 *
 * @param  array        Tag attributes
 * @return HTML markup  Embeded Tweet
 */
function bq($atts)
{

 	global $prefs, $thisaticle;

 	extract(lAtts(array(
		'text'		 => NULL,
		'tooltip'	 => NULL,
	), $atts));

 	include_once txpath.'/lib/classTextile.php';
 	$textile = new Textile($prefs['doctype']);

	return '<blockquote class="pat-bq">'.$textile->TextileThis($text).'<p>'.share($atts).'</p></blockquote>';


}


/**
 * Display embedded tweets
 *
 * @param  array        Tag attributes
 * @return HTML markup  Embeded Tweet
 */
function twttr($atts)
{

	global $prefs, $variable;

	extract(lAtts(array(
		'status'	 => NULL,
		'markup'	 => ($prefs['pat_article_social_twttr'] == 1 ? 'blockquote' : 'iframe'),
		'align' 	 => 'center',
		'max_width' 	 => '500',
		'media' 	 => false,
		'thread' 	 => false,
		'related' 	 => false,
		'locale'	 => $prefs['language'],
	), $atts));

	if ( !gps('txpreview') ) {
	
		switch ( strtolower($markup) ) {

			case 'iframe':
				$_att = ' style="border:0;height:100% !important" src=';
			break;

			case 'object':
				$_att = ' data=';
			break;

			default:
				$_att = ' ';

		}

		// Full URL of a Twitter link given.
		if ( preg_match('#http(s|):\/\/twitter\.com(\/\#\!\/|\/)([a-zA-Z0-9_]{1,20})\/status(es)*\/(\d+)#i', $status) ) {

			if ($markup == 'iframe' || $markup == 'object')
			
				$out = '<!-- Embedded Tweet - pat-article-social --> <div class="pat-twttr"><'.$markup.$_att.'"http://twitframe.com/show?url='.urlencode($status).'"></'.$markup.'></div>';

			// Blockquote markup.
			else
				$id = basename($status);

		// Short URL of a Twitter link given.
		} elseif ( preg_match('#^[0-9]+$#i', $status) )
				$id = $status;

			$json = 'https://api.twitter.com/1/statuses/oembed.json?id='.$id.'&amp;align='.$align.'&amp;maxwidth='.$max_width.'&amp;hide_media='.$media.'&amp;hide_thread='.$thread.'&amp;related='.$related.'&amp;lang='.$locale;
			$datas = json_decode( @file_get_contents($json), true );

			// Display json result.
			if ($datas)
				$out = '<!-- Embedded Tweet - pat-article-social --> ' . (
					$variable['mkp_amp'] ? 
						'<amp-twitter width="390" height="450" layout="responsive" data-tweetid="'.$id.'"></amp-twitter>' 
					: 
						str_replace(
							array(' align="center"', ' width="500"'),
							array('', ' style="width:500px"'),
							$datas['html']
						)
					);

		} else {

			$out = trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'status')), E_USER_WARNING);

		}

	return in_array(strtolower($markup), array('iframe', 'object', 'blockquote') ) ? $out : trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'markup')), E_USER_WARNING);


}


/**
 * Display fb embedded post
 *
 * @param  array   Tag attributes
 * @return iframe  Embedded post
 */
function fb($atts)
{
	global $prefs, $variable;

	extract(lAtts(array(
		'status'	 => NULL,
		'locale'	 => $prefs['language'],
	 ), $atts));

	if ( !gps('txpreview') ) {


		if( preg_match('#^https:\/\/w{3}\.facebook\.com\/[a-z-A-Z-0-9.]*\/posts\/[0-9]*(.*)?$#i', $status) ) {

			return '<!-- Embedded fb status - pat-article-social --> ' . ( $variable['mkp_amp'] ? 
				'<amp-facebook width="486" height="657" layout="responsive" data-href="'.$status.'"></amp-facebook>' : 
				'<div id="fb-root"></div><script>!function(e,t,n){var c,o=e.getElementsByTagName(t)[0];e.getElementById(n)||(c=e.createElement(t),c.id=n,c.src="//connect.facebook.net/'._pat_locale($locale).'/all.js#xfbml=1",o.parentNode.insertBefore(c,o))}(document,"script","facebook-jssdk");</script><div class="fb-post" data-href="'.$status.'"></div>' );

		}

		return trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'status')), E_USER_WARNING);
	}

}


/**
 * Display G+ embedded post
 *
 * @param  array   Tag attributes
 * @return iframe  Embedded post
 */
function gplus($atts)
{

	extract(lAtts(array(
		'status' => NULL,
	), $atts));

	if ( !gps('txpreview') ) {

		if( preg_match('#^https:\/\/plus\.google\.com\/[a-z-A-Z-0-9+]*\/posts\/[a-z-A-Z-0-9]*$#i', $status) ) {
			return '<!-- Embedded G+ status - pat-article-social --> <div class="g-post" data-href="'.$status.'"></div><script src="https://apis.google.com/js/platform.js" async defer></script>';
		}

		return trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'status')), E_USER_WARNING);
	}

}


/**
 * Display Instagram embedded post
 *
 * @param  array   Tag attributes
 * @return HTLM    Embedded post
 */
function instagram($atts)
{
	global $variable;

	extract(lAtts(array(
		'status' => NULL,
		'size'	 => 'm',
	), $atts));


	$url = preg_replace('/\?.*/', '', $status);
	$dim = array('t', 'm', 'l');

	$json = 'http://api.instagram.com/publicapi/oembed/?url='.$status;
	$datas = json_decode( @file_get_contents($json), true );

	return $datas ? '<!-- Embedded Instagram status - pat-article-social --> ' . ( $variable['mkp_amp'] ?
				'<amp-instagram data-shortcode="'.$status.'" width="320" height="392" layout="responsive"></amp-instagram>' : $datas['html'] ) : '';

}


/**
 * Display embedded gist
 *
 * @param  array   Tag attribute
 * @return string  Github script link
 */
function gist($atts)
{

	extract(lAtts(array(
		'url'	=> NULL,
	), $atts));

	if ( preg_match('#^https:\/\/gist.github.com\/[a-z-0-9-]+\/[a-z-0-9]+$#i', $url) )
	 	return '<!-- Embedded Gist code - pat-article-social --> <script src="'.$url.'.js"></script>';

}


/*
 * Inject custom social network scripts for Google AMP
 *
 * @param  array  Tag attribute
 * @return script External scripts
 */
function amp_social_script($atts)
{

	extract(lAtts(array(
		'site'	=> NULL,
	), $atts));

	if ($site) {

		$type = explode(',', $site);
		$out = '';

		foreach ($type as $service) {

			switch ( strtolower($service) ) {

				case 'twitter':
					$out .= '<script async custom-element="amp-twitter" src="https://cdn.ampproject.org/v0/amp-twitter-0.1.js"></script>'.n;
				break;

				case 'facebook':
					$out .= '<script async custom-element="amp-facebook" src="https://cdn.ampproject.org/v0/amp-facebook-0.1.js"></script>'.n;
 	 	 		break;

				case 'instagram':
					$out .= '<script async custom-element="amp-instagram" src="https://cdn.ampproject.org/v0/amp-instagram-0.1.js"></script>'.n;
				break;
			}

		}

		return $out;

	} else {

		return trigger_error(gTxt('invalid_attribute_value', array('{site}' => 'status')), E_USER_WARNING);
	}

}


/**
 * Generate links for social websites
 *
 * @param  array   Tag attributes
 * @return String  Link with encoded article body in arguments
 */
function pat_article_social($atts)
{

	global $prefs, $thisarticle, $real, $dribbble_data, $shot, $user, $token, $instagram_type;

	extract(lAtts(array(
		'site' 			 => 'permalink',
		'tooltip' 		 => NULL,
		'input_tooltip' 	 => NULL,
		'title' 		 => NULL,
		'via' 			 => NULL,
		'shot' 			 => NULL,
		'dribbble_data' 	 => 'followers',
		'page' 			 => NULL,
		'instagram' 		 => NULL,
		'instagram_type' 	 => 'followers',
		'user' 			 => NULL,
		'token' 		 => NULL,
		'content' 		 => 'excerpt',
		'itemprop' 		 => true,
		'image' 		 => NULL,
		'class' 		 => NULL,
		'icon' 			 => false,
		'width' 		 => '16',
		'height' 		 => '16',
		'count' 		 => false,
		'real' 			 => false,
		'zero' 			 => false,
		'unit' 			 => 'k',
		'delay' 		 => (empty($prefs['pat_article_social_delay']) ? '3' : $prefs['pat_article_social_delay']),
		'fallback' 		 => true,
		'campaign' 		 => NULL,
		'with_title' 		 => true,
	), $atts));

	if ( $site && !gps('txpreview') ) {

		// Check text content.
		if( in_array($content, array('excerpt', 'body')) )
			$extract = $thisarticle[$content];
		elseif( !empty($content) )
			$extract = $content;
		else
			trigger_error( gTxt('invalid_attribute_value', array('{name}' => 'content')), E_USER_WARNING );

		$url = permlink( array() );
		// Sanitize
		$text = ($with_title ? $thisarticle['title'].'.' : '').preg_replace('/(([&-a-z0-9;])?(#[a-z0-9;])?)[a-z0-9]+;/i', '', strip_tags($extract) );
		// Limit content
		$minus = strlen($via)+7;
		// Twitter shorten urls: http://bit.ly/ILMn3F
		$words = urlencode( _pat_article_social_trim($text, 122-$minus, true, false) );


		switch ( strtolower($site) ) {

			case 'twitter':
				$link = '<a'.($itemprop ? ' itemprop="url"' : '').' href="https://twitter.com/intent/tweet?'.($via ? 'via='._pat_article_social_validate_user($via).'&amp;' : '').'text='.$words.'&amp;url='.$url.($campaign ? $campaign : '').'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="twitter-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 16 16" xml:space="preserve"><path d="M16 3c-0.6 0.3-1.2 0.4-1.9 0.5 0.7-0.4 1.2-1 1.4-1.8-0.6 0.4-1.3 0.7-2.1 0.8C12.9 1.9 12 1.5 11.1 1.5c-1.8 0-3.3 1.5-3.3 3.3 0 0.3 0 0.5 0.1 0.8C5.2 5.4 2.7 4.1 1.1 2.1 0.8 2.6 0.7 3.2 0.7 3.8c0 1.1 0.6 2.1 1.5 2.7C1.6 6.5 1.1 6.3 0.6 6.1v0c0 1.6 1.1 2.9 2.6 3.2C3 9.4 2.7 9.5 2.4 9.5c-0.2 0-0.4 0-0.6-0.1 0.4 1.3 1.6 2.3 3.1 2.3-1.1 0.9-2.5 1.4-4.1 1.4-0.3 0-0.5 0-0.8 0C1.5 14 3.2 14.5 5 14.5c6 0 9.3-5 9.3-9.3L14.4 4.7C15 4.3 15.6 3.7 16 3z"/></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode( permlink(array()) ), '_pat_article_social_get_twitter', $delay, $zero ) : '').($fallback ? '<strong>T</strong>' : '').'</a>';
			break;


			case 'facebook':
				$link = '<a'.($itemprop ? ' itemprop="url"' : '').' rel="noreferrer" href="http://www.facebook.com/sharer/sharer.php?u='.$url.'&amp;t='.urlencode( title(array()) ).($campaign ? $campaign : '').'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg width="'.$width.'" height="'.$height.'" class="facebook-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414"><path d="M15.117 0H.883C.395 0 0 .395 0 .883v14.234c0 .488.395.883.883.883h7.663V9.804H6.46V7.39h2.086V5.607c0-2.066 1.262-3.19 3.106-3.19.883 0 1.642.064 1.863.094v2.16h-1.28c-1 0-1.195.476-1.195 1.176v1.54h2.39l-.31 2.416h-2.08V16h4.077c.488 0 .883-.395.883-.883V.883C16 .395 15.605 0 15.117 0" fill-rule="nonzero"/></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode( permlink(array()) ), '_pat_article_social_get_facebook', $delay, $zero ) : '').($fallback ? '<strong>F</strong>' : '').'</a>';
			break;


			case 'google':
				$link = '<a'.($itemprop ? ' itemprop="url"' : '').' rel="noreferrer" href="https://plus.google.com/share?url='.$url.($campaign ? $campaign : '').'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg width="'.$width.'" height="'.$height.'" class="gplus-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414"><g><path d="M5.09 7.273v1.745H7.98c-.116.75-.873 2.197-2.887 2.197-1.737 0-3.155-1.44-3.155-3.215S3.353 4.785 5.09 4.785c.99 0 1.652.422 2.03.786l1.382-1.33c-.887-.83-2.037-1.33-3.41-1.33C2.275 2.91 0 5.184 0 8s2.276 5.09 5.09 5.09c2.94 0 4.888-2.065 4.888-4.974 0-.334-.036-.59-.08-.843H5.09zM16 7.273h-1.455V5.818H13.09v1.455h-1.454v1.454h1.455v1.455h1.455V8.727H16"/></g></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, permlink(array()), '_pat_article_social_get_google', $delay, $zero ) : '').($fallback ? '<strong>G</strong>' : '').'</a>';
			break;


			case 'pinterest':
				if ( true == _pat_article_social_image() )
					$link = '<a'.($itemprop ? ' itemprop="url"' : '').' rel="noreferrer" href="http://pinterest.com/pin/create/button/?url='.$url.'&amp;description='.$words.'&amp;media='._pat_article_social_image($image).($campaign ? $campaign : '').'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg width="'.$width.'" height="'.$height.'" class="pinterest-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414"><path d="M8 0C3.582 0 0 3.582 0 8c0 3.39 2.108 6.285 5.084 7.45-.07-.633-.133-1.604.028-2.295.146-.625.938-3.977.938-3.977s-.24-.48-.24-1.188c0-1.11.646-1.943 1.448-1.943.683 0 1.012.513 1.012 1.127 0 .687-.436 1.713-.662 2.664-.19.797.4 1.445 1.185 1.445 1.42 0 2.514-1.498 2.514-3.662 0-1.915-1.376-3.254-3.342-3.254-2.276 0-3.61 1.707-3.61 3.472 0 .687.263 1.424.593 1.825.066.08.075.15.057.23-.06.252-.196.796-.223.907-.035.146-.115.178-.268.107-.998-.465-1.624-1.926-1.624-3.1 0-2.524 1.834-4.84 5.287-4.84 2.774 0 4.932 1.977 4.932 4.62 0 2.757-1.74 4.977-4.153 4.977-.81 0-1.572-.422-1.833-.92l-.5 1.902c-.18.695-.667 1.566-.994 2.097.75.232 1.545.357 2.37.357 4.417 0 8-3.582 8-8s-3.583-8-8-8z" fill-rule="nonzero"/></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_pinterest', $delay, $zero ) : '').($fallback ? '<strong>P</strong>' : '').'</a>';
			break;


			case 'tumblr':
				$link = '<a'.($itemprop ? ' itemprop="url"' : '').' rel="noreferrer" href="http://www.tumblr.com/share/quote?quote='.$words.($campaign ? $campaign : '').'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg width="'.$width.'" height="'.$height.'" class="tumblr-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414"><path d="M9.708 16c-3.396 0-4.687-2.504-4.687-4.274V6.498H3.403V4.432C5.83 3.557 6.412 1.368 6.55.12c.01-.086.077-.12.115-.12H9.01v4.076h3.2v2.422H8.997v4.98c.01.667.25 1.58 1.472 1.58h.067c.424-.012.994-.136 1.29-.278l.77 2.283c-.288.424-1.594.916-2.77.936h-.12z" fill-rule="nonzero"/></svg>' : '').'<b>'.$title.'</b>'.($fallback ? '<strong>T</strong>' : '').'</a>';
			break;


			case 'pocket':
				$link = '<a'.($itemprop ? ' itemprop="url"' : '').' rel="noreferrer" href="http://getpocket.com/edit?url='.$url.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg width="'.$width.'" height="'.$height.'" class="pocket-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414"><path d="M12.533 6.84L8.77 10.45c-.213.204-.486.306-.76.306-.273 0-.547-.102-.76-.306L3.488 6.84c-.437-.418-.45-1.113-.032-1.55.42-.438 1.114-.452 1.55-.033l3.005 2.88 3.005-2.88c.436-.42 1.13-.405 1.55.032.42.437.405 1.13-.032 1.55zm3.388-5.028c-.207-.572-.755-.956-1.363-.956H1.45c-.6 0-1.144.376-1.357.936-.063.166-.095.34-.095.515v4.828l.055.96c.232 2.184 1.365 4.092 3.12 5.423.03.024.063.047.095.07l.02.015c.94.687 1.992 1.152 3.128 1.382.524.105 1.06.16 1.592.16.492 0 .986-.046 1.472-.136.058-.013.116-.023.175-.037.016-.002.033-.01.05-.018 1.088-.237 2.098-.69 3.004-1.352l.02-.014.096-.072c1.754-1.33 2.887-3.24 3.12-5.423l.054-.96V2.307c0-.167-.02-.333-.08-.495z" fill-rule="nonzero"/></svg>' : '').'<b>'.$title.'</b>'.($fallback ? '<strong>P</strong>' : '').'</a>';
			break;


			case 'instapaper':
				$link = '<a'.($itemprop ? ' itemprop="url"' : '').' rel="noreferrer" href="http://www.instapaper.com/hello2?url='.$url.'&amp;title='.urlencode( title(array()) ).'&amp;description='.$words.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="instapaper-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 199.2 359.3"><path d="M133.2 52.9c0 84.1 0 168.1 0 252.2 -0.1 3.9 0.9 8.1 3.6 11.1 5 5.7 12.4 8.6 19.6 10.4 12.2 3.4 24.9 4.4 37.6 4.9 2.6-0.1 5.3 1.8 5.3 4.6 0.3 6 0.2 12 0.1 18 0.2 3.4-3.2 5.3-6.2 5 -61 0-122 0-183.1 0 -2.8-0.2-6.2 0.6-8.4-1.7 -1.6-1.7-1.2-4.2-1.3-6.3 0.1-5-0.1-9.9 0.1-14.9 0-2.9 2.9-5 5.6-4.7 14.3-0.2 28.8-1.5 42.5-5.9 5.9-2.1 11.9-5 15.6-10.3 3.1-4.4 2.6-10.1 2.6-15.2 0-82.3 0-164.7 0-247 0-1.8-0.2-3.8-1.4-5.2 -3.4-3.9-8.2-6.1-12.8-8.2 -14.2-5.8-29.3-9-44.4-11.5 -2.8-0.6-7-0.8-7.6-4.4 -0.6-5.2-0.1-10.5-0.3-15.7 0-2.8-0.1-6.5 3-7.7C6.5-0.5 9.8-0.1 13-0.2 70.3-0.2 127.7-0.2 185-0.2c3.9 0.1 7.8-0.4 11.6 0.5 3 1.3 2.8 5 2.8 7.7 -0.2 5.3 0.3 10.6-0.3 15.9 -0.7 3.2-4.4 3.6-7.1 4 -13 1.8-26 4.3-38.5 8.6 -5.2 1.8-10.4 3.9-15 7C135.4 45.6 132.8 49 133.2 52.9z"/></svg>' : '').'<b>'.$title.'</b>'.($fallback ? '<strong>I</strong>' : '').'</a>';
			break;


			case 'linkedin':
				$link = '<a'.($itemprop ? ' itemprop="url"' : '').' rel="noreferrer" href="http://www.linkedin.com/shareArticle?mini=true&amp;url='.$url.'&amp;title='.urlencode( title(array()) ).'&amp;source='.urlencode( site_slogan(array()) ).'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg width="'.$width.'" height="'.$height.'" class="linkedin-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414"><path d="M13.632 13.635h-2.37V9.922c0-.886-.018-2.025-1.234-2.025-1.235 0-1.424.964-1.424 1.96v3.778h-2.37V6H8.51V7.04h.03c.318-.6 1.092-1.233 2.247-1.233 2.4 0 2.845 1.58 2.845 3.637v4.188zM3.558 4.955c-.762 0-1.376-.617-1.376-1.377 0-.758.614-1.375 1.376-1.375.76 0 1.376.617 1.376 1.375 0 .76-.617 1.377-1.376 1.377zm1.188 8.68H2.37V6h2.376v7.635zM14.816 0H1.18C.528 0 0 .516 0 1.153v13.694C0 15.484.528 16 1.18 16h13.635c.652 0 1.185-.516 1.185-1.153V1.153C16 .516 15.467 0 14.815 0z" fill-rule="nonzero"/></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_linkedin', $delay, $zero ) : '').($fallback ? '<strong>L</strong>' : '').'</a>';
			break;


			case 'buffer':
				$link = '<a'.($itemprop ? ' itemprop="url"' : '').' rel="noreferrer" href="http://bufferapp.com/add?id=fd854fd5d145df9c&amp;url='.$url.'&amp;text='.urlencode( site_slogan(array()) ).'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" version="1" class="buffer-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 979 1000" preserveAspectRatio="xMinYMin meet"><path d="M0 762q11-15 31.5-25.5t49-20 40.5-15.5q19 0 33.5 4.5t33.5 15 25 12.5q47 21 260 119 19 4 35.5 0t39.5-17.5 24-14.5q20-9 76.5-34.5t87.5-39.5q4-2 41.5-21t60.5-24q13-2 27.5 1t23.5 7.5 23 13 18 10.5 15.5 6 18.5 8 11 11q3 4 4 14-10 13-31 24t-51 22-40 16q-43 20-128.5 61.5t-128.5 61.5q-7 3-21 11.5t-23.5 13-25.5 11-27.5 7-29.5-1.5l-264-123q-6-3-32-14t-51.5-22-53.5-24-46.5-23.5-21.5-16.5q-4-4-4-13zm0-268q11-15 31.5-25t50-20 41.5-15q19 0 34 4.5t34.5 15 25.5 13.5q42 19 126.5 58t127.5 59q19 5 37 0.5t39-17 25-14.5q68-32 160-72 11-5 31.5-16.5t38.5-19.5 36-11q16-3 31.5 1t37.5 17 23 13q5 3 15.5 6.5t18 8 11.5 10.5q3 5 4 14-10 14-31.5 25.5t-52.5 22.5-41 16q-48 23-135.5 65t-122.5 59q-7 3-26 14t-29 15-32.5 10-35.5 0q-214-101-260-122-6-3-44-19t-69.5-30-61.5-29.5-34-22.5q-4-4-4-14zm0-267q10-15 31.5-26.5t52.5-22.5 41-16l348-162q30 0 53.5 7t56.5 26 40 22q39 18 117 54.5t117 54.5q4 2 36.5 15t54.5 24 27 20q3 4 4 13-9 13-26 22.5t-43.5 19-34.5 13.5q-47 22-140 66.5t-139 66.5q-6 3-20 11t-23 12.5-25 10.5-27 6-28-1q-245-114-256-119-4-2-63-27.5t-102-46.5-48-30q-4-4-4-13z"/></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_linkedin', $delay, $zero ) : '').($fallback ? '<strong>B</strong>' : '').'</a>';
			break;


			case 'reddit':
				$link = '<a'.($itemprop ? ' itemprop="url"' : '').' rel="noreferrer" href="http://www.reddit.com/submit?url='.$url.'&amp;title='.$text.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg width="'.$width.'" height="'.$height.'" class="reddit-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414"><path d="M1.473 9.368c-.04.185-.06.374-.06.566 0 2.3 2.94 4.173 6.554 4.173 3.613 0 6.553-1.872 6.553-4.173 0-.183-.02-.364-.055-.54l-.01-.022c-.013-.036-.02-.073-.02-.11-.2-.784-.745-1.497-1.533-2.072-.03-.01-.058-.026-.084-.047-.017-.013-.03-.028-.044-.043-1.198-.824-2.91-1.34-4.807-1.34-1.88 0-3.576.506-4.772 1.315-.01.012-.02.023-.033.033-.026.022-.056.04-.087.05-.805.576-1.364 1.293-1.572 2.086 0 .038-.01.077-.025.114l-.005.01zM8 13.003c-1.198 0-2.042-.26-2.58-.8-.116-.116-.116-.305 0-.422.117-.116.307-.116.424 0 .42.42 1.125.625 2.155.625 1.03 0 1.735-.204 2.156-.624.116-.116.306-.116.422 0 .117.118.117.307 0 .424-.538.538-1.382.8-2.58.8zM5.592 7.945c-.61 0-1.12.51-1.12 1.12 0 .608.51 1.102 1.12 1.102.61 0 1.103-.494 1.103-1.102 0-.61-.494-1.12-1.103-1.12zm4.83 0c-.61 0-1.12.51-1.12 1.12 0 .608.51 1.102 1.12 1.102.61 0 1.103-.494 1.103-1.102 0-.61-.494-1.12-1.103-1.12zM13.46 6.88c.693.556 1.202 1.216 1.462 1.94.3-.225.48-.578.48-.968 0-.67-.545-1.214-1.214-1.214-.267 0-.52.087-.728.243zM1.812 6.64c-.67 0-1.214.545-1.214 1.214 0 .363.16.7.43.927.268-.72.782-1.375 1.478-1.924-.202-.14-.443-.218-.694-.218zm6.155 8.067c-3.944 0-7.152-2.14-7.152-4.77 0-.183.016-.363.046-.54C.33 9.068 0 8.487 0 7.852c0-1 .813-1.812 1.812-1.812.446 0 .87.164 1.2.455 1.24-.796 2.91-1.297 4.75-1.33l1.208-3.69.264.063c.002 0 .004 0 .006.002l2.816.663c.228-.533.757-.908 1.373-.908.822 0 1.49.67 1.49 1.492 0 .823-.668 1.492-1.49 1.492-.823 0-1.492-.67-1.493-1.49l-2.57-.606L8.39 5.17c1.773.07 3.374.572 4.57 1.35.333-.307.767-.48 1.228-.48 1 0 1.812.814 1.812 1.813 0 .665-.354 1.26-.92 1.578.025.166.04.334.04.504-.002 2.63-3.21 4.77-7.153 4.77zM13.43 1.893c-.494 0-.895.4-.895.894 0 .493.4.894.894.894.49 0 .892-.4.892-.893s-.4-.894-.893-.894z" /></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_reddit', $delay, $zero ) : '').($fallback ? '<strong>R</strong>' : '').'</a>';
			break;


			case 'dribbble':
				$link = '<a'.($itemprop ? ' itemprop="url"' : '').' rel="noreferrer" href="https://dribbble.com/'.$page.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg width="'.$width.'" height="'.$height.'" class="dribbble-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414"><path d="M8 16c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm6.747-6.905c-.234-.074-2.115-.635-4.257-.292.894 2.456 1.258 4.456 1.328 4.872 1.533-1.037 2.624-2.68 2.93-4.58zM10.67 14.3c-.102-.6-.5-2.688-1.46-5.18l-.044.014C5.312 10.477 3.93 13.15 3.806 13.4c1.158.905 2.614 1.444 4.194 1.444.947 0 1.85-.194 2.67-.543zm-7.747-1.72c.155-.266 2.03-3.37 5.555-4.51.09-.03.18-.056.27-.08-.173-.39-.36-.778-.555-1.16C4.78 7.85 1.47 7.807 1.17 7.8l-.003.208c0 1.755.665 3.358 1.756 4.57zM1.31 6.61c.307.005 3.122.017 6.318-.832-1.132-2.012-2.353-3.705-2.533-3.952-1.912.902-3.34 2.664-3.784 4.785zM6.4 1.368c.188.253 1.43 1.943 2.548 4 2.43-.91 3.46-2.293 3.582-2.468C11.323 1.827 9.736 1.176 8 1.176c-.55 0-1.087.066-1.6.19zm6.89 2.322c-.145.194-1.29 1.662-3.816 2.694.16.325.31.656.453.99.05.117.1.235.147.352 2.274-.286 4.533.172 4.758.22-.015-1.613-.59-3.094-1.543-4.257z" /></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_dribbble', $delay, $zero ) : '').($fallback ? '<strong>D</strong>' : '').'</a>';
			break;


			case 'stumbleupon':
				$link = '<a'.($itemprop ? ' itemprop="url"' : '').' rel="noreferrer" href="http://www.stumbleupon.com/submit?url='.$url.'&amp;title='.$title.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg width="'.$width.'" height="'.$height.'" class="stumbleupon-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414"><path d="M8 0C3.58 0 0 3.582 0 8c0 4.42 3.58 8 8 8s8-3.58 8-8c0-4.418-3.58-8-8-8zm-.412 5.938v3.3c0 1.236-1.128 2.167-2.3 2.167-1.096 0-2.12-.518-2.236-1.756V7.587h1.65v1.65c0 .41.29.477.585.477.293 0 .65-.066.65-.478v-3.3c.034-1.236 1.053-2.01 2.194-2.01 1.162 0 1.932.878 1.932 2.01v.696l-.818.39-.832-.39V5.526s-.11-.12-.28-.12c-.283 0-.544.12-.544.532zm5.36 3.3c0 1.236-1.06 2.074-2.235 2.074-1.174 0-2.3-.838-2.3-2.075v-1.65h1.65v1.65c0 .412.357.478.65.478.294 0 .586-.066.586-.478v-1.65h1.648v1.65z" fill-rule="nonzero"/></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_stumbleupon', $delay, $zero ) : '').($fallback ? '<strong>S</strong>' : '').'</a>';
			break;


			case 'delicious':
				$link = '<a href="http://del.icio.us/post?url='.$url.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg width="'.$width.'" height="'.$height.'" class="stumbleupon-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414"><path d="M8 8H0v8h8V8zm8-8H8v8h8V0z" /></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_delicious', $delay, $zero ) : '').($fallback ? '<strong>D</strong>' : '').'</a>';
			break;


			case 'instagram':
				$link = '<a'.($itemprop ? ' itemprop="url"' : '').' rel="noreferrer" href="https://instagram.com/'.$instagram.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg width="'.$width.'" height="'.$height.'" class="instagram-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414"><path d="M8 0C5.827 0 5.555.01 4.702.048 3.85.088 3.27.222 2.76.42c-.526.204-.973.478-1.417.923-.445.444-.72.89-.923 1.417-.198.51-.333 1.09-.372 1.942C.008 5.555 0 5.827 0 8s.01 2.445.048 3.298c.04.852.174 1.433.372 1.942.204.526.478.973.923 1.417.444.445.89.72 1.417.923.51.198 1.09.333 1.942.372.853.04 1.125.048 3.298.048s2.445-.01 3.298-.048c.852-.04 1.433-.174 1.942-.372.526-.204.973-.478 1.417-.923.445-.444.72-.89.923-1.417.198-.51.333-1.09.372-1.942.04-.853.048-1.125.048-3.298s-.01-2.445-.048-3.298c-.04-.852-.174-1.433-.372-1.942-.204-.526-.478-.973-.923-1.417-.444-.445-.89-.72-1.417-.923-.51-.198-1.09-.333-1.942-.372C10.445.008 10.173 0 8 0zm0 1.44c2.136 0 2.39.01 3.233.048.78.036 1.203.166 1.485.276.374.145.64.318.92.598.28.28.453.546.598.92.11.282.24.705.276 1.485.038.844.047 1.097.047 3.233s-.01 2.39-.048 3.233c-.036.78-.166 1.203-.276 1.485-.145.374-.318.64-.598.92-.28.28-.546.453-.92.598-.282.11-.705.24-1.485.276-.844.038-1.097.047-3.233.047s-2.39-.01-3.233-.048c-.78-.036-1.203-.166-1.485-.276-.374-.145-.64-.318-.92-.598-.28-.28-.453-.546-.598-.92-.11-.282-.24-.705-.276-1.485C1.45 10.39 1.44 10.136 1.44 8s.01-2.39.048-3.233c.036-.78.166-1.203.276-1.485.145-.374.318-.64.598-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276C5.61 1.45 5.864 1.44 8 1.44zm0 2.452c-2.27 0-4.108 1.84-4.108 4.108 0 2.27 1.84 4.108 4.108 4.108 2.27 0 4.108-1.84 4.108-4.108 0-2.27-1.84-4.108-4.108-4.108zm0 6.775c-1.473 0-2.667-1.194-2.667-2.667 0-1.473 1.194-2.667 2.667-2.667 1.473 0 2.667 1.194 2.667 2.667 0 1.473-1.194 2.667-2.667 2.667zm5.23-6.937c0 .53-.43.96-.96.96s-.96-.43-.96-.96.43-.96.96-.96.96.43.96.96z"></path></svg>' : '').'<b>'.$title.'</b>'.($count ? _pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_instagram', $delay, $zero ) : '').($fallback ? '<strong>I</strong>' : '').'</a>';
			break;

			case 'email':
				$link = '<a rel="nofollow" href="mailto:?subject='.$prefs['sitename'].'&amp;body='.urlencode($text).'%0A%0A'.$url.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 485.4 485.4" xml:space="preserve"><path d="M0 81.8v321.8h485.4V81.8H0zM242.7 280.5L43.6 105.7h398.2L242.7 280.5zM163.4 242.6L23.9 365.2V120.1L163.4 242.6zM181.5 258.5l61.2 53.8 61.2-53.8L441.9 379.7H43.5L181.5 258.5zM322 242.7l139.5-122.5v245.1L322 242.7z"/></svg>' : '').'<b>'.$title.'</b>'.($fallback ? '<strong>I</strong>' : '').'</a>';
			break;


			case 'permalink':
				global $pretext, $plugins;

				// Deals with smd_short_url plugin if exists.
				$rs = safe_row("name, status", "txp_plugin", 'name="smd_short_url" and status="1"');
				if ($rs)
					$url = hu.$pretext['id'];

				$link = '<span class="link-container"><a rel="nofollow" href="'.$url.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" onclick="toggle(\'show-link\');return false"><strong>&#128279;</strong>'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="permalink-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 512 512"><path d="M482.3 210.8L346.5 346.5c-37.5 37.5-98.2 37.5-135.7 0l-45.2-45.2 45.3-45.2 45.3 45.3c12.5 12.5 32.8 12.5 45.3 0L437 165.5c12.5-12.5 12.5-32.8 0-45.3l-45.2-45.2c-12.5-12.5-32.8-12.5-45.2 0l-48.5 48.5c-22.5-13.2-48-18.9-73.3-17.2l76.5-76.5c37.5-37.5 98.3-37.5 135.8 0l45.3 45.3C519.8 112.5 519.8 173.3 482.3 210.8zM213.9 388.6L165.5 437c-12.5 12.5-32.8 12.5-45.2 0L75 391.8c-12.5-12.5-12.5-32.7 0-45.2l135.8-135.7c12.5-12.5 32.8-12.5 45.3 0l45.3 45.3 45.3-45.2 -45.2-45.2c-37.5-37.5-98.2-37.5-135.7 0L29.8 301.3c-37.5 37.5-37.5 98.3 0 135.8L75 482.3c37.5 37.5 98.3 37.5 135.8 0l76.5-76.5C262 407.4 236.5 401.8 213.9 388.6z"/></svg>' : '').'<b>'.$title.'</b></a>'.n.'<input type="text" value="'.$url.'" '.($input_tooltip ? 'title="'.$input_tooltip.'" ' : '').'id="show-link" onclick="this.setSelectionRange(0,9999);return false" readonly></span>'.n.'<script>function toggle(e){var l=document.getElementById(e);l.style.display="block"==l.style.display?"none":"block"}</script>';
			break;

		}

		return ($itemprop ? '<span itemprop="sharedContent" itemscope itemtype="http://schema.org/WebPage" class="pat-social"><span itemprop="headline" class="txt-indent">'.$thisarticle['title'].'</span> <meta itemprop="datePublished" content="'.posted(array('format'=>'%Y-%m-%d')).'" /> '.$link.'</span>' : $link);

	} elseif ( empty($site) ) {

		return trigger_error( gTxt('invalid_attribute_value', array('{name}' => 'site')), E_USER_WARNING );

	} else {

		return;
	}
}


/**
 * Read or create a file with content
 *
 * @param  string  $file  A flat file name
 * @param  string  $url   URI
 * @param  string  $type  A function to call
 * @param  integer $delay Caching time in minutes
 * @param  boolean $zero  Choose to display zero count
 * @return String  File's content
 */
function _pat_article_social_get_content($file, $url = NULL, $type, $delay, $zero) {

	global $path_to_site, $pat_article_social_dir;

	// Proper path & file with extension.
	$file = $path_to_site.'/'.$pat_article_social_dir.'/'.$file.'.txt';

	// Times.
	if($delay <= 0)
	$delay = 1;
	$current_time = time();
	$expire_time = (int)$delay * 60 * 60;

	// Grab content file or create it.
	if ( file_exists($file) && ($current_time - $expire_time <= filemtime($file)) ) {
		// Reading file.
		$out = @file_get_contents($file);
	} else {
		// Check what kind of datas.
		if ( function_exists($type) || $delay == 0 )
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
 * @param  string URLs  Share counts
 * @param  string       Letter format
 * @return integer
 */
// Twitter®
function _pat_article_social_get_twitter($url, $unit = NULL)
{
	$json_string = @file_get_contents('http://opensharecount.com/count.json?url='.$url);
	$json = json_decode($json_string, true);

	if (isset($json['count']) ) 
		$tw = intval($json['count']);
	else
		$tw = 0;

	return $tw;
}
// Facebook®
function _pat_article_social_get_facebook($url) {

	$src = json_decode( @file_get_contents('http://graph.facebook.com/'.$url) );
	$src->share->share_count ? $fb_count = $src->share->share_count : $fb_count = 0;

	return $fb_count;

}
// G+®
function _pat_article_social_get_google($url) {

 	$curl = curl_init();
 	curl_setopt($curl, CURLOPT_URL, "https://clients6.google.com/rpc");
 	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get", "id":"p", "params":{"nolog":true, "id":"'.$url.'", "source":"widget", "userId":"@viewer", "groupId":"@self"}, "jsonrpc":"2.0","key":"p", "apiVersion":"v1"}]');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	$curl_results = curl_exec ($curl);
	curl_close ($curl);
	$json = json_decode($curl_results, true);
	$g_count = intval($json[0]['result']['metadata']['globalCounts']['count']);
	$g_count ? $g_count : $g_count = 0;

	return $g_count;

}
// Pinterest®
function _pat_article_social_get_pinterest($url) {

	$pinfo = json_decode( preg_replace('/^receiveCount\((.*)\)$/', "\\1", @file_get_contents('http://api.pinterest.com/v1/urls/count.json?callback=receiveCount&url='.$url)) );

	if ( isset($pinfo->count) ) return $pinfo->count;

}
// LinkedIn®
function _pat_article_social_get_linkedin($url) {

	$linfo = json_decode( @file_get_contents('https://www.linkedin.com/countserv/count/share?url='.$url.'&amp;format=json') );

	return ( isset($linfo->count) ? $linfo->count : 0 );

}
// Buffer®
function _pat_article_social_get_buffer($url) {

 	$binfo = json_decode( @file_get_contents('https://api.bufferapp.com/1/links/shares.json?url='.$url) );

	if ( isset($binfo->shares) ) return $binfo->shares;

}
// Reddit®
function _pat_article_social_get_reddit($url) {

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
// Dribbble®
function _pat_article_social_get_dribbble($url) {

	global $dribbble_data, $shot;

	if( false === _pat_article_social_occurs($dribbble_data, array('followers', 'likes', 'comments', 'shots')) )
		return trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'dribbble_data')), E_USER_WARNING);

	$data = $dribbble_data.'_count';
	$counter = 0;

	$json = json_decode( @file_get_contents('http://api.dribbble.com/shots/'.$shot) );

	 if ($json) 
		$counter = (int) $json->player->{$data};
	
 	return $counter;

}
// Stumbleupon®
function _pat_article_social_get_stumbleupon($url) {

	$json = json_decode( @file_get_contents('http://www.stumbleupon.com/services/1.01/badge.getinfo?url='.$url), true );
	return isset($json['result']['views']) ? intval($json['result']['views']) : 0;

}
// Delicious®
function _pat_article_social_get_delicious($url) {

	$json = json_decode( @file_get_contents('http://feeds.delicious.com/v2/json/urlinfo/data?url='.$url), true );

	return isset($json[0]['total_posts']) ? intval($json[0]['total_posts']) : 0;

}
// Instagram®
function _pat_article_social_get_instagram() {

	global $user, $token, $instagram_type;

	$json = json_decode( @file_get_contents('https://api.instagram.com/v1/users/'.$user.'/?access_token='.$token) );

	return $json->data->counts->{($instagram_type == 'followers' ? 'followed_by' : 'media')};

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
		'unit'		=> 'k',
		'delay'		=> 3,
		'showalways' 	=> 0,
		'text'		=> false,
		'alternative'	=> '',
		'plural' 	=> 's',
		'lang'		=> $prefs['language'],
		'zero' 		=> false,
		'class' 	=> 'shares',
	), $atts));

	($lang == 'fr-fr') ? $space = '&thinsp;' : '';

	if ( $site ) {

		if ( !gps('txpreview') ) {

			$list = explode( ',', strtolower($site) );
			$n = count($list);

			foreach ( $list as $el ) {
				if ( false == _pat_article_social_occurs($el, $refs) )
					return trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'site')), E_USER_WARNING);
			}

			$sum = 0;

			if( !file_exists($path_to_site.'/'.$pat_article_social_dir.'/'.$thisarticle['thisid'].'-shares.txt') )
				_pat_article_social_get_content( $thisarticle['thisid'].'-shares', '', $sum, $delay, $zero );

			for ($i=0; $i < $n; ++$i)
				if ( file_exists($path_to_site.'/'.$pat_article_social_dir.'/'.$thisarticle['thisid'].'-'.$list[$i].'.txt') ) {
					$sum += @file_get_contents( $path_to_site.'/'.$pat_article_social_dir.'/'.$thisarticle['thisid'].'-'.$list[$i].'.txt' );
				}

			_pat_article_social_get_content( $thisarticle['thisid'].'-shares', '', $sum, $delay, $zero );

			// Check to render a zero value
			$zero ? '' : ($sum > 0 ? '' : $sum = '');

			return ($showalways || $sum > 0) ? tag('<b>'.$text.( ($sum > 1 && $text) ? $plural.$space.':' : '' ).' </b>'._pat_format_count($sum, $unit, $lang), 'span', ' class="'.$class.'"') : ( $zero ? tag('<b>'.$text.( ($sum > 1 && $text) ? $plural.$space.':' : '' ).' </b>'._pat_format_count($sum, $unit, $lang), 'span', ' class="'.$class.'"') : tag('<b>'.$alternative.$space.' </b>', 'span', ' class="'.$class.'"') );

		} else {
			return '';
		}

	} else {

	return trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'site or cache directory')), E_USER_WARNING);

	}
}


/**
 * Check values from a list
 *
 * @param  string $el
 * @param array $array
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
 * @param integer $number
 * @param string  $unit
 * @param string  $lang
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
 * i18n from adi_plugins. Tks ;)
 * @param   $phrase   $atts
 */
function pat_article_social_gTxt($phrase, $atts = array()) {
// will check installed language strings before embedded English strings - to pick up Textpack
// - for TXP standard strings gTxt() & pat_hyphenate_gTxt() are functionally equivalent
	global $pat_article_social_gTxt;

	if (strpos(gTxt($phrase, $atts), $phrase) !== FALSE) { // no TXP translation found
		if (array_key_exists($phrase, $pat_article_social_gTxt)) // translation found
			return strtr($pat_article_social_gTxt[$phrase], $atts);
		else // last resort
			return $phrase;
		}
	else // TXP translation
		return gTxt($phrase, $atts);
}


/**
 * Plugin prefs.
 *
 * @param  $event, $step
 * @return Insert this plugin prefs into 'txp_prefs' table.
 */
function pat_article_social_prefs()
{

	global $textarray;

	$textarray['pat_article_social_dir'] = gTxt('pat_article_social_dir');
	$textarray['pat_article_social_twttr'] = gTxt('pat_article_social_twttr');
	$textarray['pat_article_social_delay'] = gTxt('pat_article_social_delay');

	if (!safe_field ('name', 'txp_prefs', "name='pat_article_social_dir'"))
		safe_insert('txp_prefs', "prefs_id=1, name='pat_article_social_dir', val='cache', type=1, event='admin', html='text_input', position=21");

	if (!safe_field ('name', 'txp_prefs', "name='pat_article_social_twttr'"))
		safe_insert('txp_prefs', "prefs_id=1, name='pat_article_social_twttr', val='1', type=1, event='admin', html='yesnoradio', position=22");

	if (!safe_field ('name', 'txp_prefs', "name='pat_article_social_delay'"))
		safe_insert('txp_prefs', "prefs_id=1, name='pat_article_social_delay', val='24', type=1, event='admin', html='text_input', position=23");

	safe_repair('txp_plugin');

}


/**
 * Delete cache dir in prefs & all files in it.
 *
 * @param
 * @return Delete this plugin prefs.
 */
function pat_article_social_cleanup()
{
	global $path_to_site, $message, $pat_article_social_dir;

	// Array of tables & rows to be removed
	$els = array('txp_prefs' => 'pat_article_social', 'txp_lang' => 'pat_article_social');

	// Process actions
	foreach ($els as $table => $row) {
		safe_delete($table, "name LIKE '".str_replace('_', '\_', $row)."\_%'");
		safe_repair($table);
	}

	//echo graf('The "cache" directory and all its files will be removed. '.gTxt('are_you_sure'));

	//array_map('unlink', glob("'.$path_to_site.'/'.$pat_article_social_dir.'/'*.txt"));

}
