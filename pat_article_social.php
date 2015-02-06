<?php
/*
 * pat_article_social (formerly pat_article_tweet) Textpattern CMS plugin
 * @author:  © Patrick LEFEVRE, all rights reserved. <patrick[dot]lefevre[at]gmail[dot]com>
 * @link:    http://pat-article-social.cara-tm.com
 * @type:    Public
 * @prefs:   no
 * @order:   5
 * @version: 0.4.6
 * @license: GPLv2
*/

// TO DO: ADD SUPPORT FOR EMBEDED @font-face ICONS. ADD SIZE VALUES FROM IMAGES. ADD TWITTER CARD TYPES SELECTION.
//        BACK-OFFICE CHART VISUALISATION OF SHARES BY INDIVIDUAL ARTICLES.

if (txpinterface == 'admin')
{
	register_callback('pat_article_social_prefs', 'prefs', '', 1);
	register_callback('pat_article_social_cleanup', 'plugin_lifecycle.pat_article_social', 'deleted');
}

global $refs;
$refs = array('facebook', 'twitter', 'google', 'pinterest', 'Linkedin', 'buffer');

/**
 * Generate meta tag for social websites
 *
 * @param  array  Tag attributes
 * @return string HTML meta tags
 */
function pat_article_social_meta($atts)
{

	global $prefs, $thisarticle;

	extract(lAtts(array(
		'type'		=> NULL,
		'card' 		=> 'summary',
		'image'		=> NULL,
		'width'		=> NULL,
		'height'	=> NULL,
		'api' 		=> '',
		'locale' 	=> $prefs['language'],
		'user'		=> NULL,
		'creator'	=> NULL,
		'fbauthor' 	=> NULL,
		'gauthor' 	=> NULL,
		'fbpublisher'	=> NULL,
		'gpublisher'	=> NULL,
		'title' 	=> $prefs['sitename'],
		'description' 	=> $prefs['site_slogan'],
	), $atts));


	if ( $type && !gps('txpreview') ) {

		$type = explode(',', $type);
		$locale = preg_replace_callback('(^([a-z]{2})(.*)?([a-z]{2}))i', function($m){return "$m[1]_".strtoupper($m[3]);}, $locale);
		$current = _pat_article_social_get_uri();
		$image ? $image : $image = _pat_article_social_image();
		$description = txpspecialchars($description);

		foreach ($type as $service) {

			switch( strtolower($service) ) {

			case 'twitter':
	$tags = '<meta name="twitter:card" content="summary">'.n;
	$tags .= _pat_article_social_validate_user($user, 'site');
	$tags .= _pat_article_social_validate_user($creator, 'creator');
	$tags .= '<meta property="twitter:image" content="'._pat_article_social_image($image).'">'.n;
	$tags .= <<<EOF
<meta property="twitter:title" content="<txp:if_article_list>{$title}<txp:else /><txp:title no_widow="0" /></txp:if_article_list>">
<meta property="twitter:url" content="$current">
<meta name="twitter:description" content="$description">
EOF;
			break;


			case 'facebook':
	$tags = <<<EOF
<meta property="og:locale" content="$locale">
<meta property="og:site_name" content="{$prefs['sitename']}">
<meta property="og:title" content="<txp:if_article_list>{$title}<txp:else /><txp:title no_widow="0" /></txp:if_article_list>">
<meta property="og:description" content="$description">
<meta property="og:type" content="website">
<meta property="og:url" content="$current">
<meta property="og:image" content="$image">

EOF;
	$tags .= ($api ? '<meta property="fb:app_id" content="'.$api.'">'.n : '');
	$tags .= ($admins ? '<meta property="fb:admins" content="'.$admins.'">'.n : '');
	$tags .= ($fbauthor ? '<meta property="article:author" content="'.$fbauthor.'">'.n : '');
	$tags .= ($fbpublisher ? '<meta property="article:publisher" content="'.$fbpublisher.'">' : ''); 
			break;


			case 'google':
	$tags = <<<EOF
<meta itemprop="name" content="{$prefs['sitename']}">
<meta itemprop="title" content="<txp:if_article_list>{$title}<txp:else /><txp:title no_widow="0" /></txp:if_article_list>">
<meta itemprop="description" content="$description">
<meta itemprop="url" content="$current">
<meta itemprop="image" content="$image">

EOF;

	$tags .= ($gauthor ? '<link rel="author" href="https://plus.google.com/'.$gauthor.'">'.n : '');
	$tags .= ($gpublisher ? '<link rel="publisher" href="https://plus.google.com/'.$gpublisher.'">'.n : '');
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
	if ( preg_match("/\@[a-z0-9_]+/i", $entry) )
		$out = ($attribute ? '<meta name="twitter:'.$attribute.'" content="'.$entry.'">'.n : $entry);

	return $out ? $out : trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'user or creator')), E_USER_WARNING);

}


