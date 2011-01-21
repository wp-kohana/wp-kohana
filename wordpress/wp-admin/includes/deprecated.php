<?php
/**
 * Deprecated admin functions from past WordPress versions. You shouldn't use these
 * functions and look for the alternatives instead. The functions will be removed
 * in a later version.
 *
 * @package WordPress
 * @subpackage Deprecated
 */

/*
 * Deprecated functions come here to die.
 */

/**
 * @since 2.1
 * @deprecated 2.1
 * @deprecated Use wp_tiny_mce().
 * @see wp_tiny_mce()
 */
function tinymce_include() {
	_deprecated_function( __FUNCTION__, '2.1', 'wp_tiny_mce()' );

	wp_tiny_mce();
}

/**
 * Unused Admin function.
 *
 * @since 2.0
 * @deprecated 2.5
 *
 */
function documentation_link() {
	_deprecated_function( __FUNCTION__, '2.5', '' );
	return;
}

/**
 * Calculates the new dimentions for a downsampled image.
 *
 * @since 2.0.0
 * @deprecated 3.0.0
 * @deprecated Use wp_constrain_dimensions()
 *
 * @param int $width Current width of the image
 * @param int $height Current height of the image
 * @param int $wmax Maximum wanted width
 * @param int $hmax Maximum wanted height
 * @return mixed Array(height,width) of shrunk dimensions.
 */
function wp_shrink_dimensions( $width, $height, $wmax = 128, $hmax = 96 ) {
	_deprecated_function( __FUNCTION__, '3.0', 'wp_constrain_dimensions()' );
	return wp_constrain_dimensions( $width, $height, $wmax, $hmax );
}

/**
 * {@internal Missing Short Description}}
 *
 * @since unknown
 * @deprecated unknown
 * @deprecated Use wp_category_checklist()
 * @see wp_category_checklist()
 *
 * @param unknown_type $default
 * @param unknown_type $parent
 * @param unknown_type $popular_ids
 */
function dropdown_categories( $default = 0, $parent = 0, $popular_ids = array() ) {
	_deprecated_function( __FUNCTION__, '0.0', 'wp_category_checklist()' );
	global $post_ID;
	wp_category_checklist( $post_ID );
}

/**
 * {@internal Missing Short Description}}
 *
 * @since unknown
 * @deprecated unknown
 * @deprecated Use wp_link_category_checklist()
 * @see wp_link_category_checklist()
 *
 * @param unknown_type $default
 */
function dropdown_link_categories( $default = 0 ) {
	_deprecated_function( __FUNCTION__, '0.0', 'wp_link_category_checklist()' );
	global $link_id;
	wp_link_category_checklist( $link_id );
}

/**
 * {@internal Missing Short Description}}
 *
 * @since unknown
 * @deprecated 3.0.0
 * @deprecated Use wp_dropdown_categories()
 * @see wp_dropdown_categories()
 *
 * @param unknown_type $currentcat
 * @param unknown_type $currentparent
 * @param unknown_type $parent
 * @param unknown_type $level
 * @param unknown_type $categories
 * @return unknown
 */
function wp_dropdown_cats( $currentcat = 0, $currentparent = 0, $parent = 0, $level = 0, $categories = 0 ) {
	_deprecated_function( __FUNCTION__, '3.0', 'wp_dropdown_categories()' );
	if (!$categories )
		$categories = get_categories( array('hide_empty' => 0) );

	if ( $categories ) {
		foreach ( $categories as $category ) {
			if ( $currentcat != $category->term_id && $parent == $category->parent) {
				$pad = str_repeat( '&#8211; ', $level );
				$category->name = esc_html( $category->name );
				echo "\n\t<option value='$category->term_id'";
				if ( $currentparent == $category->term_id )
					echo " selected='selected'";
				echo ">$pad$category->name</option>";
				wp_dropdown_cats( $currentcat, $currentparent, $category->term_id, $level +1, $categories );
			}
		}
	} else {
		return false;
	}
}

/**
 * Register a setting and its sanitization callback
 *
 * @since 2.7.0
 * @deprecated 3.0.0
 * @deprecated Use register_setting()
 * @see register_setting()
 *
 * @param string $option_group A settings group name.  Should correspond to a whitelisted option key name.
 * 	Default whitelisted option key names include "general," "discussion," and "reading," among others.
 * @param string $option_name The name of an option to sanitize and save.
 * @param unknown_type $sanitize_callback A callback function that sanitizes the option's value.
 * @return unknown
 */
function add_option_update_handler( $option_group, $option_name, $sanitize_callback = '' ) {
	_deprecated_function( __FUNCTION__, '3.0', 'register_setting()' );
	return register_setting( $option_group, $option_name, $sanitize_callback );
}

/**
 * Unregister a setting
 *
 * @since 2.7.0
 * @deprecated 3.0.0
 * @deprecated Use unregister_setting()
 * @see unregister_setting()
 *
 * @param unknown_type $option_group
 * @param unknown_type $option_name
 * @param unknown_type $sanitize_callback
 * @return unknown
 */
function remove_option_update_handler( $option_group, $option_name, $sanitize_callback = '' ) {
	_deprecated_function( __FUNCTION__, '3.0', 'unregister_setting()' );
	return unregister_setting( $option_group, $option_name, $sanitize_callback );
}

/**
 * Determines the language to use for CodePress syntax highlighting.
 *
 * @since 2.8.0
 * @deprecated 3.0.0
 *
 * @param string $filename
**/
function codepress_get_lang( $filename ) {
	_deprecated_function( __FUNCTION__, '3.0' );
	return;
}

/**
 * Adds Javascript required to make CodePress work on the theme/plugin editors.
 *
 * @since 2.8.0
 * @deprecated 3.0.0
**/
function codepress_footer_js() {
	_deprecated_function( __FUNCTION__, '3.0' );
	return;
}

/**
 * Determine whether to use CodePress.
 *
 * @since 2.8
 * @deprecated 3.0.0
**/
function use_codepress() {
	_deprecated_function( __FUNCTION__, '3.0' );
	return;
}

?>