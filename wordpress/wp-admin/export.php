<?php
/**
 * WordPress Export Administration Panel
 *
 * @package WordPress
 * @subpackage Administration
 */

/** Load WordPress Bootstrap */
require_once ('admin.php');

if ( !current_user_can('export') )
	wp_die(__('You do not have sufficient permissions to export the content of this site.'));

/** Load WordPress export API */
require_once('./includes/export.php');
$title = __('Export');

add_contextual_help($current_screen,
	'<p>' . __('You can export a file of your site&#8217;s content in order to import it into another installation or platform. The export file will be an XML file format called WXR. Posts, pages, comments, custom fields, categories, and tags can be included. You can set filters to have the WXR file only include a certain date, author, category, tag, all posts or all pages, certain publishing statuses.') . '</p>' .
	'<p>' . __('Once generated, your WXR file can be imported by another WordPress site or by another blogging platform able to access this format.') . '</p>' .
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="http://codex.wordpress.org/Tools_Export_SubPanel" target="_blank">Export Documentation</a>') . '</p>' .
	'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
);

if ( isset( $_GET['download'] ) ) {
		$author = isset($_GET['author']) ? $_GET['author'] : 'all';
		$taxonomy = array();
		foreach ( get_taxonomies( array( 'show_ui' => true ) ) as $tax )
			$taxonomy[ $tax ] = ! empty( $_GET['export_taxonomy'][ $tax ] ) ? $_GET['export_taxonomy'][ $tax ] : 'all';
		$post_type = isset($_GET['export_post_type']) ? stripslashes_deep($_GET['export_post_type']) : 'all';
		$status = isset($_GET['export_post_status']) ? stripslashes_deep($_GET['export_post_status']) : 'all';
		$mm_start = isset($_GET['mm_start']) ? $_GET['mm_start'] : 'all';
		$mm_end = isset($_GET['mm_end']) ? $_GET['mm_end'] : 'all';
		if( $mm_start != 'all' ) {
			$start_date = sprintf( "%04d-%02d-%02d", substr( $mm_start, 0, 4 ), substr( $mm_start, 5, 2 ), 1 );
		} else {
			$start_date = 'all';
		}
		if( $mm_end != 'all' ) {
			$end_date = sprintf( "%04d-%02d-%02d", substr( $mm_end, 0, 4 ), substr( $mm_end, 5, 2 ), 1 );
		} else {
			$end_date = 'all';
		}

	export_wp( array( 'author' => $author, 'taxonomy' => $taxonomy, 'post_type' => $post_type, 'post_status' => $status, 'start_date' => $start_date, 'end_date' => $end_date ) );
	die();
}

require_once ('admin-header.php');

$dateoptions = $edateoptions = '';
$types = "'" . implode("', '", get_post_types( array( 'public' => true, 'can_export' => true ), 'names' )) . "'";
$stati = "'" . implode("', '", get_post_stati( array( 'internal' => false ), 'names' )) . "'";
if ( $monthyears = $wpdb->get_results("SELECT DISTINCT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, YEAR(DATE_ADD(post_date, INTERVAL 1 MONTH)) AS `eyear`, MONTH(DATE_ADD(post_date, INTERVAL 1 MONTH)) AS `emonth` FROM $wpdb->posts WHERE post_type IN ($types) AND post_status IN ($stati) ORDER BY post_date ASC ") ) {
	foreach ( $monthyears as $k => $monthyear )
		$monthyears[$k]->lmonth = $wp_locale->get_month( $monthyear->month, 2 );
	for( $s = 0, $e = count( $monthyears ) - 1; $e >= 0; $s++, $e-- ) {
		$dateoptions .= "\t<option value=\"" . $monthyears[$s]->year . '-' . zeroise( $monthyears[$s]->month, 2 ) . '">' . $monthyears[$s]->lmonth . ' ' . $monthyears[$s]->year . "</option>\n";
		$edateoptions .= "\t<option value=\"" . $monthyears[$e]->eyear . '-' . zeroise( $monthyears[$e]->emonth, 2 ) . '">' . $monthyears[$e]->lmonth . ' ' . $monthyears[$e]->year . "</option>\n";
	}
}

