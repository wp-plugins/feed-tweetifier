<?php

/*
Plugin Name: Feed Tweetifier
Plugin URI: http://ollieparsley.com/projects/feedtweetifier
Description: Twitter formatted RSS - Get a Twitter formatted RSS feed that will add extra info to the title tag of each item. Perfect for feeds that are posted to Twitter using TwitterFeed, HootSuite or EasyTweets. WARNING: This plugin does not work with the Feedburner plugin.
Author: Ollie Parsley
Version: 0.1beta
Author URI: http://ollieparsley.com
*/

/*
**********************************************
stop editing here unless you know what you do!
**********************************************
*/

//Global variables
$tweeder_app_name = 'Feed Tweetifier';
$tweeder_app_feed_name = 'tweetified';
$tweeder_app_option_name = 'feedtweetifier_template';
$tweeder_app_folder_name = 'feed-tweetifier-0.1';

function tweeder_init() {
	global $tweeder_app_feed_name;
	global $tweeder_app_option_name;
	add_feed($tweeder_app_feed_name, 'tweeder_rss');
	add_action('admin_menu', 'tweeder_admin_menu_adder');

	//Set the detault
	if(get_option($tweeder_app_option_name).'' == ''){
		update_option($tweeder_app_option_name, '{TITLE} - by @{USERNAME}');
	}
}

function tweeder_admin_menu_adder() {
	global $tweeder_app_name;
	add_options_page('Options', $tweeder_app_name, 8, __FILE__, 'tweeder_admin_menu');
}