/**
 * Display article image
 *
 * @param
 * @return URI  Full article image URI
 */
function _pat_article_social_image()
{

	global $thisarticle;

	$img = $thisarticle['article_image'];

	if (intval($img)) {

		if ($rs = safe_row('*', 'txp_image', 'id = ' . intval($img))) {
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


/**
 * Generate links for social websites
 *
 * @param  array   Tag attributes
 * @return String  Link with encoded article body in arguments
 */
function pat_article_social($atts)
{

	global $thisarticle;

	extract(lAtts(array(
		'site'		=> 'permalink',
		'tooltip' 	=> NULL,
		'title'		=> NULL,
		'via'		=> NULL,
		'content' 	=> 'excerpt',
		'class'		=> NULL,
		'icon' 		=> false,
		'width' 	=> '16',
		'height' 	=> '16',
		'count' 	=> false,
		'zero' 		=> false,
		'unit' 		=> 'k',
		'delay' 	=> 3,
		'image' 	=> NULL,
	), $atts));

	if ( $site && !gps('txpreview') ) {

		if( in_array($content, array('excerpt', 'body')) )
			$extract = $thisarticle[$content];
		else
			trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'content')), E_USER_WARNING);

		$url = permlink(array());
		$text = $thisarticle['title'].' '.dumbDown( strip_tags( preg_replace("/\s+/", " ", $extract) ) );
		$minus = strlen($via)+7;
		// Twitter shorten urls: http://bit.ly/ILMn3F
		$words = ($via ? 'via '._pat_article_social_validate_user($via).': ' : '').urlencode( substr($text, 0, 115-$minus) ).'...';

		switch( strtolower($site) ) {


			case 'twitter':
				$link = '<a title="'.$tooltip.'" href="https://twitter.com/share?url='.$url.'&amp;text='.$words.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="twitter-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 16 16" xml:space="preserve"><path d="M16 3.04c-0.59 0.26-1.22 0.44-1.88 0.52 0.68-0.41 1.2-1.05 1.44-1.81 -0.63 0.38-1.34 0.65-2.09 0.8C12.88 1.9 12.02 1.5 11.08 1.5c-1.81 0-3.28 1.47-3.28 3.28 0 0.26 0.03 0.51 0.09 0.75C5.15 5.39 2.73 4.09 1.11 2.1 0.83 2.58 0.67 3.15 0.67 3.75c0 1.14 0.58 2.14 1.46 2.73C1.59 6.46 1.09 6.32 0.64 6.07v0.04c0 1.59 1.13 2.92 2.63 3.22C3 9.4 2.71 9.45 2.41 9.45c-0.21 0-0.42-0.02-0.62-0.06 0.42 1.3 1.63 2.25 3.07 2.28 -1.12 0.88-2.54 1.4-4.08 1.4 -0.26 0-0.53-0.02-0.78-0.04C1.45 13.96 3.18 14.5 5.03 14.5c6.04 0 9.34-5 9.34-9.34L14.36 4.74C15 4.27 15.56 3.7 16 3.04z"></path></svg>' : '').'<b>'.$title.'</b>'.($count ? '  <span>'._pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode( permlink(array()) ), '_pat_article_social_get_twitter', $delay, $zero, $unit ).'</span>' : '').'</a>';
			break;


			case 'facebook':
				$link = '<a href="http://www.facebook.com/sharer.php?u='.$url.'&amp;t='.urlencode( title(array()) ).'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="facebook-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 11.861 23.303"><path d="M11.861,8.323H7.594V6.243c0,0-0.239-1.978,1.144-1.978c1.563,0,2.811,0,2.811,0V0H6.763c0,0-4.005-0.017-4.005,4.005c0,0.864-0.004,2.437-0.01,4.318H0v3.434h2.741c-0.016,5.46-0.035,11.545-0.035,11.545h4.888V11.757h3.226L11.861,8.323z"/></svg>' : '').'<b>'.$title.'</b>'.($count ? '  <span>'._pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode( permlink(array()) ), '_pat_article_social_get_facebook', $delay, $zero, $unit ).'</span>' : '').'</a>';
			break;


			case 'google':
				$link = '<a href="https://plus.google.com/share?url='.$url.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="gplus-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 89.609 88"><path d="M4.754,21.943c0,7.468,2.494,12.859,7.412,16.027c4.025,2.594,8.701,2.981,11.129,2.981c0.59,0,1.062-0.022,1.393-0.044c0,0-0.771,5.016,2.949,9.981l-0.168-0.002C21.023,50.888,0,52.234,0,69.517c0,17.585,19.309,18.478,23.182,18.478c0.303,0,0.48-0.006,0.48-0.006C23.705,87.989,23.982,88,24.453,88c2.486,0,8.912-0.312,14.879-3.213C47.072,81.029,51,74.494,51,65.362c0-8.825-5.982-14.077-10.35-17.916c-2.666-2.338-4.969-4.358-4.969-6.322c0-2.001,1.682-3.505,3.809-5.41c3.445-3.081,6.691-7.466,6.691-15.755c0-7.287-0.945-12.178-6.766-15.281c0.607-0.311,2.752-0.537,3.814-0.684C46.385,3.563,51,3.074,51,0.498V0H28.006C27.775,0.006,4.754,0.859,4.754,21.943zM41.871,67.007c0.439,7.033-5.576,12.222-14.607,12.879c-9.174,0.67-16.727-3.46-17.166-10.483c-0.213-3.374,1.271-6.684,4.176-9.316c2.947-2.669,6.992-4.314,11.393-4.639c0.52-0.033,1.035-0.054,1.549-0.054C35.705,55.394,41.455,60.384,41.871,67.007z M35.867,17.131c2.256,7.934-1.15,16.229-6.578,17.759c-0.623,0.175-1.27,0.265-1.922,0.265c-4.973,0-9.9-5.031-11.725-11.96c-1.02-3.894-0.938-7.296,0.232-10.568c1.15-3.219,3.215-5.393,5.814-6.124c0.625-0.177,1.273-0.267,1.924-0.267C29.617,6.235,33.477,8.725,35.867,17.131z M74.609,34.41v-15h-9.5v15h-15v9.5h15v15h9.5v-15h15v-9.5H74.609z"/></svg>' : '').'<b>'.$title.'</b>'.($count ? '  <span>'._pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode( permlink(array()) ), '_pat_article_social_get_google', $delay, $zero, $unit ).'</span>' : '').'</a>';
			break;


			case 'pinterest':
				$link = '<a href="http://pinterest.com/pin/create/button/?url='.$url.'&amp;description='.$words.'&amp;media='._pat_article_social_image($image).'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="pinterest-icon" x="0" y="0" width="'.$width.'" height="'.$height.'" viewBox="0 0 16.53 21.25"><path d="M6.796,14.03c-0.558,2.926-1.24,5.73-3.258,7.195c-0.623-4.422,0.915-7.743,1.629-11.269C3.949,7.907,5.313,3.781,7.882,4.798c3.159,1.25-2.737,7.622,1.222,8.418c4.135,0.83,5.82-7.173,3.258-9.776c-3.703-3.758-10.78-0.085-9.91,5.295c0.211,1.315,1.57,1.714,0.543,3.531C0.624,11.739-0.083,9.87,0.008,7.377c0.146-4.079,3.666-6.936,7.195-7.331c4.463-0.5,8.652,1.639,9.23,5.837c0.652,4.739-2.014,9.873-6.787,9.504C8.353,15.287,7.81,14.646,6.796,14.03z"/></svg>' : '').'<b>'.$title.'</b>'.($count ? '  <span>'._pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_pinterest', $delay, $zero, $unit ).'</span>' : '').'</a>';
			break;


			case 'tumblr':
				$link = '<a href="http://www.tumblr.com/share/quote?quote='.$words.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="tumblr-icon" x="0px" y="0px" width="'.$width.'" height="'.$height.'" viewBox="0 0 56 92"><path d="M56,86.982c-3.885,1.852-7.402,3.152-10.547,3.898C42.303,91.625,38.895,92,35.236,92c-4.154,0-7.828-0.535-11.023-1.594c-3.191-1.066-5.912-2.58-8.172-4.54c-2.256-1.969-3.82-4.061-4.689-6.274c-0.871-2.215-1.305-5.425-1.305-9.629v-32.27H0V24.678c3.568-1.176,6.631-2.855,9.176-5.051c2.547-2.189,4.59-4.826,6.131-7.9c1.541-3.07,2.6-6.979,3.182-11.727h12.926v23.256h21.574v14.438H31.414v23.594c0,5.333,0.279,8.756,0.842,10.273c0.555,1.512,1.596,2.722,3.109,3.626c2.018,1.22,4.314,1.83,6.902,1.83c4.604,0,9.18-1.517,13.732-4.544V86.982z"/></svg>' : '').'<b>'.$title.'</b></a>';
			break;


			case 'pocket':
				$link = '<a href="http://getpocket.com/edit?url='.$url.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="pocket-icon" x="0px" y="0px" width="'.$width.'" height="'.$height.'" viewBox="0 0 96 96"><g><g><path d="M8.44,10.46c2.73-1.02,5.7-0.95,8.56-1c21,0.09,42,0.07,63,0.01c3.66,0.04,7.82,0.18,10.54,2.99c3.07,2.97,3.05,7.53,3.05,11.49c-0.15,8.59,0.15,17.18-0.21,25.76c-0.59,11.43-5.99,22.54-14.74,29.94c-16.2,13.77-42.06,14.73-58.97,1.67C9.89,73.97,4.11,62.24,2.86,50.19C2.03,40.8,2.57,31.36,2.4,21.95C2.18,17.43,3.83,12.19,8.44,10.46z M21.37,40.57c3.05,5.4,8.31,9.09,12.35,13.71c4.13,3.87,7.45,8.92,12.66,11.42c4.27,1.47,7.56-2.4,10.21-5.12c4.93-5.39,10.22-10.44,15.32-15.67c2.15-2.31,4.72-5.47,3.2-8.81c-1.24-3.93-6.57-5.2-9.62-2.53c-6.36,4.94-10.9,11.88-17.41,16.66c-6.59-5.13-11.17-12.5-18.09-17.22C25.37,29.93,18.81,35.53,21.37,40.57z"/></g></g><g><path class="inner" d="M21.37,40.57c-2.56-5.04,4-10.64,8.62-7.56c6.92,4.72,11.5,12.09,18.09,17.22c6.51-4.78,11.05-11.72,17.41-16.66c3.05-2.67,8.38-1.4,9.62,2.53c1.52,3.34-1.05,6.5-3.2,8.81c-5.1,5.23-10.39,10.28-15.32,15.67c-2.65,2.72-5.94,6.59-10.21,5.12c-5.21-2.5-8.53-7.55-12.66-11.42C29.68,49.66,24.42,45.97,21.37,40.57z"/></g></svg>' : '').'<b>'.$title.'</b></a>';
			break;


			case 'instapaper':
				$link = '<a href="http://www.instapaper.com/hello2?url='.$url.'&amp;title='.urlencode( title(array()) ).'&amp;description='.$words.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="instapaper-icon" x="0px" y="0px" width="'.$width.'" height="'.$height.'" viewBox="0 0 199.22 359.29"><path d="M133.221,52.92c0.01,84.05,0,168.1,0,252.15c-0.07,3.949,0.85,8.08,3.59,11.07c5,5.729,12.42,8.56,19.6,10.42c12.21,3.43,24.939,4.43,37.57,4.85c2.6-0.11,5.299,1.84,5.289,4.59c0.26,6,0.19,12.02,0.051,18.01c0.16,3.35-3.23,5.34-6.25,4.99c-61.021,0.029-122.05-0.05-183.07,0.029c-2.8-0.17-6.21,0.561-8.37-1.68c-1.62-1.67-1.22-4.199-1.35-6.3c0.11-4.97-0.14-9.94,0.13-14.899c-0.02-2.92,2.85-4.961,5.61-4.75c14.34-0.16,28.84-1.471,42.54-5.94c5.9-2.069,11.9-5.03,15.59-10.28c3.09-4.43,2.62-10.07,2.64-15.189c-0.03-82.34-0.01-164.69-0.01-247.04c-0.05-1.81-0.16-3.8-1.44-5.21c-3.39-3.89-8.16-6.14-12.79-8.18c-14.19-5.84-29.28-8.99-44.36-11.55c-2.83-0.6-6.98-0.85-7.64-4.37c-0.56-5.2-0.09-10.46-0.27-15.69c-0.05-2.78-0.11-6.48,3-7.69C6.45-0.51,9.76-0.13,13-0.21C70.34-0.18,127.67-0.2,185.01-0.2c3.86,0.14,7.84-0.45,11.631,0.52c2.969,1.29,2.799,4.97,2.819,7.68c-0.181,5.29,0.34,10.62-0.33,15.89c-0.729,3.18-4.45,3.56-7.11,3.99c-13.02,1.83-26.02,4.28-38.47,8.6c-5.22,1.81-10.399,3.94-15.01,7.02C135.41,45.59,132.82,48.96,133.221,52.92z"/></svg>' : '').'<b>'.$title.'</b></a>';
			break;


			case 'linkedin':
				$link = '<a href="http://www.linkedin.com/shareArticle?mini=true&amp;url='.$url.'&amp;title='.urlencode( title(array()) ).'&amp;source='.urlencode( site_slogan(array()) ).'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="linkedin-icon" x="0px" y="0px" width="'.$width.'" height="'.$height.'" viewBox="0 0 90 88.738"><g><path d="M20.379,9.621c0,5.315-3.924,9.62-10.381,9.62C3.924,19.241,0,14.937,0,9.621C0,4.178,4.051,0,10.252,0C16.455,0,20.254,4.178,20.379,9.621z M0.506,88.738V26.837h19.242v61.901H0.506z"/><path d="M31.262,46.584c0-7.721-0.252-14.176-0.504-19.747h16.707l0.889,8.608h0.379c2.531-4.053,8.734-10.002,19.115-10.002C80.506,25.443,90,33.926,90,52.153v36.585H70.758V54.433c0-7.976-2.785-13.417-9.748-13.417c-5.316,0-8.479,3.67-9.871,7.215c-0.508,1.265-0.635,3.038-0.635,4.811v35.697H31.262V46.584z"/></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>' : '').'<b>'.$title.'</b></a>';
			break;


			case 'buffer':
				$link = '<a href="http://bufferapp.com/add?id=fd854fd5d145df9c&amp;url='.$url.'&amp;text='.urlencode( site_slogan(array()) ).'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" target="_blank">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" width="'.$width.'" height="'.$height.'" viewBox="0 0 979 1000" preserveAspectRatio="xMinYMin meet"><path d="M0 762q11-15 31.5-25.5t49-20 40.5-15.5q19 0 33.5 4.5t33.5 15 25 12.5q47 21 260 119 19 4 35.5 0t39.5-17.5 24-14.5q20-9 76.5-34.5t87.5-39.5q4-2 41.5-21t60.5-24q13-2 27.5 1t23.5 7.5 23 13 18 10.5 15.5 6 18.5 8 11 11q3 4 4 14-10 13-31 24t-51 22-40 16q-43 20-128.5 61.5t-128.5 61.5q-7 3-21 11.5t-23.5 13-25.5 11-27.5 7-29.5-1.5l-264-123q-6-3-32-14t-51.5-22-53.5-24-46.5-23.5-21.5-16.5q-4-4-4-13zm0-268q11-15 31.5-25t50-20 41.5-15q19 0 34 4.5t34.5 15 25.5 13.5q42 19 126.5 58t127.5 59q19 5 37 0.5t39-17 25-14.5q68-32 160-72 11-5 31.5-16.5t38.5-19.5 36-11q16-3 31.5 1t37.5 17 23 13q5 3 15.5 6.5t18 8 11.5 10.5q3 5 4 14-10 14-31.5 25.5t-52.5 22.5-41 16q-48 23-135.5 65t-122.5 59q-7 3-26 14t-29 15-32.5 10-35.5 0q-214-101-260-122-6-3-44-19t-69.5-30-61.5-29.5-34-22.5q-4-4-4-14zm0-267q10-15 31.5-26.5t52.5-22.5 41-16l348-162q30 0 53.5 7t56.5 26 40 22q39 18 117 54.5t117 54.5q4 2 36.5 15t54.5 24 27 20q3 4 4 13-9 13-26 22.5t-43.5 19-34.5 13.5q-47 22-140 66.5t-139 66.5q-6 3-20 11t-23 12.5-25 10.5-27 6-28-1q-245-114-256-119-4-2-63-27.5t-102-46.5-48-30q-4-4-4-13z"/></svg>' : '').'<b>'.$title.'</b>'.($count ? '  <span>'._pat_article_social_get_content( $thisarticle['thisid'].'-'.$site, urlencode(permlink(array()) ), '_pat_article_social_get_linkedin', $delay, $zero, $unit ).'</span>' : '').'</a>';
			break;


			case 'permalink':
				global $pretext, $plugins;

				// Deal with smd_short_url plugin if exists.
				$rs = safe_row("name, status", "txp_plugin", 'name="smd_short_url" and status="1"');
				if ($rs)
					$url = hu.$pretext['id'];

				$link = '<span class="link-container"><a href="'.$url.'" title="'.$tooltip.'" class="social-link'.($class ? ' '.$class : '').'" onclick="toggle(\'show-link\');return false">'.($icon ? '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" class="permalink-icon" x="0px" y="0px" width="'.$width.'" height="'.$height.'" viewBox="0 0 512 512"><path d="M482.25,210.752L346.5,346.502c-37.5,37.5-98.25,37.5-135.75,0l-45.25-45.25l45.25-45.25l45.25,45.25c12.5,12.469,32.781,12.5,45.25,0L437,165.502c12.469-12.484,12.469-32.781,0-45.266l-45.25-45.25c-12.469-12.469-32.781-12.469-45.25,0l-48.469,48.469c-22.469-13.219-48-18.891-73.281-17.188l76.5-76.531c37.5-37.484,98.281-37.484,135.75,0l45.25,45.25C519.75,112.471,519.75,173.268,482.25,210.752z M213.938,388.564L165.5,437.002c-12.5,12.5-32.781,12.469-45.25,0L75,391.752c-12.5-12.469-12.5-32.75,0-45.25l135.75-135.75c12.469-12.469,32.781-12.469,45.25,0l45.25,45.25l45.25-45.25l-45.25-45.25c-37.5-37.484-98.25-37.484-135.75,0L29.75,301.252c-37.5,37.5-37.5,98.281,0,135.75L75,482.252c37.469,37.5,98.25,37.5,135.75,0l76.5-76.5C261.969,407.439,236.5,401.752,213.938,388.564z"/></svg>' : '').'<b>'.$title.'</b></a>'.n.'<input type="text" value="'.$url.'" id="show-link" onclick="select(this);return false" readonly></span>'.n.'<script>function toggle(e){var l=document.getElementById(e);l.style.display="block"==l.style.display?"none":"block"}</script>';
			break;


		}

		return $link;
	}

	return trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'site')), E_USER_WARNING);

}