?>

<div class="wrap">
<?php screen_icon(); ?>
<h2><?php echo esc_html( $title ); ?></h2>

<p><?php _e('When you click the button below WordPress will create an XML file for you to save to your computer.'); ?></p>
<p><?php _e('This format, which we call WordPress eXtended RSS or WXR, will contain your posts, pages, comments, custom fields, categories, and tags.'); ?></p>
<p><?php _e('Once you&#8217;ve saved the download file, you can use the Import function on another WordPress site to import this site.'); ?></p>
<form action="" method="get">
<h3><?php _e('Filters'); ?></h3>

<table class="form-table">
<tr>
<th><label for="mm_start"><?php _e('Start Date'); ?></label></th>
<td>
<select name="mm_start" id="mm_start">
	<option value="all" selected="selected"><?php _e('All Dates'); ?></option>
<?php echo $dateoptions; ?>
</select>
</td>
</tr>
<tr>
<th><label for="mm_end" id="mm_end"><?php _e('End Date'); ?></label></th>
<td>
<select name="mm_end" id="mm_end">
	<option value="all" selected="selected"><?php _e('All Dates'); ?></option>
<?php echo $edateoptions; ?>
</select>
</td>
</tr>
<tr>
<th><label for="author"><?php _e('Authors'); ?></label></th>
<td>
<select name="author" id="author">
<option value="all" selected="selected"><?php _e('All Authors'); ?></option>
<?php
$authors = $wpdb->get_results( "SELECT DISTINCT u.id, u.display_name FROM $wpdb->users u INNER JOIN $wpdb->posts p WHERE u.id = p.post_author ORDER BY u.display_name" );
foreach ( (array) $authors as $author ) {
	echo "<option value='{$author->id}'>{$author->display_name}</option>\n";
}
?>
</select>
</td>
</tr>
<?php foreach ( get_taxonomies( array( 'show_ui' => true ), 'objects' ) as $tax_obj ) {
	$term_dropdown = wp_dropdown_categories( array( 'taxonomy' => $tax_obj->name, 'hide_if_empty' => true, 'show_option_all' => __( 'All Terms' ), 'name' => 'export_taxonomy[' . $tax_obj->name . ']', 'id' => 'taxonomy-' . $tax_obj->name, 'class' => '', 'echo' => false ) );
	if ( $term_dropdown )
		echo '<tr><th><label for="taxonomy-' . $tax_obj->name . '">' . $tax_obj->labels->name . '</label></th><td>' . $term_dropdown . '</td></tr>';
}
?>
<tr>
<th><label for="post_type"><?php _e('Content Types'); ?></label></th>
<td>
<select name="export_post_type" id="post_type">
	<option value="all" selected="selected"><?php _e('All Content'); ?></option>
	<?php foreach ( get_post_types( array( 'public' => true, 'can_export' => true ), 'objects' ) as $post_type_obj ) { ?>
		<option value="<?php echo $post_type_obj->name; ?>"><?php echo $post_type_obj->labels->name; ?></option>
	<?php } ?>
</select>
</td>
</tr>
<tr>
<th><label for="status"><?php _e('Statuses'); ?></label></th>
<td>
<select name="export_post_status" id="status">
	<option value="all" selected="selected"><?php _e('All Statuses'); ?></option>
<?php foreach ( get_post_stati( array( 'internal' => false ), 'objects' ) as $post_status_obj ) { ?>
	<option value="<?php echo $post_status_obj->name; ?>"><?php echo $post_status_obj->label; ?></option>
<?php } ?>
</select>
</td>
</tr>
</table>
<p class="submit"><input type="submit" name="submit" class="button" value="<?php esc_attr_e('Download Export File'); ?>" />
<input type="hidden" name="download" value="true" />
</p>
</form>
</div>

<?php


include ('admin-footer.php');
?>