function tweeder_admin_menu() {

	global $wpdb;
	global $tweeder_app_name;
	global $tweeder_app_folder_name;
	global $tweeder_app_feed_name;
	global $tweeder_app_option_name;

	//Check to see if the form has been posted
	if(isset($_POST[$tweeder_app_option_name])){
		//Save the feed template option
		update_option($tweeder_app_option_name, $_POST[$tweeder_app_option_name]);
	}
	
	if(isset($_POST['submit_author_options'])){
		//Loop through each posted item
		foreach ($_POST as $k=>$v) {
			$pos = (int)strrpos($k, "-");
			if($pos > 0){
				//Item has a dash in it
				$field_array = explode("-",$k);
				if($field_array[1].'' == 'twitterusername'){
					$field_user_id = $field_array[0].'';
					$field_user_twitter_name_value = $_POST[$k];
					update_usermeta($field_user_id,'twitter_username',$field_user_twitter_name_value);
				}
			}
		}
	}

	$tweeder_feed_saved = get_option($tweeder_app_option_name).'';
	$tweeder_feed = $tweeder_feed_saved;
	
	echo '<div class="wrap">';
	echo '<div class="icon32" style="background-image:url(\''.get_bloginfo('url').'/wp-content/plugins/'.$tweeder_app_folder_name.'/images/icon.jpg\')"><br /></div>';
	echo '<h2>'.$tweeder_app_name.' Options</h2>';
	echo '<div style="float:right;padding:3px;margin;10px;border:1px #000000 solid;text-align:center;"><a href="'.get_bloginfo('url').'/?feed='.$tweeder_app_feed_name.'" target="_blank"><img src="'.get_bloginfo('url').'/wp-content/plugins/'.$tweeder_app_folder_name.'/images/rss.jpg" alt="View the tweetified feed" style="text-decoration:none;" /><br />View the<br />tweetified feed</a></div>';
	echo '<p>The "Feed template" section is for setting how the RSS title is set in the feed and consequently how the item will be displayed in Twitter. To get the feed into Twitter you will need to add the feed into a service like <a href="http://twitterfeed.com">TwitterFeed</a>, <a href="http://hootsuite.com">HootSuite</a> or <a href="http://easytweets.com">EasyTweets</a>.</p>';
	//Main options
	echo '<h3>Feed template</h3>';
	echo '<form method="post" action="">';
	echo '<p>';
	echo '<div style="width:300px;">{TITLE} - The articles title<br />';
	echo '{USERNAME} - The authors username<br /></div>';
	echo '<textarea name="'.$tweeder_app_option_name.'" style="height:50px;width:400px;">'.$tweeder_feed.'</textarea><br /><small>Don\'t forget that the URL will be appended to the end by the template when used with TwitterFeed etc.</small></p>';
	echo '<p><input type="submit" name="submit_main_options" value="Update main options" class="button-primary" /></p>';
	echo '</form>';
	echo '<hr />';
	//Author setting
	echo '<h3>Author twitter usernames</h3>';
	echo '<p>Please set the authors Twitter usernames below. Without any @ symbols</p>';
	echo '<form method="post" action="">';
  
	echo '<p>';
	echo '<table cellpadding="2" cellspacing"2" border="0">';
	echo '<tr><th style="text-align:left;">Name</th><th style="text-align:left;">Twitter Username</th></tr>';
	$users_array = $wpdb->get_col($wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY %s ASC", "user_nicename"));
	foreach($users_array as $user_id){
		$user = get_userdata($user_id);
		echo '<tr>';
		echo '<td style="text-align:left;width:200px;">'.$user->first_name.' '.$user->last_name.' ('.$user->user_login.')</td>';
		echo '<td style="text-align:left;"><input name="'.$user->ID.'-twitterusername" type="text" maxchars="20" style="width:150px;" value="'.get_usermeta($user_id,'twitter_username').'" /></td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</p>';
	echo '<p><input type="submit" name="submit_author_options" class="button-primary" value="Update author options" /></p>';
	echo '</form>';

	echo '</div>';
}

function tweeder_rss(){
/**
 * RSS2 Feed formatted for tweeder
 */
$feed_title_template = get_option("tweeder_feed_template").'';
header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
$more = 1;

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';

?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	<?php do_action('rss2_ns'); ?>
>
<channel>
	<title><?php bloginfo_rss('name'); wp_title_rss(); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php bloginfo_rss('url') ?></link>
	<description><?php bloginfo_rss("description") ?></description>
	<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></pubDate>
	<?php the_generator( 'rss2' ); ?>
	<language><?php echo get_option('rss_language'); ?></language>
	<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
	<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
	<?php do_action('rss2_head'); ?>
	<?php while( have_posts()) : the_post(); ?>
	<item>
		<?
		//Change the title
		$final_title = '';
		$author_id = get_the_author_id();
		$current_title = $feed_title_template;
		$current_title = str_replace("{TITLE}",get_the_title_rss(),$current_title);
		$current_title = str_replace("{USERNAME}",get_usermeta($author_id,'twitter_username'),$current_title);
		$final_title = $current_title;
		?>
		<title><?php echo $final_title; ?></title>
		<link><?php the_permalink_rss() ?></link>
		<comments><?php comments_link(); ?></comments>
		<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
		<dc:creator><?php the_author() ?></dc:creator>
		<?php the_category_rss() ?>
		<guid isPermaLink="false"><?php the_guid(); ?></guid>
		<?php if (get_option('rss_use_excerpt')) : ?>
                <description><![CDATA[<?php the_excerpt_rss() ?>]]></description>
        <?php else : ?>
                <description><![CDATA[<?php the_excerpt_rss() ?>]]></description>
            <?php if ( strlen( $post->post_content ) > 0 ) : ?>
                <content:encoded><![CDATA[<?php the_content() ?>]]></content:encoded>
            <?php else : ?>
                <content:encoded><![CDATA[<?php the_excerpt_rss() ?>]]></content:encoded>
            <?php endif; ?>
        <?php endif; ?>
                <wfw:commentRss><?php echo get_post_comments_feed_link(); ?></wfw:commentRss>
        <?php rss_enclosure(); ?>
	<?php do_action('rss2_item'); ?>
	</item>
	<?php endwhile; ?>
</channel>
</rss>
<?
}

//Add action
add_action('init', 'tweeder_init');
?>