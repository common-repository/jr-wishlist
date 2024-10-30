<?php
/*
Plugin Name: JR Wishlist
Plugin URI: http://www.jakeruston.co.uk/2009/12/wordpress-plugin-jr-wishlist/
Description: Allows you to insert a wishlist as a widget on your blog.
Version: 1.2.7
Author: Jake Ruston
Author URI: http://www.jakeruston.co.uk
*/

/*  Copyright 2010 Jake Ruston - the.escapist22@gmail.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Hook for adding admin menus
add_action('admin_menu', 'jr_wishlist_add_pages');
register_activation_hook(__FILE__,'wishlist_install');

if (!function_exists("_iscurlinstalled")) {
function _iscurlinstalled() {
if (in_array ('curl', get_loaded_extensions())) {
return true;
} else {
return false;
}
}
}

if (!function_exists("jr_show_notices")) {
function jr_show_notices() {
echo "<div id='warning' class='updated fade'><b>Ouch! You currently do not have cURL enabled on your server. This will affect the operations of your plugins.</b></div>";
}
}

if (!_iscurlinstalled()) {
add_action("admin_notices", "jr_show_notices");

} else {
if (!defined("ch"))
{
function setupch()
{
$ch = curl_init();
$c = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
return($ch);
}
define("ch", setupch());
}

if (!function_exists("curl_get_contents")) {
function curl_get_contents($url)
{
$c = curl_setopt(ch, CURLOPT_URL, $url);
return(curl_exec(ch));
}
}
}

if (!function_exists("jr_wishlist_refresh")) {
function jr_wishlist_refresh() {
update_option("jr_submitted_wishlist", "0");
}
}

function wishlist_choice () {
if (get_option("jr_wishlist_links_choice")=="") {

if (_iscurlinstalled()) {
$pname="jr_wishlist";
$url=get_bloginfo('url');
$content = curl_get_contents("http://www.jakeruston.co.uk/plugins/links.php?url=".$url."&pname=".$pname);
update_option("jr_submitted_wishlist", "1");
wp_schedule_single_event(time()+172800, 'jr_wishlist_refresh'); 
} else {
$content = "Powered by <a href='http://arcade.xeromi.com'>Free Online Games</a> and <a href='http://directory.xeromi.com'>General Web Directory</a>.";
}

if ($content!="") {
$content=utf8_encode($content);
update_option("jr_wishlist_links_choice", $content);
}
}

if (get_option("jr_wishlist_link_personal")=="") {
$content = curl_get_contents("http://www.jakeruston.co.uk/p_pluginslink4.php");

update_option("jr_wishlist_link_personal", $content);
}
}

// action function for above hook
function jr_wishlist_add_pages() {
    add_options_page('JR Wishlist', 'JR Wishlist', 'administrator', 'jr_wishlist', 'jr_wishlist_options_page');
}

$wishlist_db_version = "1.0.0";

function wishlist_install () {
   global $wpdb;
   global $wishlist_db_version;

   $table_name = $wpdb->prefix . "jrwishlist";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
      $sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  title text NOT NULL,
	  description text NOT NULL,
	  url text NOT NULL,
	  done int NOT NULL,
	  UNIQUE KEY id (id)
	);";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
 
      add_option("wishlist_db_version", $wishlist_db_version);
	  }
}


// jr_wishlist_options_page() displays the page content for the Test Options submenu
function jr_wishlist_options_page() {

    // variables for the field and option names 
    $opt_name_5 = 'mt_wishlist_plugin_support';
	$opt_name_6 = 'mt_wishlist_title';
    $hidden_field_name = 'mt_wishlist_submit_hidden';
    $data_field_name_5 = 'mt_wishlist_plugin_support';
	$data_field_name_6 = 'mt_wishlist_title';

    // Read in existing option value from database
    $opt_val_5 = get_option($opt_name_5);
	$opt_val_6 = get_option($opt_name_6);

if (!$_POST['feedback']=='') {
$my_email1="the.escapist22@gmail.com";
$plugin_name="JR Wishlist";
$blog_url_feedback=get_bloginfo('url');
$user_email=$_POST['email'];
$user_email=stripslashes($user_email);
$subject=$_POST['subject'];
$subject=stripslashes($subject);
$name=$_POST['name'];
$name=stripslashes($name);
$response=$_POST['response'];
$response=stripslashes($response);
$category=$_POST['category'];
$category=stripslashes($category);
if ($response=="Yes") {
$response="REQUIRED: ";
}
$feedback_feedback=$_POST['feedback'];
$feedback_feedback=stripslashes($feedback_feedback);
if ($user_email=="") {
$headers1 = "From: feedback@jakeruston.co.uk";
} else {
$headers1 = "From: $user_email";
}
$emailsubject1=$response.$plugin_name." - ".$category." - ".$subject;
$emailmessage1="Blog: $blog_url_feedback\n\nUser Name: $name\n\nUser E-Mail: $user_email\n\nMessage: $feedback_feedback";
mail($my_email1,$emailsubject1,$emailmessage1,$headers1);
?>
<div class="updated"><p><strong><?php _e('Feedback Sent!', 'mt_trans_domain' ); ?></strong></p></div>
<?php
}

if ($_GET['delete']) {
global $wpdb;
$table_name = $wpdb->prefix . "jrwishlist";

$results = $wpdb->query("DELETE FROM " . $table_name . " WHERE id=" . $_GET['delete']);

?>
<div class="updated"><p><strong><?php _e('Item Deleted!', 'mt_trans_domain' ); ?></strong></p></div>
<?php
}

if ($_POST['varhid']=="Y") {
global $wpdb;
$table_name = $wpdb->prefix . "jrwishlist";
$wpdb->update($table_name, array( 'title' => $_POST["title"], 'description' => $_POST["description"], 'url' => $_POST["url"]), array( 'id' => $_POST['id'] ))
?>
<div class="updated"><p><strong><?php _e('Item Edited!', 'mt_trans_domain' ); ?></strong></p></div>
<?php
}

if ( $_POST['mt_wishlist_hidden_submit'] == 'Y') {
$title=$_POST['title'];
$description=$_POST['description'];
$url=$_POST['url'];

   global $wpdb;
   global $wishlist_db_version;

   $table_name = $wpdb->prefix . "jrwishlist";

    $sql = 'INSERT INTO ' . $table_name . ' SET ';
	$sql .= 'title = "'. $title .'", ';
	$sql .= 'description = "'. $description .'", ';
	$sql .= 'url = "'. $url .'"';
	
    $wpdb->query( $sql );

        // Put an options updated message on the screen

?>
<div class="updated"><p><strong><?php _e('Item Created!', 'mt_trans_domain' ); ?></strong></p></div>
<?php
}

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val_5 = $_POST[$data_field_name_5];
		$opt_val_6 = $_POST[$data_field_name_6];

        // Save the posted value in the database
        update_option( $opt_name_5, $opt_val_5 );
		update_option( $opt_name_6, $opt_val_6 );

        // Put an options updated message on the screen

?>
<div class="updated"><p><strong><?php _e('Options saved.', 'mt_trans_domain' ); ?></strong></p></div>
<?php

    }

    // Now display the options editing screen

    echo '<div class="wrap">';

    // header

    echo "<h2>" . __( 'JR Wishlist Plugin Options', 'mt_trans_domain' ) . "</h2>";
$blog_url_feedback=get_bloginfo('url');
	$donated=curl_get_contents("http://www.jakeruston.co.uk/p-donation/index.php?url=".$blog_url_feedback);
	if ($donated=="1") {
	?>
		<div class="updated"><p><strong><?php _e('Thank you for donating!', 'mt_trans_domain' ); ?></strong></p></div>
	<?php
	} else {
	?>
	<div class="updated"><p><strong><?php _e('Please consider donating to help support the development of my plugins!', 'mt_trans_domain' ); ?></strong><br /><br /><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="ULRRFEPGZ6PSJ">
<input type="image" src="https://www.paypal.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form></p></div>
<?php
}

    // options form
    
    $change3 = get_option("mt_wishlist_plugin_support");


if ($change3=="Yes" || $change3=="") {
$change3="checked";
$change31="";
} else {
$change3="";
$change31="checked";
}

    ?>
	<?php

	if ($_GET['set']=="done") {
global $wpdb;
$table_name = $wpdb->prefix . "jrwishlist";
$id=$_GET["id"];

$query = $wpdb->query("UPDATE " . $table_name . " SET done=1 WHERE id=".$id);
}

if ($_GET['edit']) {
global $wpdb;
$table_name = $wpdb->prefix . "jrwishlist";

$rows = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE id=" . $_GET['edit']);

foreach ($rows as $rows) {
echo '<h3>Edit Item</h3>';
echo '<form action="" method="post">';
echo 'Title: <input type="text" name="title" value="'.$rows->title.'" />';
echo 'Description: <input type="text" name="description" value="'.$rows->description.'" />';
echo 'URL: <input type="text" name="url" value="'.$rows->url.'" />';
echo '<input type="hidden" name="varhid" value="Y" />';
echo '<input type="hidden" name="id" value="'.$_GET["edit"].'" />';
echo '<input type="submit" value="Edit" />';
echo '</form>';
}
}
?>	
<form name="form3" method="post" action="">
<h3>View Items</h3>

<?php
   global $wpdb;
   $table_name = $wpdb->prefix . "jrwishlist";
   
$rows = $wpdb->get_results("SELECT * FROM " . $table_name);
$rows2 = $wpdb->get_results("SELECT * FROM " . $table_name);

foreach ($rows2 as $rows2) {
$i ++;
}

if ($i>0 && $i<2) {
$query = $wpdb->query("UPDATE " . $table_name . " SET enabled=1");
}


foreach ($rows as $rows) {

if ($rows->done==1) {
echo "<del>" . $rows->title . " - Done! - Edit - </del><a href='?page=jr_wishlist&delete=". $rows->id ."'>Delete</a><br />";
} else {
	echo "" . $rows->title . " - <a href='?page=jr_wishlist&set=done&id=".$rows->id."'>Done!</a> - <a href='?page=jr_wishlist&edit=". $rows->id ."'>Edit</a> - <a href='?page=jr_wishlist&delete=". $rows->id ."'>Delete</a>";
	echo "<br />";
}
}
?>

<h3>Create an Item</h3>
<form action="" method="post">
<input type="hidden" name="mt_wishlist_hidden_submit" value="Y" />

<p><?php _e("Title:", 'mt_trans_domain' ); ?> 
<input type="text" name="title" />
</p><hr />

<p><?php _e("Description:", 'mt_trans_domain' ); ?> 
<textarea name="description"></textarea>
</p><hr />

<p><?php _e("URL:", 'mt_trans_domain' ); ?> 
<input type="text" name="url" />
</p><hr />

<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Create Item', 'mt_trans_domain' ) ?>" />
</p><hr />
</form>

<h3>Settings</h3>

<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<p><?php _e("Widget Title:", 'mt_trans_domain' ); ?> 
<input type="text" name="<?php echo $data_field_name_6; ?>" value="<?php echo $opt_val_6; ?>" />
</p><hr />

<p><?php _e("Show Plugin Support?", 'mt_trans_domain' ); ?> 
<input type="radio" name="<?php echo $data_field_name_5; ?>" value="Yes" <?php echo $change3; ?>>Yes
<input type="radio" name="<?php echo $data_field_name_5; ?>" value="No" <?php echo $change31; ?> id="Please do not disable plugin support - This is the only thing I get from creating this free plugin!" onClick="alert(id)">No
</p>

<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Update Options', 'mt_trans_domain' ) ?>" />
</p><hr />

</form>

<br /><br />

<script type="text/javascript">
function validate_required(field,alerttxt)
{
with (field)
  {
  if (value==null||value=="")
    {
    alert(alerttxt);return false;
    }
  else
    {
    return true;
    }
  }
}

function validate_form(thisform)
{
with (thisform)
  {
  if (validate_required(subject,"Subject must be filled out!")==false)
  {email.focus();return false;}
  if (validate_required(email,"E-Mail must be filled out!")==false)
  {email.focus();return false;}
  if (validate_required(feedback,"Feedback must be filled out!")==false)
  {email.focus();return false;}
  }
}
</script><h3>Submit Feedback about my Plugin!</h3>
<p><b>Note: Only send feedback in english, I cannot understand other languages!</b></p>
<form name="form2" method="post" action="" onsubmit="return validate_form(this)">
<p><?php _e("Name:", 'mt_trans_domain' ); ?> 
<input type="text" name="name" /></p>
<p><?php _e("E-Mail:", 'mt_trans_domain' ); ?> 
<input type="text" name="email" /></p>
<p><?php _e("Category:", 'mt_trans_domain'); ?>
<select name="category">
<option value="Bug Report">Bug Report</option>
<option value="Feature Request">Feature Request</option>
<option value="Other">Other</option>
</select>
<p><?php _e("Subject (Required):", 'mt_trans_domain' ); ?>
<input type="text" name="subject" /></p>
<input type="checkbox" name="response" value="Yes" /> I want e-mailing back about this feedback</p>
<p><?php _e("Comment (Required):", 'mt_trans_domain' ); ?> 
<textarea name="feedback"></textarea>
</p>
<p class="submit">
<input type="submit" name="Send" value="<?php _e('Send', 'mt_trans_domain' ); ?>" />
</p><hr /></form>
</div>
<?php
 

} 

function init_wishlist_widget() {
register_sidebar_widget('JR Wishlist', 'show_wishlist');
}

if (get_option("jr_wishlist_links_choice")=="") {
wishlist_choice();
}

function show_wishlist($args) {
extract($args);

$supportplugin = get_option("mt_wishlist_plugin_support"); 
$title = get_option("mt_wishlist_title"); 
global $wpdb;

$table_name = $wpdb->prefix . "jrwishlist";

   $rows = $wpdb->get_results("SELECT * FROM " . $table_name . "");
echo $before_title.$title.$after_title."".$before_widget;   
foreach ($rows as $rows) {
if ($rows->done==1) {
echo "<li><del>".$rows->title."<br />".$rows->description."</del></li>";
} else {
echo "<li><a href='".$rows->url."'>".$rows->title."</a><br />".$rows->description."</li>";
}
}

if ($supportplugin=="Yes" || $supportplugin=="") {
$linkper=utf8_decode(get_option('jr_wishlist_link_personal'));
if (get_option("jr_wishlist_link_newcheck")=="") {
$pieces=explode("</a>", get_option('jr_wishlist_links_choice'));
$pieces[0]=str_replace(" ", "%20", $pieces[0]);
$pieces[0]=curl_get_contents("http://www.jakeruston.co.uk/newcheck.php?q=".$pieces[0]."");
$new=implode("</a>", $pieces);
update_option("jr_wishlist_links_choice", $new);
update_option("jr_wishlist_link_newcheck", "444");
}

if (get_option("jr_submitted_wishlist")=="0") {
$pname="jr_wishlist";
$url=get_bloginfo('url');
$content = curl_get_contents("http://www.jakeruston.co.uk/plugins/links.php?url=".$url."&pname=".$pname);
update_option("jr_submitted_wishlist", "1");
update_option("jr_wishlist_links_choice", $content);

wp_schedule_single_event(time()+172800, 'jr_wishlist_refresh'); 
} else if (get_option("jr_submitted_wishlist")=="") {
$pname="jr_wishlist";
$url=get_bloginfo('url');
$current=get_option("jr_wishlist_links_choice");
$content = curl_get_contents("http://www.jakeruston.co.uk/plugins/links.php?url=".$url."&pname=".$pname."&override=".$current);
update_option("jr_submitted_wishlist", "1");
update_option("jr_wishlist_links_choice", $content);

wp_schedule_single_event(time()+172800, 'jr_wishlist_refresh'); 
}

echo '<p style="font-size:x-small">Wishlist Plugin created by '.$linkper.' - '.stripslashes(get_option("jr_wishlist_links_choice")).'</p>';
}
echo $after_widget;
}

add_action("plugins_loaded", "init_wishlist_widget");

?>
