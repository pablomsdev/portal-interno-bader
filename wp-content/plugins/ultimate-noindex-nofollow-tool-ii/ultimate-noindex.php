<?php
/*
Plugin Name: Ultimate Noindex Nofollow Tool II
Description: Improves your blog's search engine optimization by "noindexing" pages you choose. Now also for page-based (as opposed to date-based) archives.
Version: 1.3
Author: Kilian Evang
Author URI: https://texttheater.net

Copyright 2009-2015 Jonathan Kemp and Kilian Evang

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
*/

add_action('admin_menu', 'unn_admin_menu');
add_action('wp_head', 'wp_noindex');
add_action('login_head', 'unn_noindex_login');
add_action('admin_head', 'unn_noindex_admin');
add_filter( 'wp_list_pages', 'unn_nofollow_pages' );
add_filter( 'get_archives_link', 'unn_nofollow_archives' );
add_filter( 'wp_list_categories', 'unn_nofollow_cats' );
add_filter( 'loginout', 'unn_nofollow_login' );
add_filter( 'register', 'unn_nofollow_register' );

function wp_noindex() {
	if (get_option('unn_noindex_date') == "yes") {
  		if (is_date()) {
			echo "	<meta name=\"robots\" content=\"noindex, follow\"/>\n";
		} 
	}
	
	if (get_option('unn_noindex_search') == "yes") {
  		if (is_search()) {
			echo "	<meta name=\"robots\" content=\"noindex, follow\"/>\n";
		} 
	}
	
	if (get_option('unn_noindex_pages')) {
		$pages = explode(",", get_option('unn_noindex_pages'));
		if (is_page($pages)) {
			echo "	<meta name=\"robots\" content=\"noindex, follow\"/>\n";
		}  
	}
	
	if (get_option('unn_noindex_cat') == "yes") {
  		if (is_category()) {
			echo "	<meta name=\"robots\" content=\"noindex, follow\"/>\n";
		} 
	}
	
	if (get_option('unn_noindex_tags') == "yes") {
  		if (is_tag()) {
			echo "	<meta name=\"robots\" content=\"noindex, follow\"/>\n";
		} 
	}
	
	if (get_option('unn_noindex_auth') == "yes") {
  		if (is_author()) {
			echo "	<meta name=\"robots\" content=\"noindex, follow\"/>\n";
		} 
	}
	
	if (get_option('unn_noindex_paged') == "yes") {

  		if (is_paged() && !is_date() && !is_search() && !is_category() && !is_tag() && !is_author()) {
			echo "	<meta name=\"robots\" content=\"noindex, follow\"/>\n";
		} 
	}
}

function unn_noindex_admin() {
	if (get_option('unn_noindex_admin') == "yes") {
		echo "	<meta name=\"robots\" content=\"noindex\"/>\n";
	}
}

function unn_noindex_login() {
	if (get_option('unn_noindex_login') == "yes") {
		echo "	<meta name=\"robots\" content=\"noindex\"/>\n";
	}
}

function unn_nofollow_pages( $output ) {
	if (get_option('unn_nofollow_pages')) {
		$text = stripslashes($output);
		$page_ids = explode(",", get_option('unn_nofollow_pages'));
		foreach ($page_ids as $key => $page_id) {
			$page_id = trim($page_id);
			$pattern = '|<a (.+?[=/]' . $page_id . '["&/].+?)>|i';
			$text = preg_replace_callback($pattern, 'wp_rel_nofollow_callback', $text);
		}
		return $text;
	} else {
		return $output;
	}
}

function unn_nofollow_archives( $text ) {
	if (get_option('unn_nofollow_archives') == "yes") {
		$text = stripslashes($text);
		$text = preg_replace_callback('|<a (.+?)>|i', 'wp_rel_nofollow_callback', $text);
		return $text;
	} else {
		return $text;
	}
}

function unn_nofollow_cats( $text ) {
	if (get_option('unn_nofollow_cats') == "yes") {
		$text = stripslashes($text);
		$text = preg_replace_callback('|<a (.+?)>|i', 'wp_rel_nofollow_callback', $text);
		return $text;
	} else {
		return $text;
	}
}

function unn_nofollow_register( $link ) {
	if (get_option('unn_nofollow_register') == "yes") {
		$link = stripslashes($link);
		$link = preg_replace_callback('|<a (.+?)>|i', 'wp_rel_nofollow_callback', $link);
		return $link;
	} else {
		return $link;
	}
}

function unn_nofollow_login( $link ) {
	if (get_option('unn_nofollow_login') == "yes") {
		$link = stripslashes($link);
		$link = preg_replace_callback('|<a (.+?)>|i', 'wp_rel_nofollow_callback', $link);
		return $link;
	} else {
		return $link;
	}
}

function unn_admin_menu() {  
	add_options_page('Ultimate Noindex Nofollow Options', 'Ultimate Noindex', 'level_8', __FILE__, 'unn_admin');
}

