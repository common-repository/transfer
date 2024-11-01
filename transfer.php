<?php
/*
Plugin Name: Transfer
Version: 1.0.0
Plugin URI: http://wordpress.org/extend/plugins/transfer/
Description: Allows users to submit posts to another WordPress installation.
Author: Edgar Kummer
Date: 2009-02-19 12:30
Author URI: http://www.aperto.de

Revision history
1.0.0 - 2009-02-19: Initial release

*/

/*  Copyright 2009  Edgar Kummer / Aperto AG  (email : wordpress@aperto.de)

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


set_include_path('.'
		. PATH_SEPARATOR . dirname(__FILE__) . '/library/'
		. PATH_SEPARATOR . get_include_path()
		);

require_once 'Aperto/XmlRpc.php';    // include class Aperto/XmlRpc

// set transfer server options/settings
add_option('aperto_transferserverurl', '');
add_option('aperto_transferserveruser', '');
add_option('aperto_transferserverpassword', '');
add_option('aperto_transferserverpublish', 'false');

// Hook for extending admin menu
add_action('admin_menu', 'aperto_add_pages');

// Load up the localization file if we're using WordPress in a different language
// Place it in the "localization" folder and name it "transfer-[value in wp-config].mo"
load_plugin_textdomain('transfer', PLUGINDIR . '/transfer/localization');

// action function for above hook
function aperto_add_pages() {

    // Add a new submenu under Options:
    add_options_page('Transfer', 'Transfer', 8, 'transferoptions', 'aperto_options_page');

}

// aperto_options_page() displays the page content for the Transfer Options submenu
function aperto_options_page() {

    // variables for the field and option names
    $hidden_field_name = 'aperto_submit_hidden';
    $url_field_name = 'aperto_transferserverurl';
    $user_field_name = 'aperto_transferserveruser';
    $password_field_name = 'aperto_transferserverpassword';
    $publish_field_name = 'aperto_transferserverpublish';

    // Read in existing option value from database
    $url_val = get_option('aperto_transferserverurl');
    $user_val = get_option('aperto_transferserveruser');
    $password_val = get_option('aperto_transferserverpassword');

    // Check if the user has posted information
    // If they did, this hidden field will be set to 'Y'
    if( $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $url_val = $_POST[ $url_field_name ];
        $user_val = $_POST[ $user_field_name ];
        $password_val = $_POST[ $password_field_name ];

        // Save the posted value in the database
        update_option( $url_field_name, $url_val );
        update_option( $user_field_name, $user_val );
        update_option( $password_field_name, $password_val );
        if ($_POST[ $publish_field_name ] == 'publish') {
            update_option( $publish_field_name, 'true' );
        } else {
            update_option( $publish_field_name, 'false' );
        }


        // Put an options updated message on the screen
        echo '<div class="updated"><p><strong>' . __('Settings saved.', 'transfer' ) . '</strong></p></div>';
    }

     // set publish checkbox status
	if (get_option('aperto_transferserverpublish') == 'true') {
		$publishCheck = 'checked="checked"';
	} else {
		$publishCheck = '';
	} ?>

    <div class="wrap">
    <h2><?php _e( 'Transfer Settings', 'transfer' ); ?></h2>

    <form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

    <table class="form-table">
      <tr valign="top">
        <th scope="row"><?php _e("Transfer-Server URL:", 'transfer' ); ?></th>
        <td><input type="text" name="<?php echo $url_field_name; ?>" value="<?php echo $url_val; ?>" size="50" /></td>
      </tr>
      <tr valign="top">
        <th scope="row"><?php _e("User:", 'transfer' ); ?></th>
        <td><input type="text" name="<?php echo $user_field_name; ?>" value="<?php echo $user_val; ?>" size="20" /></td>
      </tr>
      <tr valign="top">
        <th scope="row"><?php _e("Password:", 'transfer' ); ?></th>
        <td><input type="password" name="<?php echo $password_field_name; ?>" value="<?php echo $password_val; ?>" size="20" /></td>
      </tr>
      <tr valign="top">
        <th scope="row"><?php _e("Instant Publish:", 'transfer' ); ?></th>
        <td><input type="checkbox" name="<?php echo $publish_field_name; ?>" value="publish" <?php echo $publishCheck; ?> /></td>
      </tr>
    </table>

    <p class="submit">
    <input type="submit" name="aperto_submit" value="<?php _e('Save Changes', 'transfer' ) ?>" />
    </p>

    </form>
    </div>

<?php
}

// output checkbox to easily change transfer options
function aperto_addInlineTransferCheckbox() {
	global $post;

	// set checkbox status
	if (get_post_meta($post->ID, 'transfer', true)) {
		$check = 'checked="checked"';
	} else {
		$check = '';
	}
	echo '<div><p style="margin:0.3em 0 0.7em"><label class="selectit" for="transfer_status" ><input type="checkbox" name="transfer_status" id="transfer_status" style="min-width:0px;margin-right:0.5em" value="transfer" ' . $check . ' />' . __( 'Transfer to', 'transfer' ) . ' <a href="' . get_option('aperto_transferserverurl') . '">' . __( 'external WordPress', 'transfer' ) . '</a></label></p></div>';
}

// submit posts with the XML-RPC protocol
function aperto_xmlrpc ($postId) {

	//retrieve the post content
	$post_data = get_post($postId);
	$author_data = get_userdata($post_data->post_author);

	//general custom field update
	$transferStatus = $_POST['transfer_status'];

	// check post_type to prevent double posts on save_post action/hook
	if ($transferStatus && $post_data->post_type == 'post') {
		// connect to xmlrpc server
		$xmlRpc = new XmlRpc (get_option('aperto_transferserverurl'), get_option('aperto_transferserveruser'), get_option('aperto_transferserverpassword'));

		// current post categries
		$postCategories = array();
		if (get_the_category($postId)) {
			$categories = get_the_category($postId);
			foreach($categories as $category) {
				$postCategories[] = $category->cat_name;
			}
		}


		// current post tags
		$postTags = array();
		if (get_the_tags($postId)) {
			$tags = get_the_tags($postId);
			foreach($tags as $tag) {
				$postTags[] = $tag->name;
			}
		}

		// current custom fields
		$postCustomFields = array();
		foreach ( (array) has_meta($postId) as $meta ) {
			// Don't expose protected fields.
			if ( strpos($meta['meta_key'], '_wp_') === 0 || $meta['meta_key'] == 'transfer' ) {
				continue;
			}

			$postCustomFields[] = array(
				"key"   => $meta['meta_key'],
				"value" => $meta['meta_value']
			);
		}

		// find all images in the post
		$match_count = preg_match_all("/<img[^']*?src=\"([^']*?)\"[^']*?>/", $post_data->post_content, $match_array, PREG_PATTERN_ORDER);
		$imageList = $match_array[1];

		$postContent = $post_data->post_content;	// load post content
		
		// delete image links form content
		$siteUrl = preg_replace("(\/)", '\/', get_settings('siteurl'));
		$postContent = preg_replace("/(<a href=\"$siteUrl\/wp-content\/uploads\/[^']*?\"[^']*?>)([^']*?)(<\/a>)/", '$2', $postContent);
		
		$postContent = str_replace(get_settings('siteurl'), get_option('aperto_transferserverurl'), $postContent);    // update to transfer site url
		$postContent = preg_replace('/\/(\d+)\/(\d+)\//i', date("/Y/m/"), $postContent);    // update to current date
		foreach ($imageList as $imageFileName) {
		    $postContent = str_replace(basename($imageFileName), sanitize_file_name(basename($imageFileName)), $postContent); //update to sanatised file name
		}		

		// blog entry parameters
		$postTitle = $post_data->post_title;
		$postExcerpt = $post_data->post_excerpt;
		$postPublish = false;
		if (get_option('aperto_transferserverpublish') == 'true') {
		    $postPublish = true;
		}


		if (get_post_meta($postId, 'transfer', true)) {
			$externalPostId = get_post_meta($postId, 'transfer', true);
			// update external blog post
			$xmlRpc->updateBlogPost ($externalPostId, $postTitle, $postCategories, $postContent, $postExcerpt, $postTags, $postCustomFields, $postPublish);
		} else {
		    // add post author name
		    $postCustomFields[] = array(
				"key"   => 'author',
				"value" => $author_data->display_name
		    );

			// transfer blog post
			$transferPostId = $xmlRpc->transferBlogPost ($postTitle, $postCategories, $postContent, $postExcerpt, $postTags, $postCustomFields, $postPublish);

			// set custom field with external Post Id
			$_POST['metakeyinput'] = 'transfer';
			$_POST['metavalue'] = $transferPostId;
			add_meta($postId);

			foreach ($imageList as $fileUrl) {
				$fileUrl = urldecode($fileUrl);
				$mediaFileName = sanitize_file_name(basename($fileUrl));
				$filePath = ABSPATH . str_replace(get_settings('siteurl'), '', $fileUrl);

				if(file_exists($filePath)) {
					$mediaFile = file_get_contents($filePath);

					// transfer media object
					$xmlRpc->transferMediaObject ($mediaFileName, $mediaFile);
				}
			}
		}
	}
	return $postId;
}

// add actions
add_action('post_submitbox_start', 'aperto_addInlineTransferCheckbox');
add_action('save_post', 'aperto_xmlrpc');

?>