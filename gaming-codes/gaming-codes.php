<?php
/*
Plugin Name: Gaming Codes
Plugin URI: http://www.ryuuko.cl/desarrollo/gaming-codes
Description: A plugin that allows your users to have Gamer Codes. Also, if you have 'El Aleph' (another plugin) installed, you can make list of users for each console.
Version: 1.0
Author URI: http://alumnos.dcc.uchile.cl/~egraells

This plugin is licensed under the terms of the General Public License. Please see the file license.txt.

*/

/// initialize this plugin

add_action('init', 'gaming_codes_initialize', 100);

function gaming_codes_initialize() {
	global $gaming_fields;
	if (!class_exists('ElAleph')) return;
	
	$gaming_fields = array(
		'nds' => array('id' => 'gaming_code-nds', 'type' => __('console', 'gaming-codes'), 'description' => __("Nintendo DS Friend Code", "gaming-codes")),
		'wii' => array('id' => 'gaming_code-wii', 'type' => __('console', 'gaming-codes'), 'description' => __("Nintendo Wii Friend Code", "gaming-codes")),
		'x360' => array('id' => 'gaming_code-x360', 'type' => __('console', 'gaming-codes'), 'description' => __("XBox-Live Gamer Tag", "gaming-codes")),
		'ps3' => array('id' => 'gaming_code-ps3', 'type' => __('console', 'gaming-codes'), 'description' => __("PlayStation Network ID", "gaming-codes")),
		'ggpo' => array('id' => 'gaming_code-ggpo', 'type' => __('emulation', 'gaming-codes'), 'description' => __("GGPO.net Username", "gaming-codes"))
	);	
	
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
	if ($user_id < 0) wp_die(__("What are you looking at?", "aleph"));

	echo '<h3>' . __('Gaming Codes', 'aleph') . '</h3>';
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
		if (isset($_POST[$field['id']]))
			update_usermeta($user_id, $field['id'], stripslashes($_POST[$field['id']]));
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
			if ($u->{$field['id']}) 
				$codes[] = '<li class="' . $slug . '"><span>' . $field['description'] . '</span> ' . $u->{$field['id']} . '</li>';
			else {
				$value = get_usermeta($u->ID, $field['id']);
				if ($value != '') $codes[] = '<li class="' . $slug . '"><span>' . $field['description'] . '</span> ' . $value . '</li>';
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

add_filter('query_vars', 'gaming_codes_query_vars');

function gaming_codes_query_vars($qvars) {
	$qvars[] = 'gaming_code';
	return $qvars;
}

add_filter('users_query', 'gaming_codes_users_query');

function gaming_codes_users_query($query) {
	if ('author-list' == get_query_var('pagename') && '' != get_query_var('gaming_code'))
		$query .= '&gaming_code=' . get_query_var('gaming_code');
	return $query;
}

add_filter('users_join', 'gaming_codes_users_join');

function gaming_codes_users_join($join) {
	global $wpdb;
	if ('' !== get_query_var('gaming_code'))
		$join .= " INNER JOIN $wpdb->usermeta ON ($wpdb->users.ID = $wpdb->usermeta.user_id) ";
	return $join;
}

add_filter('users_where', 'gaming_code_users_where');
function gaming_code_users_where($where) {
	global $wpdb, $el_aleph_uq, $gaming_fields;
	if ('' != get_query_var('gaming_code')) {
		if (isset($gaming_fields[get_query_var('gaming_code')])) {
			$el_aleph_uq->queried_object = new stdClass;
			/// @todo: have a better queried object
			$el_aleph_uq->queried_object = $gaming_fields[get_query_var('gaming_code')];
			$meta_key = preg_replace('|[^a-z0-9_]|i', '', $el_aleph_uq->queried_object['id']); // from update usermeta
			$where .= " AND $wpdb->usermeta.meta_key = '$meta_key' ";
		} 
		else $where .= " AND 0 ";
	}
	return $where;
}

/// filter for fake post title

add_filter('users_template_title', 'gaming_code_page_title');

function gaming_code_page_title($title) {
	global $gaming_fields;
	if (isset($gaming_fields[get_query_var('gaming_code')])) 
		return sprintf(__("Users with a %s", 'gaming-codes'), $gaming_fields[get_query_var('gaming_code')]['description']);
	else 
		return $title;
}

/// conditional tag for your templates

function is_gaming_code_listing() {
	return is_user_list() && ('' != get_query_var('gaming_code'));
}

/// rewrite rules

add_filter('rewrite_rules_array', 'gaming_codes_rewrite_rules');

function gaming_codes_rewrite_rules($rewrite) {
	global $gaming_fields;

	$gaming_rules = array();

	foreach ($gaming_fields as $slug => $label) {
		$gaming_rules[__('searching', 'aleph') . '/' . __('people', 'aleph') . '/' . $label['type'] . '/' . $slug . '/page/?([0-9]{1,})/?$'] = 'index.php?pagename=author-list&gaming_code=' . $slug . '&paged=$matches[1]';
		$gaming_rules[__('searching', 'aleph') . '/' . __('people', 'aleph') . '/' . $label['type'] . '/' . $slug . '/?$'] = 'index.php?pagename=author-list&gaming_code=' . $slug;
	}

	return $gaming_rules + $rewrite;
}

/// template tag to display links to the user lists

function gaming_codes_links($before = '<ul class="gaming-codes-links"><li>', $after = '</li></ul>', $separator = '</li><li>') {
	global $wp_rewrite, $gaming_fields;
	$links = array();
	if ($wp_rewrite->using_permalinks()) {
		foreach ($gaming_fields as $name => $label)
			$links[] = '<a href="' . get_option('siteurl') . '/' . __('searching', 'aleph') . '/' . __('people', 'aleph') . '/' . $label['type'] . '/' . $name . '">' . sprintf(__("See users with a %s", 'gaming-codes'), $label['description']) . '</a>';
	} else {
		foreach ($gaming_fields as $name => $label) 
			$links[] = '<a href="' . get_option('siteurl') . '?pagename=author-interests&gaming_code=' . $name . '">' . sprintf(__("See users with a %s", 'gaming-codes'), $label['description']) . '</a>';
	}
	if (!empty($links)) 
		echo $before . implode($separator, $links) . $after;
}

?>