function unn_admin() {
	if($_POST['unn_hidden'] == 'Y') {
		//Form data sent
		$noindex_date = $_POST['unn_noindex_date'];
		update_option('unn_noindex_date', $noindex_date);

		$noindex_paged = $_POST['unn_noindex_paged'];
		update_option('unn_noindex_paged', $noindex_paged);
			
		$noindex_search = $_POST['unn_noindex_search'];
		update_option('unn_noindex_search', $noindex_search);
		
		$noindex_cat = $_POST['unn_noindex_cat'];
		update_option('unn_noindex_cat', $noindex_cat);
		
		$noindex_tags = $_POST['unn_noindex_tags'];
		update_option('unn_noindex_tags', $noindex_tags);
		
		$noindex_auth = $_POST['unn_noindex_auth'];
		update_option('unn_noindex_auth', $noindex_auth);
		
		$noindex_login = $_POST['unn_noindex_login'];
		update_option('unn_noindex_login', $noindex_login);
		
		$noindex_pages = $_POST['unn_noindex_pages'];
		update_option('unn_noindex_pages', $noindex_pages);
		
		$noindex_admin = $_POST['unn_noindex_admin'];
		update_option('unn_noindex_admin', $noindex_admin);
		
		$nofollow_archives = $_POST['unn_nofollow_archives'];
		update_option('unn_nofollow_archives', $nofollow_archives);
		
		$nofollow_cats = $_POST['unn_nofollow_cats'];
		update_option('unn_nofollow_cats', $nofollow_cats);
		
		$nofollow_pages = $_POST['unn_nofollow_pages'];
		update_option('unn_nofollow_pages', $nofollow_pages);
		
		$nofollow_register = $_POST['unn_nofollow_register'];
		update_option('unn_nofollow_register', $nofollow_register);
		
		$nofollow_login = $_POST['unn_nofollow_login'];
		update_option('unn_nofollow_login', $nofollow_login);
	?>  
    	<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
	<?php  
	} else {
		//Normal page display
		$noindex_date = get_option('unn_noindex_date');
		$noindex_paged = $_POST['unn_noindex_paged'];
		update_option('unn_noindex_date', $noindex_date);
		$noindex_search = get_option('unn_noindex_search');
		$noindex_cat = get_option('unn_noindex_cat');
		$noindex_tags = get_option('unn_noindex_tags');
		$noindex_auth = get_option('unn_noindex_auth');
		$noindex_login = get_option('unn_noindex_login');
		$noindex_pages = get_option('unn_noindex_pages');
		$noindex_admin = get_option('unn_noindex_admin');
		$nofollow_archives = get_option('unn_nofollow_archives');
		$nofollow_cats = get_option('unn_nofollow_cats');
		$nofollow_pages = get_option('unn_nofollow_pages');
		$nofollow_register = get_option('unn_nofollow_register');
		$nofollow_login = get_option('unn_nofollow_login');
		
		if (empty($noindex_date)) 	$noindex_date = "unchecked";
		if (empty($noindex_search)) 	$noindex_search = "unchecked";
		if (empty($noindex_cat)) 	$noindex_cat = "unchecked";
		if (empty($noindex_tags)) 	$noindex_tags = "unchecked";
		if (empty($noindex_auth)) 	$noindex_auth = "unchecked";
		if (empty($noindex_login)) 	$noindex_login = "unchecked";
		if (empty($noindex_admin)) 	$noindex_admin = "unchecked";
		if (empty($nofollow_archives)) 	$nofollow_archives = "unchecked";
		if (empty($nofollow_cats)) 	$nofollow_cats = "unchecked";
		if (empty($nofollow_register)) 	$nofollow_register = "unchecked";
		if (empty($nofollow_login)) 	$nofollow_login = "unchecked";
	}
?>
	
	<div class="wrap">
		<h2><?php _e('Ultimate Noindex Nofollow Options') ?></h2>
			
		<form name="unn_admin_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
			<input type="hidden" name="unn_hidden" value="Y">
            <table>
                <tr>
                	<td colspan="2">
                    	<h3>Noindex</h3>
                    	<p>Enter the page ID's of the pages you want noindexed. Separate multiple page ID's with a comma.</p>
                    	<p>Enter the ID's, names or titles of the pages you want noindexed, separated by commas. Example: <code>42,about-me,Contact</code></p>
                        <textarea name="unn_noindex_pages"><?php echo $noindex_pages; ?></textarea>
                    </td>
                </tr>
                <tr>
                	<td>&nbsp;</td>
                	<td>&nbsp;</td>
                </tr>
                <tr>
                	<td><input name="unn_noindex_date" type="checkbox" value="yes" <?php checked('yes', get_option('unn_noindex_date')); ?> /></td>
                	<td><?php _e("Add noindex meta tag to date-based archives." ); ?></td>
                </tr>
                <tr>
                	<td>&nbsp;</td>
                	<td>&nbsp;</td>
                </tr>
                <tr>
                	<td><input name="unn_noindex_paged" type="checkbox" value="yes" <?php checked('yes', get_option('unn_noindex_paged')); ?> /></td>
                	<td><?php _e("Add noindex meta tag to page-based archives." ); ?></td>
                </tr>
                <tr>
                	<td>&nbsp;</td>
                	<td>&nbsp;</td>
                </tr>
                <tr>
                	<td><input name="unn_noindex_search" type="checkbox" value="yes" <?php checked('yes', get_option('unn_noindex_search')); ?> /></td>
                	<td><?php _e("Add noindex meta tag to search result pages." ); ?></td>
                </tr>
                <tr>
                	<td>&nbsp;</td>
                	<td>&nbsp;</td>
                </tr>
                <tr>
                	<td><input name="unn_noindex_cat" type="checkbox" value="yes" <?php checked('yes', get_option('unn_noindex_cat')); ?> /></td>
                	<td><?php _e("Add noindex meta tag to category pages." ); ?></td>
                </tr>
                <tr>
                	<td>&nbsp;</td>
                	<td>&nbsp;</td>
                </tr>
                <tr>
                	<td><input name="unn_noindex_tags" type="checkbox" value="yes" <?php checked('yes', get_option('unn_noindex_tags')); ?> /></td>
                	<td><?php _e("Add noindex meta tag to tag pages." ); ?></td>
                </tr>
                <tr>
                	<td>&nbsp;</td>
                	<td>&nbsp;</td>
                </tr>
                <tr>
                	<td><input name="unn_noindex_auth" type="checkbox" value="yes" <?php checked('yes', get_option('unn_noindex_auth')); ?> /></td>
                	<td><?php _e("Add noindex meta tag to author pages." ); ?></td>
                </tr>
                <tr>
                	<td>&nbsp;</td>
                	<td>&nbsp;</td>
                </tr>
                <tr>
                	<td><input name="unn_noindex_login" type="checkbox" value="yes" <?php checked('yes', get_option('unn_noindex_login')); ?> /></td>
                	<td><?php _e("Add noindex meta tag to login page." ); ?></td>
                </tr>
                <tr>
                	<td>&nbsp;</td>
                	<td>&nbsp;</td>
                </tr>
                <tr>
                	<td><input name="unn_noindex_admin" type="checkbox" value="yes" <?php checked('yes', get_option('unn_noindex_admin')); ?> /></td>
                	<td><?php _e("Add noindex meta tag to admin pages." ); ?></td>
                </tr>
                <tr>
                	<td>&nbsp;</td>
                	<td>&nbsp;</td>
                </tr>
                <tr>
                	<td colspan="2">
                    	<h3>Nofollow</h3>
                    	<p>Enter page ID's or page slugs of the pages you want nofollowed. Separate multiple page ID's <br>
                        or page slugs with a comma.</p>
                        <p><small><strong>Note:</strong> Page ID's and page slugs are not interchangable here. You must use one or the other <br>
                        depending on how your permalinks are structured.</small></p>
                        <textarea name="unn_nofollow_pages"><?php echo $nofollow_pages; ?></textarea>
                    </td>
                </tr>
                <tr>
                	<td colspan="2">
                        <p><small>Choose only one of these, as you probably don't want to nofollow both categories and archives.</small></p>
                    </td>
                </tr>
                <tr>
                	<td><input name="unn_nofollow_archives" type="checkbox" value="yes" <?php checked('yes', get_option('unn_nofollow_archives')); ?> /></td>
                	<td><?php _e("Nofollow the archive links in your theme." ); ?></td>
                </tr>
                <tr>
                	<td>&nbsp;</td>
                	<td>&nbsp;</td>
                </tr>
                <tr>
                	<td><input name="unn_nofollow_cats" type="checkbox" value="yes" <?php checked('yes', get_option('unn_nofollow_cats')); ?> /></td>
                	<td><?php _e("Nofollow the category links in your theme." ); ?></td>
                </tr>
                <tr>
                	<td>&nbsp;</td>
                	<td>&nbsp;</td>
                </tr>
                <tr>
                	<td>&nbsp;</td>
                	<td>&nbsp;</td>
                </tr>
                <tr>
                	<td><input name="unn_nofollow_register" type="checkbox" value="yes" <?php checked('yes', get_option('unn_nofollow_register')); ?> /></td>
                	<td><?php _e("Nofollow the register link in your theme." ); ?></td>
                </tr>
                <tr>
                	<td>&nbsp;</td>
                	<td>&nbsp;</td>
                </tr>
                <tr>
                	<td><input name="unn_nofollow_login" type="checkbox" value="yes" <?php checked('yes', get_option('unn_nofollow_login')); ?> /></td>
                	<td><?php _e("Nofollow the login link in your theme." ); ?></td>
                </tr>
                <tr>
                	<td colspan="2"><p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options') ?>" /></p></td>
                </tr>
            </table>
		</form>
	</div>
<?php
}
?>