/**
 * Read or create a file with content
 *
 * @param $file, $url, $type, $delay
 * @return String  File's content
 */
function _pat_article_social_get_content($file, $url = NULL, $type, $delay, $zero) {

	global $path_to_site, $pat_article_social_dir;

	// Proper path & file with extension.
	$file = $path_to_site.'/'.$pat_article_social_dir.'/'.$file.'.txt';

	// Times.
	$current_time = time();
	$expire_time = (int)$delay * 60 * 60;

	// Grab content file or create it.
	if ( file_exists($file) && ($current_time - $expire_time < filemtime($file)) ) {
		// Reading file.
		$out = @file_get_contents($file);
	} else {
		// Check what kind of datas.
		if ( function_exists($type) )
			$out = $type($url);
		else
			$out = $type;
		// Write or create file.
		file_put_contents($file, $out);

	}

	return $zero ? $out : ( (int)$out > 0 ? $out : '' );

}


/**
 * Get social counts.
 *
 * @param  String Integer URLs  Share counts
 * @return integer
 */

// Twitter
function _pat_article_social_get_twitter($url, $unit = NULL)
{
	$json = json_decode( @file_get_contents('http://urls.api.twitter.com/1/urls/count.json?url='.$url), true );

	if (isset($json['count']) ) 
		$tw = intval($json['count']);
	else
		$tw = 0;

	return $tw;
}
// Facebook
function _pat_article_social_get_facebook($url, $unit = NULL)
{
	$src = json_decode( @file_get_contents('http://graph.facebook.com/'.$url) );
	$src->shares ? $fb_count = $src->shares : $fb_count = 0;

	return $fb_count;
}
// G+
function _pat_article_social_get_google($url, $unit = NULL)
{
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
// Pinterest
function _pat_article_social_get_pinterest($url, $unit = NULL)
{
	$pinfo = json_decode(preg_replace('/^receiveCount\((.*)\)$/', "\\1", @file_get_contents('http://api.pinterest.com/v1/urls/count.json?callback=receiveCount&url='.$url)));

	if ( isset($pinfo->count) ) return $pinfo->count;
}
// LinkedIn
function _pat_article_social_get_linkedin($url, $unit = NULL)
{
	$linfo = json_decode( @file_get_contents('https://www.linkedin.com/countserv/count/share?url='.$url.'&amp;format=json') );

	if ( isset($linfo->count) ) return $linfo->count;
}
// Buffer
function _pat_article_social_get_buffer($url, $unit = NULL)
{
	$binfo = json_decode( @file_get_contents('https://api.bufferapp.com/1/links/shares.json?url='.$url) );

	if ( isset($binfo->shares) ) return $binfo->shares;
}


/**
 * Sum of share counts
 *
 * @param  $atts array
 * @return String  HTML tag
 */

function pat_article_social_sum($atts) {

	global $prefs, $path_to_site, $pat_article_social_dir, $thisarticle;

	extract(lAtts(array(
		'site'		=> NULL,
		'unit'		=> 'k',
		'delay'		=> 3,
		'showalways' 	=> 0,
		'text'		=> 'Total share',
		'plural'	=> 's',
		'lang'		=> $prefs['language'],
		'zero' 		=> false,
	), $atts));

	if ( $site && !gps('txpreview') ) {

		($lang == 'fr-fr') ? $space = '&thinsp;' : '';

		$sum = 0;

		if( !file_exists($path_to_site.'/'.$pat_article_social_dir.'/'.$thisarticle['thisid'].'-shares.txt') ) {
			_pat_article_social_get_content( $thisarticle['thisid'].'-shares', '', $sum, $delay, $zero );
		}

		$list = explode( ',', strtolower($site) );
		$n = count($list);

		foreach ( $list as $el ) {
			if ( _pat_article_social_occurs($el) === false )
				return trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'site')), E_USER_WARNING);
		}

		for ($i=0; $i < $n; ++$i)
			if ( file_exists($path_to_site.'/'.$pat_article_social_dir.'/'.$thisarticle['thisid'].'-'.$list[$i].'.txt') ) {
				$sum += @file_get_contents( $path_to_site.'/'.$pat_article_social_dir.'/'.$thisarticle['thisid'].'-'.$list[$i].'.txt' );
			}

		_pat_article_social_get_content( $thisarticle['thisid'].'-shares', '', $sum, $delay, $zero );

		// Check to render a zero value
		$zero ? '' : ($sum > 0 ? '' : $sum = false);

	return ($showalways || $zero) ? tag('<b>'.$text.($sum > 1 ? $plural : '').$space.': </b>'._pat_format_count($sum, $unit), 'span', ' class="shares"') : '';

	} else {
		return;
	}

	return trigger_error(gTxt('invalid_attribute_value', array('{name}' => 'site or cache directory')), E_USER_WARNING);

}


