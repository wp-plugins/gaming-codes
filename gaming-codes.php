<?php
/*
Plugin Name: Gaming Codes
Plugin URI: http://alumnos.dcc.uchile.cl/~egraells
Description: A plugin that allows your users to have Gamer Codes. Also, if you have 'El Aleph' (another plugin) installed, you can make list of users for each console.
Version: 1.5
Author URI: http://alumnos.dcc.uchile.cl/~egraells

This plugin is licensed under the terms of the General Public License. Please see the file license.txt.

*/

/// initialize this plugin

add_action('init', 'gaming_codes_initialize', 100);

function gaming_codes_initialize() {
	
	load_plugin_textdomain("gaming-codes", 'wp-content/plugins/gaming-codes');
	
	global $gaming_fields;
	
	$gaming_fields = array(
		'nds' => array('id' => 'gaming_code-nds', 'type' => __('console', 'gaming-codes'), 'description' => __("Nintendo DS Friend Code", "gaming-codes")),
		'wii' => array('id' => 'gaming_code-wii', 'type' => __('console', 'gaming-codes'), 'description' => __("Nintendo Wii Friend Code", "gaming-codes")),
		'x360' => array('id' => 'gaming_code-x360', 'type' => __('console', 'gaming-codes'), 'description' => __("XBox-Live Gamer Tag", "gaming-codes")),
		'ps3' => array('id' => 'gaming_code-ps3', 'type' => __('console', 'gaming-codes'), 'description' => __("PlayStation Network ID", "gaming-codes")),
		'ggpo' => array('id' => 'gaming_code-ggpo', 'type' => __('emulation', 'gaming-codes'), 'description' => __("GGPO.net Username", "gaming-codes"))
	);	
	
	if (function_exists('aleph_register_user_view')) {
		foreach ($gaming_fields as $slug => $field) {
			$view_slug = 'gaming-codes-' . $slug;
			$view_title = sprintf(__("Users with a %s", 'gaming-codes'), $field['description']);
			$view_query = '_user_key=' . $field['id'];
			$view_url_slug = $field['type'] . '/' . $slug;
			aleph_register_user_view($view_slug, $view_title, $view_query, $view_url_slug);
		}
	}	
}

register_activation_hook(__FILE__, 'gaming_codes_activation');

function gaming_codes_activation() {
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}

/// print the profile form

add_action('show_user_profile', 'gaming_codes_form');
add_action('edit_user_profile', 'gaming_codes_form');

function gaming_codes_form() {
	global $gaming_fields;
	global $userdata;

	$user_id = $userdata->ID;

	if (isset($_GET['user_id'])) $user_id = (int) $_GET['user_id'];
	if ($user_id < 0) wp_die(__("What are you looking at?", "gaming-codes"));

	echo '<h3>' . __('Gaming Codes', 'gaming-codes') . '</h3>';
	echo '<table class="form-table"><tbody>';

	foreach ($gaming_fields as $field) {
		echo '<tr>';
		echo '<th><label for="' . $field['id'] . '">' . $field['description'] . '</label></th>';
		echo '<td>';
		echo '<input type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . get_usermeta($user_id, $field['id']) . '" />';
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

/// process the profile form

add_action('profile_update', 'gaming_codes_process_form');

/// @todo: check how to secure the values read from post

function gaming_codes_process_form($user_id) {
	global $gaming_fields;
	foreach ($gaming_fields as $slug => $field) {
		if (isset($_POST[$field['id']]) && !empty($_POST[$field['id']]))
			update_usermeta($user_id, $field['id'], stripslashes($_POST[$field['id']]));
		else
			delete_usermeta($user_id, $field['id']);
	}
}

/// template tags for authors (post loops) & users (user loops).

function the_user_gaming_codes($before = '', $after = '', $u = NULL) {
	global $user;
	global $gaming_fields;
	if (!$gaming_fields) return;
	if (!$u) {
		$u = $user;
	}
	if ($u) {
		$codes = array();
		
		foreach ($gaming_fields as $slug => $field) {
			$field_key = preg_replace('|[^a-z0-9_]|i', '', $field['id']);
			if (empty($field_key))
				continue;
			if ($u->{$field_key}) 
				$codes[] = '<li class="' . $slug . '"><span>' . $field['description'] . '</span> ' . $u->{$field_key} . '</li>';
			else {
				$value = get_usermeta($u->ID, $field_key);
				if (!empty($value)) 
					$codes[] = '<li class="' . $slug . '"><span>' . $field['description'] . '</span> ' . $value . '</li>';
			}
		}
		
		if (!empty($codes)) {
			echo $before;
			echo '<ul class="gaming-codes">';
			echo implode(" ", $codes);
			echo '</ul>';
			echo $after;
		}
	}
}

function the_author_gaming_codes($before = '', $after = '') {
	global $authordata;
	if ($authordata)
		the_user_gaming_codes($before, $after, $authordata);
}

/// the following is for the plugin 'El Aleph'

/// conditional tag for your templates

function is_gaming_code_listing() {
	global $gaming_fields;
	if (is_user_view() && strpos(get_query_var('user_view'), 'gaming-codes-') !== false) 
		return true;
	return false;
}

function the_user_current_gaming_code($u = NULL) {
	global $user, $gaming_fields;
	if (!$u)
		$u = $user;
	if ($u) {
		$slug = str_replace('gaming-codes-', '', get_query_var('user_view'));
		if (isset($gaming_fields[$slug])) {
			$field_key = preg_replace('|[^a-z0-9_]|i', '', $gaming_fields[$slug]['id']);
			if (!empty($field_key)) {
				$code = '';
				if ($u->{$field_key}) 
					$code = '<li class="' . $slug . '"><span>' . $gaming_fields[$slug]['description'] . '</span> ' . $u->{$field_key} . '</li>';
				else {
					$value = get_usermeta($u->ID, $field_key);
					if ($value != '') 
						$code = '<li class="' . $slug . '"><span>' . $gaming_fields[$slug]['description'] . '</span> ' . $value . '</li>';
				}
				if (!empty($code))
					echo '<ul class="gaming-codes">' . $code . '</ul>';
			}
		}
	}
}

// gamercard

function the_user_gamercard($u = NULL) {
	global $user;
	if (!$u)
		$u = $user;
	if ($u) { 
		$field = preg_replace('|[^a-z0-9_]|i', '', 'gaming_code-x360');
		$tag = $u->{$field};
		if (empty($tag))
			$tag = get_usermeta($u->ID, $field);
		if (!empty($tag))
			echo '<iframe src="http://gamercard.xbox.com/' . urlencode($tag) . '.card" frameborder="0" height="141" scrolling="no" width="204"></iframe>';
	}	
}

function the_author_gamercard() {
	global $authordata;
	the_user_gamercard($authordata);
}

?>