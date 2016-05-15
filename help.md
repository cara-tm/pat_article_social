
#pat_article_social v 0.6.0/0.6.1 (formerly pat_article_tweet)

##Purpose

**Create social links for your articles (support for facebook, Twitter, G+, Pinterest, Tumblr, Pocket, Instapaper, Linkedin, Reddit, Dribbble, Stumbleupon, Delicious & Instagram) with an icon (optional and in SVG format) a share counting (optional) plus all needed Open Graph meta tags for your head document. No cookies, no javascript: UE compiliant. Allow embedded social posts into your article's body from Twitter, fb, G+, Instagram and Gist and create quotations (part of texts) followed by share links. Google AMP capable (v 0.6.1)**

~~Note: no more Twitter share counts since v 0.5.4 (see: [Twitter official announce](https://blog.twitter.com/2015/hard-decisions-for-a-sustainable-platform))~~. No more with v 0.5.7: a third part service is used to display the Twitter share counts (see below).

##Plugin Preferences

After installation, choose "Admin"->"Preferences" tab to access this plugin prefs and to set the "cache" directory. Default: /root/cache; the default markup for the short txp:twttr tag; the default delay to cache the share counts. 

Note: you need to create the cache directory; this plugin doesn't.

##Uninstallation

You can safely remove this plugin without changes in your database except all the content into the cache directory field.

##Usages

Notice: All the following pat_article_social tags are intented to be used as single tags, not container ones. Previews of articles don't show the renderings of this plugin, voluntarily. Depending of the number of social links (each are individual TXP tags) and the value of the "delay" attribute when the counting feature is chosen, pages rendering can be a little bit slow when catching time is over. In all cases, you can take advantages to use the [asy_jpcache](https://github.com/netcarver/asy_jpcache) plugin or [aks_cache](http://makss.uaho.net/plugins/aks_cache).

##1.° In your doctype HTML document (before &lt;/head&gt;)

##Creates HTML meta tags for the social website:##

    <txp:pat_article_social_meta type="" card="" image="" user="" creator="" label1="" data1="" label2="" data2="" locale="" fb_api="" fb_admins="" fb_type="" fb_author="" fb_publisher="" g_author="" g_publisher="" title="" description="" lenght="" />

Important Note: facebook **use the first occurrence** of Open Graph meta tags in the HTML document even if specific ones exist (i.e.: Twitter Open Graph). You are strongly encouraged to call facebook first and all other social networks after it (see: "type" attribute below).

##Quick Start Example

    <!DOCTYPE html>
    <html lang="<txp:lang />" dir="<txp:text item="lang_dir" />">

    <head>
    <meta charset="utf-8">
    <title><txp:page_title /></title>
    <meta name="generator" content="Textpattern CMS">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow, noodp, noydir">
    <txp:pat_article_social_meta type="twitter,facebook,google" card="summary_large_image" fb_publisher="Mark-Zuckerberg" fb_author="Mark-Zuckerberg" g_publisher="+PatrickLEFEVRE-lowel" g_author="+PatrickLEFEVRE-lowel" description='<txp:if_individual_article><txp:body /></txp:if_individual_article>' user="@lowel" creator="@lowel" />


###Attributes

>    **type**: String (required). Coma separated list of social network's name [1]. Default: none (empty).
> 
>    **card**: String (optional). Twitter card type among: "summary", "summary_large_image", "product". Default: summary. Note "gallery" had been removing on July 3, 2015 https://dev.twitter.com/cards/types/gallery. This plugin takes care of images associated with the current article and changes the Twitter's card format accordingly (changed to "summary_large_image"). Basically, in normal contexts you just need to set the card attribute with "summary" or keep it blank.
    ~~Notice: If there are more than one image associated with the current article, the value of the card attribute changes accordingly.~~
> 
>    **image**: URL (optional). Full path to a custom image for replacement. Default: article image.
> 
>    **user**: String (optional). Twitter user account (with a @ prefix). Default: none (empty).
> 
>    **creator**: String (optional). Twitter user account of the site creator (with a @ prefix). Default: none (empty).
    Notice: Twitter accounts are case sensitive.
> 
>    **label1**: String (optional). Twitter Open Graph meta tag for "product" type (see: card attribute). Default: empty (none).
> 
>    **data1**: String (optional). Twitter Open Graph meta tag for "product" type (see: card attribute). Default: empty (none).
> 
>    **label2**: String (optional). Twitter Open Graph meta tag for "product" type (see: card attribute). Default: empty (none).
> 
>    **data2**: String (optional). Twitter Open Graph meta tag for "product" type (see: card attribute). Default: empty (none).
>
>    **locale**: String (required). i18n support. Set the country language code. Notice: the two first letters are lowercases; the two last letters are uppercases (i.e. en_US). Default: locale TXP prefs.
> 
>    **fb_api**: String (optional). API Key for facebook only. Default: none (empty).
> 
>    **fb_admins**: String (optional). Admin facebook page ID. Default: none (empty).
> 
>    **fb_type**: String (optional). The type of website that you would like your website to be categorized by facebook. Default: website.
> 
>    **fb_author**: String (optional). Personal author's facebook page URL. Default: none (empty).
> 
>    **fb_publisher**: String (optional). Personal author's facebook page URL. Default: none (empty).
> 
>    **g_author**: String (optional). Personal author's Google + page URL. Default: none (empty).
> 
>    **g_publisher**: String (optional). Website author's profile page on G+. Notice: there is no validation for this content attribute. Default: none (empty).
    Examples: publisher="me" & publisher="+me" generate: <meta property="article:publisher" content="http://www.facebook.com/me"> & <link rel="publisher" href="http://google.com/+me">.
> 
>    **title**: String (optional). Title (event sensitive: individual article title or site name in article list context). Default: preferences site name.
> 
>    **description**: String (optional). Short description of the page (200 characters maximum). Default: page title.
> 
>    **lenght**: Integer (optional). As recommanded by several Social Networks, limits the lenght (in characters) for the "description" attribute. Don't cut words, add hyphens after the last word found just before a space within the characters limit. Default: 200.
> 

Note: to display the Twitter share counts, you need to add the Opensharecount service in you Twitter account. See more here: [http://opensharecount.com](http://opensharecount.com).

##2.° In an article form (individual articles)

###Creates an HTML link for the current article. Allow visitors to publish a link to their social accounts:

    <txp:pat_article_social site="" tooltip="" input_tooltip="" title="" text="" content="" itemprop="" via="" shot="" page="" icon="" class="" width="" height="" count="" real="" instagram="" user="" token="" zero="" unit="" delay="" image="" fallback="" />

###Quick Start Example

    <txp:hide>
    Display summ of shares. Set which social networks you want to add in the sum.
    </txp:hide>
    <txp:pat_article_social_sum site="twitter,facebook,google,pinterest,reddit" lang="en-us" showalways="0" text='<txp:body />' title"Total of share" itemprop="1" alternative="Share" count="1" zero="0" delay="24" unit="k" />
    
	<txp:hide>
	Display social networks links. Note "delay" attribute can be different for each website.
	</txp:hide>
	<txp:pat_article_social site="twitter" user="@Pourtester" creator="@Pourtester" tooltip=" Tweet it " title="Twitter" content="body" via="@Pourtester" icon="1" class="twitter" count="1" zero="0" delay="24" />
    <txp:pat_article_social site="facebook" tooltip=" Share it " title="facebook" content="body" icon="1" class="facebook" count="1" zero="0" delay="24" />
    <txp:pat_article_social site="google" tooltip=" Share it " title="G+" content="body" icon="1" class="google" count="1" zero="0" delay="24" />
    <txp:pat_article_social site="pinterest" tooltip=" Pin it " title="Pinterest" content="body" icon="1" class="pinterest" count="1" zero="0" delay="24" />
    <txp:pat_article_social site="instagram" tooltip=" Share to Instagram " title="Instagram" content="body" icon="1" class="instagram" instagram="simplebits" user="123" token="123.abc.345" count="1" zero="0" delay="24" />
    <txp:pat_article_social site="reddit" tooltip=" Share to Reddit " title="Reddit" content="body" icon="1" class="reddit" count="1" zero="0" delay="24" />
    <txp:pat_article_social site="dribbble" tooltip=" See my Dribbble page " title="Dribbble" content="body" icon="1" class="dribbble" shot="1945713" dribbble_data="followers" page="simplebits" count="0" zero="0" delay="0" />
    <txp:pat_article_social site="delicious" tooltip=" Share to Delicious " title="Delicious" content="body" icon="1" class="delicious" count="1" zero="0" delay="24" />
    <txp:pat_article_social site="stumbleupon" tooltip=" Share to Stumbleupon " title="Stumbleupon" content="body" icon="1" class="stumbleupon" count="1" zero="0" delay="24" />
    <txp:pat_article_social site="linkedin" tooltip=" Share to Linkedin " title="Linkedin" content="body" icon="1" class="linkedin" count="1" zero="0" delay="24" />
    <txp:pat_article_social site="buffer" tooltip=" Save to Buffer " title="Buffer" content="body" icon="1" class="buffer" count="1" zero="0" delay="24" />
    <txp:pat_article_social site="instapaper" tooltip=" Read later " title="Instapaper" content="body" icon="1" class="instapaper" count="1" zero="0" delay="24" />
    <txp:pat_article_social site="pocket" tooltip=" Save to Pocket " title="Pocket" content="body" icon="1" class="pocket" count="1" zero="0" delay="24" />
    <txp:pat_article_social site="permalink" class="permalink" tooltip=" Permalink " title="Permalink" icon="1" />

If you don't want to display the default SVG icons, you can replace all one by a font. Best choose is fontAwesome.
Add this line into your &lt;head&gt; document part:

    <link href="https://opensource.keycdn.com/fontawesome/4.5.0/font-awesome.min.css" rel="stylesheet">

Then, set the "icon" attribute to 0 value and set the "class" attribute with the fontAwesome one.

###Attributes

>    **site**: String (required). A comma separated list of social websites (facebook, twitter, google, pinterest, tumblr, instapaper, pocket, linkedin, buffer, reddit, dribbble, stumbleupon, delicious, ello). Default: permalink. Notice: "permalink" attribute creates a show/hide input bellow the icon with the current article's permalink.
> 
>    **tooltip**: String (optional). Tooltip of the link. Default: none (empty).
> 
>    **input_tooltip**: String (optional). Tooltip for the permalink input. Default: none (empty).
    Note: this tag return full article permlink URL but deals with smd_short_url plugin if installed and activated (short article URL).
> 
>    **title**: String (optional). Title of the link. Default: none (empty).
> 
>    **content**: String (optional). body or excerpt. Choose what to add as a short text2 (only for Twitter). Default: article title.
>
>    **itemprop**: boolean (optional). Injects 'itemprop' attributes into the HTML result for share links. Default: 1 (true). Note; validate your pages with the Google official online tool: https://developers.google.com/structured-data/testing-tool/
> 
>    **via**: String (optional). For Twitter only, add a "via" link into the tweet. Default: none (empty).
>
>    **shot**: Integer (optional). For Dribbble only, set the number of one of your shot (see your Dribbble URL content to find it, never mind which one you choose). Default: none (empty).
> 
>    **page**: String (optional). For Dribbble only, set your account user name (see your Dribbble URL content to find it). Default: none (empty).
> 
>    **class**: String (optional). Additional CSS class attribute for the link. Default: none (empty).
    icon: Boolean (0 or 1 - optional). Display the official social icon (see format attribute below). Default: "0" (no icon).
> 
>    **format**: String (optional). Choose which icon format between "svg" or "font" (@font-face). Default: svg.
>
>    **width**: Integer (optional). Width of the icon. Default: "16" (16px).
> 
>    **height**: Integer (optional). Height of the icon. Default: "16" (16px).
> 
>    **alternative*** String (optional). The text to display as a label when there are no count (i.e.: alternative="ShareNow:"). Default: empty.
> 
>    **count**: Boolean (0 or 1 - optional). Display article share count value 3. Default: "0" (no counts). No count for Tumblr, Pocket neither Instapaper. Notice: share count results are in a catch for 3 hours by default in order to preserve your website speed against external calls to social networks.
> 
>    **real**: Boolean (0 or 1 - optional). For Reddit only: choose to minus counts by "down" shares. Default: false (0 - retrieve full scores without "downs").
> 
>    **instagram**: String (required). Your Instagram page (i.e.: instagram="simplebits"). Default: empty.
> 
>    **user**: Integer (required). Your user ID (see below).
> 
>    **token**: String (required). Your token. User ID & token can be founnd here: http://www.pinceladasdaweb.com.br/instagram/access-token 
> 
>    **zero**: Boolean (0 or 1 - optional). Choose to display 0 for empty counts. Default: false (no zero).
> 
>    **unit**: String (optional). Unit to display after the count. Default: "k".
> 
>    **delay**: Integer (optional). Catching delay for article share counts results in hours. Default: 3 (hours).
> 
>    **image**: String (optional). Only in use for Pinterest links (Twitter & facebook use Open Graph meta tags instead). It can be a TXP form suitable for example with watermark solution. Default: the current article image. 
> 
>    **fallback**: Boolean (optional). Choose to display the social website first letter as a fallback for browsers which do not support SVG format. Default: 1 (true), show first letters. 
> 
>    **campaign**: String (optional). Choose to add some queries in the URLs for tracking campaigns. Default: none (empty).
> 
>    **with_title**: Boolean (optional). Choose to add the article title into the share content. Default: 1 (yes).
> 

Notice: Default color icons are black. See below how to change it.

###Removing the fallback social network names to keep only icons

CSS rules:

    .social-link b,
      .social-link strong {
    	overflow: hidden;
    	position: absolute;
    	width: 1px;
    	height: 1px;
    	padding: 0;
    	border: none;
    	clip: rect(1px 1px 1px 1px);
    }

##3.° Creates a total shares count as shown in the Mashable.com website:

###Whatever place you want (individual or article lists)

    <txp:pat_article_social_sum site="" lang="" showalways="" text="" plural="" alternative="" count="" zero="" delay="" unit="" />

###Quick Start Example

    <txp:pat_article_social_sum site="twitter,facebook,google,pinterest,reddit" lang="en-us" showalways="0" text="Total of share" plural="s" alternative="Share" count="1" zero="0" delay="24" unit="k" />

###Attributes

>    **site**: String (required). Comma separated list of social websites to be added in the share counting (among: twitter, facebook, google, pinterest). Default: none (empty).
> 
>    **unit**: String (optional). Unit for the count. Default: k.
> 
>    **delay**: Integer (optional). Delay in hours for the cache. Default: 3 (hours).
> 
>    **text**: String (optional). Text to display in front of the count. Default: "Total share".
> 
>    **showalways**: Boolean (optional). Always show or not this tag rendering. Default: 0 (no).
> 
>    **plural**: String (optional). The final letter for plural with the "text" attribute. Default: "s".
> 
>    **lang**: String (optional). i18n support for the space before colon (typographic rule). If set, overwrites prefs. Default: TXP language prefs. (i.e. "fr-fr").
> 
>    **zero**: Boolean (optional). Render or not zero value. Default: 0 (0 = no; 1 = yes).
> 

![pat-article-social sneak peek](http://postimg.org/image/fn8alxwj5/)!

#Incorporate a quotation as a part of text (extract) in your article's body followed by social shares (facebook, twitter & G+)

##Usage:

    <txp:bq text="" />

**Warning**: you can't use quote signs in you input (`"`) but Textile converted ones (`“` and `”` or `«` and `»`) otherwise this tag will not render anything.
Some attentive users may invoke the use of `.notextile` (i.e. `.notextile <txp:bq text="" />`) for a better markup. But that is not important: basically, Google considers a perfect pages structure suspect without any errors.

##Attributes

> 
>    **text** String (required). The text content to display as a blockquote. Default: none (empty).
> 
>    **tooltip** String (optional). The tooltip for each social icon links. Default: none (empty).
>

##HTML markup rendering

Generates this into your HTML page:


    <blockquote class="pat-bq">
     	<p>My text followed by social shares.</p>
     	<p>
     		<span itemprop="sharedContent" itemscope="" itemtype="http://schema.org/WebPage" class="pat-social">
     			<span itemprop="headline" class="txt-indent">Welcome to your site</span> 
     			<meta itemprop="datePublished" content="2016-01-06"> 
     				<a itemprop="url" href="http://www.facebook.com/sharer/sharer.php?u=http://example.com/welcome-to-your-site&amp;t=Welcome+to+your+site" title="" class="social-link facebook" target="_blank"><svg width="16" height="16" class="facebook-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414"><path d="M15.117 0H.883C.395 0 0 .395 0 .883v14.234c0 .488.395.883.883.883h7.663V9.804H6.46V7.39h2.086V5.607c0-2.066 1.262-3.19 3.106-3.19.883 0 1.642.064 1.863.094v2.16h-1.28c-1 0-1.195.476-1.195 1.176v1.54h2.39l-.31 2.416h-2.08V16h4.077c.488 0 .883-.395.883-.883V.883C16 .395 15.605 0 15.117 0" fill-rule="nonzero"></path></svg><b></b><strong>F</strong></a>
     			</span>
     			<span itemprop="sharedContent" itemscope="" itemtype="http://schema.org/WebPage" class="pat-social">
     				<span itemprop="headline" class="txt-indent">Welcome to your site</span> 
     				<meta itemprop="datePublished" content="2016-01-06"> 
     					<a itemprop="url" href="https://plus.google.com/share?url=http://example.com/txp/articles/welcome-to-your-site" title="" class="social-link google" target="_blank"><svg width="16" height="16" class="gplus-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="1.414"><g><path d="M5.09 7.273v1.745H7.98c-.116.75-.873 2.197-2.887 2.197-1.737 0-3.155-1.44-3.155-3.215S3.353 4.785 5.09 4.785c.99 0 1.652.422 2.03.786l1.382-1.33c-.887-.83-2.037-1.33-3.41-1.33C2.275 2.91 0 5.184 0 8s2.276 5.09 5.09 5.09c2.94 0 4.888-2.065 4.888-4.974 0-.334-.036-.59-.08-.843H5.09zM16 7.273h-1.455V5.818H13.09v1.455h-1.454v1.454h1.455v1.455h1.455V8.727H16"></path></g></svg><b></b><strong>G</strong>
     					</a>
     			</span>
     			<span itemprop="sharedContent" itemscope="" itemtype="http://schema.org/WebPage" class="pat-social">
     				<span itemprop="headline" class="txt-indent">Welcome to your site</span> 
     				<meta itemprop="datePublished" content="2016-01-06"> 
     					<a itemprop="url" href="https://twitter.com/intent/tweet?text=My+text+followed+by+social+share.&amp;url=http://example.com/txp/articles/welcome-to-your-site" title="" class="social-link twitter" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="twitter-icon" x="0" y="0" width="16" height="16" viewBox="0 0 16 16" xml:space="preserve"><path d="M16 3c-0.6 0.3-1.2 0.4-1.9 0.5 0.7-0.4 1.2-1 1.4-1.8-0.6 0.4-1.3 0.7-2.1 0.8C12.9 1.9 12 1.5 11.1 1.5c-1.8 0-3.3 1.5-3.3 3.3 0 0.3 0 0.5 0.1 0.8C5.2 5.4 2.7 4.1 1.1 2.1 0.8 2.6 0.7 3.2 0.7 3.8c0 1.1 0.6 2.1 1.5 2.7C1.6 6.5 1.1 6.3 0.6 6.1v0c0 1.6 1.1 2.9 2.6 3.2C3 9.4 2.7 9.5 2.4 9.5c-0.2 0-0.4 0-0.6-0.1 0.4 1.3 1.6 2.3 3.1 2.3-1.1 0.9-2.5 1.4-4.1 1.4-0.3 0-0.5 0-0.8 0C1.5 14 3.2 14.5 5 14.5c6 0 9.3-5 9.3-9.3L14.4 4.7C15 4.3 15.6 3.7 16 3z"></path></svg><b></b><strong>T</strong>
     					</a>
     				</span>
     			</p>
     		</blockquote>
 


##Example of CSS for a quick start usage

> 
>    .pat-bq {
>    	margin: 2em 0 4em;
>    	padding-left: 5px;
>    	border-left: 5px solid royalblue;
>    }
> 


#Embedded statuses in article's bodies

Note: Starting with version 0.5.7, this plugin stores the default delay time (in hours) to cache the share counts into the Preferences panel. Set it for all the social networks you use. If you want to restore individually the "delay" attribute in your tags, set this preference with a blank value. 
In order to center your embedded statuses (best visual display), add these CSS rules into your stylesheets:

    .twitter-tweet,
       .fb_iframe_widget {
		display: block!important;
		overflow-y: hidden;
		max-width: 500px;
		margin: 0 auto;
	}
	
	.fb_iframe_widget {
		max-width: 552px;
	}

All embedded statuses are "Responsive": but not on window resize events, only when a page is loaded.

##4.° Insert a Tweet into your article's body

Just use this line into your article's body (Textile rendering):

    <txp:twttr status="" markup=""  media="" related"" locale="" />

~~Note: Because this plugin remove multiple widgets.js files integration within the embedded tweets, you need to add this script into your HTML document just before the last `</body>` tag into your page template:~~ Not more needed since v 0.5.7

~~&lt;script async src="//platform.twitter.com/widgets.js" charset="utf-8"&gt;&lt;/script&gt;~~

###Attribute

>    **status**: String (required). The link of the Tweet to embed (given by Twitter). Default: none (empty).
>    Note: since v 0.5.1, this attribute can be set with full link URL or a short one (i.e.: status="601628600098488320" with only the tweet id)
>
>    **markup**: String (optional). Choose which kind of embedded markup to use (blockquote, iframe or object). Default: "blockquote" or empty in order to render embbedded tweets from Twitter json services.
>
>    **media**: boolean (optional). Choose not to add medias support into embedded tweets. Default: 0 (medias are shown).
>
>    **related**: boolean (optional). Choose to not add related tweets into embedded tweets. Default: 0 (related are shown).
>
>    **locale**: string (optional). The code language (in 2 letters) to translate embbedded tweets. Default: prefs language (for the TXP administration interface).
> 


###Example

    <txp:twttr status="https://twitter.com/txpfr/status/601628600098488320" />

###Shorter Form Example: embedded tweet from Twitter json services (with the first Tweet of the Internet history)

    <txp:twttr status="20" />

This tag supports long URLs link, partial and short. Both works exactly the same, `<txp:twttr status="https://twitter.com/CNBC/status/593087360243490816?ref_src=twsrc^tfw" />` and `<txp:twttr status="593087360243490816" />` even `<txp:twttr status="https://twitter.com/CNBC/status/593087360243490816" />`



###CSS layout (for "iframe" and "object" markup only. See "markup" attribute above)

All embedded Tweets except the short forms are wrapped into a div with a class selector named "pat-twttr".

The following CSS rules allow embedded Tweets to be "Responsive" friendly:

    .pat-twttr {
    	overflow: hidden;
    	position: relative;
    	width: 500px;
    	max-width: 100%;
    	min-width: 220px;
    	height: 15em;
    	margin: 7px auto
    }

    .pat-twttr iframe, .pat-twttr object{
    	position: absolute;
    	top: 0;
    	left: 0;
    	width: 100%;
    	height: 100%
    }

When you use the short form which render embedded tweets from Twitter json services, the markup is sanitized from deprecated properties in order to keep valid HTML pages. Webdesigners can center all tweets by the use of this only and simple CSS rule:

    .twitter-tweet-rendered { margin-right: auto !important; margin-left: auto !important; }

##5.° Insert an embedded facebook status into your article's body

Just insert this line into your article's body (Textile rendering):

    <txp:fb status="" />

###Attribute

> 
>    `status`string (required). The link URL of a __public__ facebook status. Default: none (empty).
> 

###Example

    <txp:fb status="https://www.facebook.com/zuck/posts/10102329188394581" />



##6.° Insert an embedded G+ post into your article's body

Just insert this line into the article's body (Textile rendering):

    <txp:gplus status="" />

###Attribute

> 
>    `status` string (required). The link URL of a G+ post. Default: none (empty).
> 

###Example

    <txp:gplus status="https://plus.google.com/+google/posts/eTmjj1arLBq" />


##7.° Insert an embedded Instagram image into your article's body

Just insert this line into your articl's body (Textile rendering):

    <txp:instagram status="" />

###Attribute

> 
>    `status` string (required). The URL link to the Instagram image (present into the address bar). Default: none (empty).
> 

###Example

    <txp:instagram status="https://instagram.com/p/1vpa3Sh-1G/?taken-by=rodinature" />



##8.° Insert an embedded Gist code into your article's body

Just insert this line into your article's body (Textile rendering):

    <txp:gist url="" />

###Attribute

> 
>    `url` string (required). The URL link to a Gist from GitHub (present into the address bar). Default: none (empty).
> 

###Example

    <txp:gist url="https://gist.github.com/cara-tm/15589a11130949510e25" />


#9.° Google AMP support

This plugin version 0.6.1 is AMP capable with the help of this plugin compagnon: [Download this plugin](https://github.com/cara-tm/mkp_if_amp)

How to?

First, install and activate the mkp_if_amp v 3.1 plugin.
Then, add this into your HTML head part:

    <txp:mkp_if_amp /><txp:if_variable name="mkp_amp" value="1"><txp:amp_social_script site="" /></txp:if_variable>

That's all. The pat-article-social plugin check the AMP context and serves and render the proper tags and ressources "AMPowered".

Attribute

    **site** String (required): A comma separated list of social network among: facebook,twitter,instagram. Default: none (empty).



#10.° CSS layout for social links

The links can easily been designed. Here is all CSS classes available for your purpose:

    .social-link: class attribute for the links.
    .social-link.twitter, .social-link.facebook, .social-link.google, .social-link.pinterest, .social-link.tumblr, .social-link.instapaper, .social-link.buffer, .social-link.pocket, .social-link.linkedin, .social-link.reddit, .social-link.stumbleupon, .social-link.delicious, .social-link.ello, .social-link.instagram, .social-link.vimeo, .social-link.permalink: individual link class attribute.
    .social-link svg: class attribute for icons.
    .twitter-icon path, .facebook-icon path, .gplus-icon path, .pinterest-icon path, .tumblr-icon path, .instapaper-icon path, .buffer path, .pocket-icon path, .pocket-icon path.inner, .linkedin-icon path, .reddit-icon path, .stumbleupon-icon path, .delicious-icon path, .instagram-icon path, .vimeo-icon path, .permalink-icon path: class attribute to colorize the logos.
    .social-link span: class attribute for counters.
    .social-link b: class attribute for the social network name.
    .social-link strong: class attribute for the social network's first letter name (fallback for browsers which don't support SVG format).

##CSS layout examples

Color palettes for social websites are available here: designpieces.com

###1°. This beautiful professional CSS example creates a "Flat" style light grey mockup which reveal inclined square shapes with colors change on the mouseover:

    /* Advanced flat layout */
    .social-link {
	position: relative;
	display: inline-block;
	width: 6em;
	height: 6em;
	margin: 2em 0 2em 3em;
	background: #fff;
	text-decoration: none;
	font:300 .65em 'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;
	/* SEO friendly indent */
	overflow: hidden;
	text-indent: 100%;
	white-space: nowrap;
	vertical-align: middle;
	/* Hover effect delay */
	-webkit-transition: all 0.5s;
	-moz-transition: all 0.5s;
	-ms-transition: all 0.5s;
	-o-transition: all 0.5s;
	transition: all 0.5s;
	/* Rotation */
	-webkit-transform: rotate(-45deg);
	-moz-transform: rotate(-45deg);
	-ms-transform: rotate(-45deg);
	-o-transform: rotate(-45deg);
	transform: rotate(-45deg);
	/* Transform origin */
	-webkit-transform-origin: 0 100%;
	-moz-transform-origin: 0 100%;
	-ms-transform-origin: 0 100%;
	-o-transform-origin: 0 100%;
	transform-origin: 0 100%;
	/* IE8+ */
    -ms-filter: "progid:DXImageTransform.Microsoft.Matrix(M11=0.7071067811865482, M12=0.7071067811865466, M21=-0.7071067811865466, M22=0.7071067811865482, SizingMethod='auto expand')";
	/* IE6 and 7 */
    filter: progid:DXImageTransform.Microsoft.Matrix(M11=0.7071067811865482, M12=0.7071067811865466, M21=-0.7071067811865466, M22=0.7071067811865482, SizingMethod='auto expand')
    }

    .social-link {text-decoration: none}

    .social-link,.social-link:active,.social-link:focus{
    	/* Remove dotted outline on links in Firefox, Chrome, IE8 and above */
    	outline: none;
    	/* Fix for IE7 */
    	_noFocusLine: expression(this.hideFocus=true)
    }

    /* SVG */
    .social-link svg {
	position: absolute;
	top: 1em;
	left: 50%;
	/* Not needed: use "width" & "height" attributes instead */
	width: 4em;
	height: 4em;
	/* Center: half of the width */
	margin-left: -2em;
	/* Rotation */
	-webkit-transform: rotate(45deg);
	-moz-transform: rotate(45deg);
	-ms-transform: rotate(45deg);
	-o-transform: rotate(45deg);
	transform: rotate(45deg);
	/* IE8+ */
	-ms-filter: "progid:DXImageTransform.Microsoft.Matrix(M11=0.7071067811865474, M12=-0.7071067811865476, M21=0.7071067811865476, M22=0.7071067811865474, SizingMethod='auto expand')";
	/* IE6-7 */
	filter: progid:DXImageTransform.Microsoft.Matrix(M11=0.7071067811865474,M12=-0.7071067811865476,M21=0.7071067811865476,M22=0.7071067811865474,SizingMethod='auto expand')
    }

    /* Social counts */
    .social-link span,
	.social-link b {
	/* No indent for IE6/7 */
	*text-indent: 0%;
	/* Inverse rotation */
	-webkit-transform: rotate(45deg);
	-ms-transform: rotate(45deg);
	transform: rotate(45deg);
	/* IE8+ */
	-ms-filter: "progid:DXImageTransform.Microsoft.Matrix(M11=0.7071067811865474, M12=-0.7071067811865476, M21=0.7071067811865476, M22=0.7071067811865474, SizingMethod='auto expand')";
	/* IE6-7 */
	filter: progid:DXImageTransform.Microsoft.Matrix(M11=0.7071067811865474,M12=-0.7071067811865476,M21=0.7071067811865476,M22=0.7071067811865474,SizingMethod='auto expand')
    }

    /* RGBA format blind colors for oldest browsers */
    .social-link.twitter:hover {background: rgba(0,160,209,1)}
    .social-link.facebook:hover {background: rgba(59,89,152,1)}
    .social-link.google:hover {background: rgba(211,72,54,1)}
    .social-link.pinterest:hover {background: rgba(204,51,51,1)}
    .social-link.tumblr:hover {background: rgba(50,80,109,1)}
    .social-link.instapaper:hover {background: rgba(51,51,51,1)}
    .social-link.pocket:hover {background: rgba(238,64,85,1)}
    .social-link.linkedin:hover {background: rgba(0,123,182,1)}
    .social-link.reddit:hover {background: rgba(204,204,204,1)}
    .social-link.permalink:hover {background: rgba(0,119,238,1)}

    /* Inner color form of this logo: use of inner class */
    .pocket path.inner{fill:rgba(255,255,255,1)}

    /* Default color of SVG icons */
        .twitter-icon path,
	.facebook-icon path,
	.gplus-icon path,
	.pinterest-icon path,
	.tumblr-icon path,
	.instapaper-icon path,
	.pocket-icon,
	.linkedin-icon,
	.reddit-icon,
	.permalink-icon {fill: #ccc}

    /* Color of inner part of this logo */
    .pocket-icon path.inner{fill:#fff}

    /* Hover color of SVG icons */
    .social-link:hover .twitter-icon path,
    .social-link:hover .facebook-icon path,
    .social-link:hover .gplus-icon path,
    .social-link:hover .pinterest-icon path,
    .social-link:hover .tumblr-icon path,
    .social-link:hover .instapaper-icon path,
    .social-link:hover .pocket-icon path,
    .social-link:hover .linkedin-icon path,
    .social-link:hover .reddit-icon path,
    .social-link:hover .permalink-icon path {fill: rgba(255,255,255,1)}

    /* Hover inner color for this logo */
    .social-link:hover .pocket-icon .inner{fill:rgba(238,64,85,1)}

    /* Counters if in use */
    .social-link span {
        display: inline-block;
	    position: absolute;
	    top: 0;
	    bottom: none;
	    left: 1em;
	    width: 8.5em;
	    text-align: center;
	    color: #aaa;
	    /* Effects */
	    -webkit-transition: all 0.5s;
	    -moz-transition: all 0.5s;
	    -ms-transition: all 0.5s;
	    -o-transition: all 0.5s;
	    transition: all 0.5s
    }

    /* Counters position on hover */
    .social-link:hover span {
	    top: 6em;
	    left: -5em
    }

    /* Social Network captions */
    .social-link b {
	    display: block;
    	    position: absolute;
	    bottom: -1em;
	    left: -2.1em;
	    width: 3.5em;
	    text-align: center;
	    color: #aaa
    }

    .social-link:hover b {visibility:hidden}

    /* Permalink */

    .link-container {
	    display: block;
	    position: relative
    }

    #show-link {
	    display: none;
	    position: absolute;
	    top: 6em;
	    left: 0;
	    width: 8.5em
    }

    /* Share counts if in use */
    .shares {
	    display: inline-block;
	    margin-top: -1.75em;
	    padding-right: .25em;
	    vertical-align: middle;
	    color: #7fc04c;
	    font:normal normal bold 64px/1 Arial,sans-serif;
    	font:normal normal bold 4rem/1 Arial,sans-serif
    }

    /* Numbers */
    .shares b {
	    display:inline-block;
    	margin-top:-1.5em;
	    vertical-align: middle;
	    color: #aaa;
	    font-size: .15em
    }

    /* Styling */
    .shares:after {
	    content:"";
	    position: relative;
	    top: 12px;
	    display: inline-block;
	    width: 30px;
	    height: 64px;
	    /* Right bar after the count as Mashable.com */
	    background:url(data:image/png;base64,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) center center no-repeat
    }

###2°. This another "Flat" example creates simple inline big colored but effective buttons:

    /* Simple flat layout */
    .social-link {
	    display: inline-block;
	    position: relative;
    	    top:. 3em;
    	    width: 9em;
	    height: 2.3em;
	    margin: 0 1em 1em 0;
    	    padding: 0.8em 0.2em 0 2em;
	    text-decoration: none;
	    text-align: center;
	    vertical-align: middle;
	    color: #fff;
	    font: 300 1em 'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;
	    -webkit-box-shadow: 0 1px 2px 0 rgba(0,0,0,.15);
	    -moz-box-shadow: 0 1px 2px 0 rgba(0,0,0,.15);
	    box-shadow: 0 1px 2px 0 rgba(0,0,0,.15)
    }

    .social-link{text-decoration: none}

    .social-link,.social-link:active,.social-link:focus{
    	/* Remove dotted outline on links in Firefox, Chrome, IE8 and above */
    	outline: none;
    	/* Fix for IE7 */
    	_noFocusLine: expression(this.hideFocus=true)
    }

    .link-container {display: block; position: relative}

    #show-link{display: none; position: absolute; top: 3em; left: 0; width: 3em}

    .twitter {background: #00aced; border: 1px solid #00aced}

    .facebook {background: #3b5998; border: 1px solid #3b5998}

    .google {background: #dd4b39; border: 1px solid #dd4b39}

    .pinterest {background: #cc333; border: 1px solid #cc333}

    .tumblr {background: #32506d; border: 1px solid #32506d}

    .instapaper {background: #333; border: 1px solid #333}

    .pocket {background: #ee4055; border: 1px solid #ee4055}

    .pocket path.inner {fill:#ee4055}

    .linkedin {background: #0e76a8; border: 1px solid #0e76a8}

    .reddit {background: #ccc; border: 1px solid #ccc}

    .permalink {background: #07e; border: 1px solid #07e}

    .social-link svg {
	    position:absolute;
	    top: 0.5em;
	    left: 0.6em;
	    width: 2em;
	    height: 2em
    }

    .twitter path,
	.facebook path,
	.google path,
	.pinterest path,
	.tumblr path,
	.instapaper path,
	.pocket-icon,
	.linkedin-icon,
	.reddit-icon,
	.permalink-icon {fill: #fff}

    .social-link:hover {
	    -webkit-box-shadow: inset 0 0 6px 0 rgba(0,0,0,.15);
	    -moz-box-shadow: inset 0 0 6px 0 rgba(0,0,0,.15);
	    box-shadow: inset 0 0 6px 0 rgba(0,0,0,.15)
    }

    .twitter:hover,
    .facebook:hover,
    .google:hover,
    .pinterest:hover,
    .tumblr:hover,
    .pocket:hover,
    .linkedin:hover,
    .reddit:hover,
    .permalink:hover {
	    background: transparent;
	    text-decoration: none !important;
	    color: #333 !important
    }

    .twitter:hover path,
    .facebook:hover path,
    .google:hover path,
    .pinterest:hover path,
    .tumblr:hover path,
    .pocket:hover path,
    .linkedin:hover path,
    .reddit:hover path,
    .permalink path {fill: #333 !important}

###3°. This another "Flat" example creates a gray vertical barr with monochrome social icons in it:

    /* Div container */
    .share-links {
	position: fixed;
	z-index: 2;
	width: 4em;
	margin-top: .3em;
	padding: 0 0 1.5em;
	background: #f4f4f4;
	border-bottom:.3em solid #323a45;
	cursor: default
    }
    /* The list of the shares */
    .shares {
	position: relative;
	display: block;
	padding: 20px 0;
	background: #323a45;
	color: #fff;
	text-align: center;
	text-transform: uppercase;
	font: .6em/1 Arial
    }
    /* Drawing an arrow here */
    .shares:after{
	content: "";
	display: block;
	position:absolute;
	z-index:2;
	bottom: -6px;
	left: 50%;
	width: 0;
	height: 0;
	margin-left: -6px;
	border-left: 6px solid transparent;
	border-right: 6px solid transparent;
	border-top: 6px solid #323a45
    }
    /* Styling all links */
    .social-link {
	position: relative;
	display: block;
	padding: 1.5em 0;
	color: #7e8890;
	line-height: 0
    }
    /* Correct first element 
    You can apply a class name instead
    */
    .social-link.first-item {
	top:.5em
    }
    /* Positionate all SVG icons */
    .social-link svg {
	position: absolute;
	top: 1.3em;
	left: 50%;
	width: 1em;
	height: 1em;
	margin-left: -.5em
    }
    /* Color for SVG icons */
    .social-link path {fill: #7e8890}
    .pocket-icon path.inner,.social-link:hover .pocket-icon path.inner,
    .reddit-icon path.inner,.social-link:hover .reddit-icon path.inner{fill:#f4f4f4}
    /* Over state for SVG icons */
    .social-link:hover path {fill: inherit}
    /* The share counts */
    .social-link span {
	display: none;
	text-align: center;
	font: 1em/1 Arial
    }
    /* But do not display it 
    .social-link b {
	display: block;
	text-indent: -999em
    }
    */
    .social-link span {
       display: block;
       position: absolute;
       z-index: 3;
       top: 4.5em;
       width: 100%;
       text-align: center;
       font: .6em/1 Arial
    }

###4°. Another simple flat layout with each counters in a black box on the right of little rounded, colored social icons 

    .social-link {
	display: inline-block;
	position: relative;
	top:. 3em;
	width: 2em;
	height: 2em;
	margin: 0 1em 1em 0;
	padding: 0;
	text-decoration: none;
	text-align: center;
	vertical-align: middle;
	color: #fff;
	font: 300 1em 'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;
	cursor: pointer;
	border-radius: 50%;
	-webkit-box-shadow: 0 1px 2px 0 rgba(0,0,0,.15);
	-moz-box-shadow: 0 1px 2px 0 rgba(0,0,0,.15);
	box-shadow: 0 1px 2px 0 rgba(0,0,0,.15)
    }

    .social-link:hover {
	-webkit-box-shadow: inset 0 0 6px 0 rgba(0,0,0,.15);
	-moz-box-shadow: inset 0 0 6px 0 rgba(0,0,0,.15);
	box-shadow: inset 0 0 6px 0 rgba(0,0,0,.15)
    }

    .social-link,.social-link:active,.social-link:focus{
    	/* Remove dotted outline on links in Firefox, Chrome, IE8 and above */
    	outline: none;
    	/* Fix for IE7 */
    	_noFocusLine: expression(this.hideFocus=true)
    }

    .twitter {background: #00aced; border: 1px solid #00aced}

    .facebook {background: #3b5998; border: 1px solid #3b5998}

    .google {background: #dd4b39; border: 1px solid #dd4b39}

    .pinterest {background: #cb2027; border: 1px solid #cb2027}

    .tumblr {background: #32506d; border: 1px solid #32506d}

    .instapaper,.buffer {background: #333; border: 1px solid #333}

    .pocket {background: #ee4055; border: 1px solid #ee4055}

    .pocket path.inner {fill: #ee4055}

    .linkedin {background: #0e76a8; border: 1px solid #0e76a8}

    .reddit,.delicious {background: #c1c1c1; border:1px solid #c1c1c1}

    .dribbble {background: #ea4c89; border:1px solid #ea4c89}

    .stumbleupon {background: #eb4823; border: 1px solid #eb4823}

    .instagram {background: #517fa4; border: 1px solid #517fa4}

    .vimeo {background: #1ab7ea; border: 1px sild #1ab7ea}

    .permalink {background:#07e; border: 1px solid #07e}

    .twitter:hover,
	.facebook:hover,
	.google:hover,
	.pinterest:hover,
	.tumblr:hover,
	.instapaper:hover,
	.buffer:hover,
	.pocket:hover,
	.linkedin:hover,
	.reddit:hover,
	.dribbble:hover,
	.stumbleupon:hover,
	.delicious:hover,
	.vimeo:hover,
	.permalink:hover {
		background: transparent;
		text-decoration: none !important
    }

    .social-link svg {
	position: absolute;
	top: 0.5em;
	left: 0.5em;
	width: 1em;
	height: 1em
    }

    .twitter path,
	.facebook path,
	.google path,
	.pinterest path,
	.tumblr path,
	.instapaper path,
	.buffer path,
	.pocket path,
	.linkedin path,
	.reddit path,
	.dribbble path,
	.stumbleupon path,
	.delicious path,
	.vimeo path,
	.permalink path {
		fill: #fff
    }

    .twitter:hover path,
	.facebook:hover path,
	.google:hover path,
	.pinterest:hover path,
	.tumblr:hover path,
	.instapaper:hover path,
	.buffer:hover path,
	.pocket:hover path,
	.linkedin:hover path,
	.reddit:hover path,
	.dribbble:hover path,
	.stumbleupon:hover path,
	.delicious:hover path,
	.vimeo:hover path,
	.permalink:hover path{
		fill: #333 !important
    }

    .pocket:hover path {fill: #000}

    .pocket:hover path.inner {fill: #fff !important}

    .social-link b {display: none}

    .social-link span{
    	display: block;
    	position: absolute;
    	right: -.3em;
    	bottom: -.18em;
    	padding: .2em .1em 0;
    	background: #555;
    	text-align: center;
    	font-size: 50%;
    	line-height: 2em;
    	border-top-left-radius: 50%
    }

    .social-link strong {
    	position: absolute;
    	top: 20%;
    	left: 30%;
    	color: #fff;
    	font-size: 0
    }

###5°. Another example extremly Flat and squared design especially for the TXP default layout

    .social-link {
	display: inline-block;
	position: relative;
	top:. 3em;
	width: 2em;
	height: 2em;
	margin: 0 .5em 1em 0;
	padding: 0;
	text-decoration: none;
	text-align: center;
	vertical-align: middle;
	outline: none;
	color: #fff;
	font: 300 1em 'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;
	cursor:pointer;
	-webkit-box-shadow: 0 1px 2px 0 rgba(0,0,0,.15);
	-moz-box-shadow: 0 1px 2px 0 rgba(0,0,0,.15);
	box-shadow: 0 1px 2px 0 rgba(0,0,0,.15)
    }

    .social-link:hover {
	-webkit-box-shadow: inset 0 0 6px 0 rgba(0,0,0,.15);
	-moz-box-shadow: inset 0 0 6px 0 rgba(0,0,0,.15);
	box-shadow: inset 0 0 6px 0 rgba(0,0,0,.15);
    /* Subtle effect 'on hover' applied on icons */
	-webkit-animation:zoomit 300ms ease-out;
	-moz-animation:zoomit 300ms ease-out;
	animation:zoomit 300ms ease-out
    }

    .social-link, .social-link:active, .social-link:focus {
    	/* Remove dotted outline on links in Firefox, Chrome, IE8 and above */
    	outline: none;
    	/* Fix for IE7 */
    	_noFocusLine: expression(this.hideFocus=true)
    }

    .twitter:hover,
    .facebook:hover,
    .google:hover,
    .pinterest:hover,
    .tumblr:hover,
    .instapaper:hover,
    .buffer:hover,
    .pocket:hover,
    .linkedin:hover,
    .reddit:hover,
    .stumbleupon:hover,
    .delicious:hover,
    .instagram:hover,
    .vimeo:hover,
    .permalink:hover {
		background: transparent;
		text-decoration: none !important;
		
    }

    .pocket:hover path {fill: #000}

    .pocket:hover path.inner {fill: #fff !important}

    .social-link svg {
	position:absolute;
	top: 0.5em;
	left: 0.5em;
	width: 1em;
	height: 1em
    }

    .twitter path,
    .facebook path,
    .google path,
    .pinterest path,
    .tumblr path,
    .instapaper path,
    .buffer path,
    .pocket path,
    .linkedin path,
    .reddit path,
    .dribbble path,
    .stumbleupon path,
    .delicious path,
    .instagram path,
    .vimeo path,
    .permalink path {
		fill: #fff
    }

    .twitter:hover path,
	.facebook:hover path,
	.google:hover path,
	.pinterest:hover path,
	.tumblr:hover path,
	.instapaper:hover path,
	.buffer:hover path,
	.pocket:hover path,
	.linkedin:hover path,
	.reddit:hover path,
	.dribbble:hover path,
	.stumbleupon:hover path,
	.delicious:hover path,
	.instagram:hover path,
	.vimeo:hover path,
	.permalink:hover path {
		fill: #333 !important
    }

    .social-link b {display: none}

    .social-link strong {position: absolute;top: 20%;left: 30%;color: #fff;font-size: 0}

    .link-container {position: relative;display: inline-block;width: 10em}

    .permalink {display: inline-block}

    #show-link {
	display: none;
	position: absolute;
	top: 3em;
	left: 0;
	width: 100%
    }


    .social-link span {
	display: none;
	position: absolute;
	top: -3.5em;
	left: 0;
	min-width: 2.4em;
	height: 25px;
	padding: 0 .3em;
	text-align: center;
	color: #525b67;
	font-size: 10px;
	line-height: 25px;
	font-weight: bold;
	border: 1px solid #e6e6e6;
	background: -moz-linear-gradient(center top , #fbfbfb 0%, #f6f6f6 100%) repeat scroll 0% 0% transparent;
	background: linear-gradient(center top , #fbfbfb 0%, #f6f6f6 100%) repeat scroll 0% 0% transparent;
	border-radius: 4px
    }

    .social-link span:before {
	content: '';
  	display: block;
  	position: absolute;
  	top: 25px;
  	left: 50%;
  	width: 0;
  	height: 0;
	margin-left:-.7em;
  	border: 7px solid transparent;
  	border-top-color: #ddd;
  	border-right-color: transparent;
  	border-bottom: none;
  	border-left-color: transparent;
  	-webkit-box-shadow: inset 0 0 6px 0 rgba(0,0,0,.15);
	-moz-box-shadow: inset 0 0 6px 0 rgba(0,0,0,.15);
	box-shadow: inset 0 0 6px 0 rgba(0,0,0,.15)
    }

    .social-link:hover span {display: block}

    .twitter path {fill: #00aced}
    .facebook path {fill: #3b5998}
    .google path {fill: #dd4B39}
    .pinterest path {fill: #cb2027}
    .tumblr path {fill: #32506d}
    .instapaper path, .buffer path {fill: #333}
    .pocket path {fill: #ee4055}
    .pocket .inner {fill: #fff}
    .linkedin path {fill: #0e76a8}
    .reddit path, .delicious path {fill: #c1c1c1}
    .dribbble path {fill: #ea4c89}
    .stumbleupon path {fill: #eb4823}
    .instagram path {fill: #517fa4}

    @-webkit-keyframes zoomit {
	15%, 40%, 75%, 100% {-webkit-transform-origin: center center}
	15% {-webkit-transform: scale(1.2, 1.1)}
	40% {-webkit-transform: scale(0.95, 0.95)}
	75% {-webkit-transform: scale(1.05, 1)}
	100% {-webkit-transform:scale(1, 1)}
    }

    @-moz-keyframes zoomit {
	15%, 40%, 75%, 100% {-moz-transform-origin: center center}
	15% {-moz-transform: scale(1.2, 1.1)}
	40% {-moz-transform: scale(0.95, 0.95)}
	75% {-moz-transform: scale(1.05, 1)}
	100% {-moz-transform: scale(1, 1)}
    }

    @keyframes zoomit {
	15%, 40%, 75%, 100% {transform-origin: center center}
	15% {transform: scale(1.2, 1.1)}
	40% {transform: scale(0.95, 0.95)}
	75% {transform: scale(1.05, 1)}
	100% {transform: scale(1, 1)}
    }

###11°. Tips and advices

You can choose to place your hidden social links on images and reveal its on mouse over.

Here is a markup example:

    <div class="img-outer relative">
	    <img src="" alt="" />
	    <div class="social-outer absolute">
		(...) pat-article-social tags come here (...)
	    </div>
    </div>

And the corresponding CSS rules:

    .relative{position:relative}
    .absolute{position:absolute}

    .social-outer {
	z-index: -1;
	top: 3%;
	right: 2%;
    }

    .img-outer:hover .socials-outer {
	z-index: 10
    }

##Changelog

- 30th April 2016. v 0.6.1. Support for Google AMP added.
- 27th April 2016. v 0.6.0. Restaures the omitted external js. Respects the ideal limitation of shares on social networks. Better shrinking function with support of final signs (., ?, !). 
- 14th April 2016. v 0.5.9. Adds the "bq" tag to render quotation text with social shares. Remove deprecated Twitter card formats. Some corrections/improvements.
- 30th March 2016. v 0.5.8. Adds the itemprop attribute in the share count tags (default: true. Else itemprop="0"). Adds new fb Open Graph meta tags.
- 22nd March 2016. v 0.5.7. Corrects errors with Twitter embedded statuses. All the plugin preferences had been added. Twitter share counting restored. No need to add javascript dependencies into page templates (the plugin does only once). Textpack support. Plugin installation and deletion added.
- 17th March 2016. v 0.5.6
- 2nd December 2015. v 0.5.5
- 30th November 2015. v 0.5.4
- 6th October 2015. v 0.5.3. Support embedded Instagram & Gist from link URLs. Long, partial & short links support for the status attribute of the <txp:twttr /> tag.
- 13th September 2015. v 0.5.2. Support embedded fb status into article's body. 
- 26th June 2015. v 0.5.1. Support Twitter json services to render embedded tweets.
- 5th June 2015. v 0.5.0. Support for embeded Tweets into article's body.
- 29th March 2015. v 0.4.9. Correct error message for PHP < 5.0.3. Add "fallback" attribute to remove first letters as fallback for browsers which do not support SVG format. No counter for Instagram for the moment.
- 26th March 2015. v. 0.4.8. Social Networks added. Plugin documentation is external.
- 20th February 2015. v 0.4.7. Support for all Twitter card. 
- 18th February 2015. v 0.4.6. Lot of changes for attributes (see list). Lot of improvements & new features added.
- 25th Decembre 2014. v 0.4.5. "plural" & "lang" attributes added for i18n support. Support for smd_short_url into permlinks if this plugin exists.
- 17th December 2014. v 0.4.4. "admins" attribute added for facebook. "showalways" attribute added. Correct the sum of "shares". Correct "zero" usage.
- 19th November 2014. v 0.4.3. Some code refactoring; Open Graph meta tags added (author & api); title attribute context sensitive; proper results when attributes omitted.
- 29th July 2014. v 0.4.2. Some code corrections; better support for the "image" attribute.
- 18th Mai 2014. v 0.4.1. locale (i18n support) & publisher (author's page link) attributes added. fancy support added. Corrects share results.
- 15th April 2014. v 0.4.0. Add an input for "permlink" attribute in order to show the article's permalink. Add "via" attribute for Twitter rendering Tweet. Use of dumbDown() TXP function for HTML special characters.
- 14th April 2014: v 0.3.9. Add permlink for current article.
- 31st March 2014: v 0.3.8. Correction of the Tumblr link.
- 29 March 2014: v 0.3.7. Add Linkedin. Correction of the Pocket link.
- 22nd March 2014: v 0.3.6. Better Twitter shorten URLs support. Support for G+ meta tags. Hide plugin rendering on public side when article status isn't "Live".
- 20th March 2014: v 0.3.5. Better CSS mockups. Open links in a new window. Correct a tag pat_article_social_sum in replacement of pat_body_tweet_sum.
- 5th March 2014: v 0.3.4. Ability to show sum of total shares count.
- 1st March 2014: v 0.3.3. "zero" attribute added. title content attribute is wrapped by b tags. Few corrections.
- 18 th February 2014: v 0.3.2. Stable version with minor corrections.
- 15th February 2014: v 0.3.1. Pocket & Instapaper added.
- 14th February 2014: v 0.3.0. Rewriting CSS examples for better readability. Caching on "counts" to speed up results.
- 29th January 2014: v 0.2.9. Support for article image.
- 28th January 2014: v 0.2.8. Some code revisions. content attribute added.
- 26 December 2013: v 0.2.7. Better current page URI rendering.
- 22nd December 2013: v 0.2.6. Add support for Tumblr.
- 21st December 2013: v -lite. Free lite version (support for facebook only).
- 16th December 2013: v 0.2.5. Add support for Pinterest.
- 15th December 2013: v 0.2.4. Add support format number in "k" for counters. New CSS example added.
- 14th December 2013: v 0.2.3. Doc corrections, buffering results.
- 13th December 2013: v 0.2.2. Inline styles removed. Add counter attribute.
- 11th December 2013: v 0.2.1. Some corrections. CSS example added.
- 9th December 2013: v 0.2.0. Accepts Twitter, facebook and Google + as social links. Inject corresponding social meta tags into header document.
- 7th December 2013: v 0.1.0. First release (formerly pat_article_tweet) only create links to Twitter.

##Credits

All embed plugin icons come from courtesy © flaticon.com.

##Notes

[1]: There are no Open Graph metatags for Pinterst & Tumblr. For G+ don't forget to use a proper declaration into your html document tag (i.e. `<html itemscope itemtype="http://schema.org/Article" lang="fr-fr" dir="ltr">`). See more here: schema.org.

[2]: The text you choose here is shrinked and followed by the article URL and all respects the 140 characters limit by Twitter.

[3]: Count is rounding up above 999. (e.g. 1 250 will be displayed 1.3 k)