/**
 * Check values from a list
 *
 */
function _pat_article_social_occurs($array)
{
	// $refs is an array outside this func.
	global $refs;

	return in_array($array, $refs);

}


/**
 * Format count results
 *
 * @param  $number $unit
 * @return String rounding up (e.g. 3K)
 */

function _pat_format_count($number, $unit) {

	if($number >= 1000)
		return round($number/1000, 1, PHP_ROUND_HALF_UP).$unit;
	else
		return $number;

}


/**
 * Plugin prefs: entry for cache dir.
 *
 */

function pat_article_social_prefs()
{
	global $textarray;

	$textarray['pat_article_social_dir'] = 'Cache directory';

	if (!safe_field ('name', 'txp_prefs', "name='pat_article_social_dir'"))
		safe_insert('txp_prefs', "prefs_id=1, name='pat_article_social_dir', val='cache', type=1, event='admin', html='text_input', position=21");

	safe_repair('txp_plugin');

}


/**
 * Delete cache dir in prefs & all files in it.
 *
 */
function pat_article_social_cleanup()
{
	global $path_to_site, $pat_article_social_dir;

	array_map('unlink', glob("'.$path_to_site.'/'.$pat_article_social_dir.'/'*.txt"));
	safe_delete('txp_prefs', "name='pat_article_social_dir'");

}
