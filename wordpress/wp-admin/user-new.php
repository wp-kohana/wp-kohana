<?php
/**
 * New User Administration Panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once('./admin.php');

if ( !current_user_can('create_users') )
	wp_die(__('Cheatin&#8217; uh?'));

if ( is_multisite() && !get_site_option( 'add_new_users' ) )
	wp_die( __('Page disabled by the administrator') );

/** WordPress Registration API */
require_once( ABSPATH . WPINC . '/registration.php');

if ( is_multisite() ) {
	function admin_created_user_email( $text ) {
		return sprintf( __( "Hi,
You've been invited to join '%s' at
%s as a %s.
If you do not want to join this site please ignore
this email. This invitation will expire in a few days.

Please click the following link to activate your user account:
%%s" ), get_bloginfo('name'), site_url(), esc_html( $_REQUEST[ 'role' ] ) );
	}
	add_filter( 'wpmu_signup_user_notification_email', 'admin_created_user_email' );

	function admin_created_user_subject( $text ) {
		return "[" . get_bloginfo('name') . "] Your site invite";
	}
}

if ( isset($_REQUEST['action']) && 'adduser' == $_REQUEST['action'] ) {
	check_admin_referer('add-user');

	if ( !is_multisite() ) {
		$user_id = add_user();

		if ( is_wp_error( $user_id ) ) {
			$add_user_errors = $user_id;
		} else {
			if ( current_user_can('edit_users') ) {
				$new_user_login = apply_filters('pre_user_login', sanitize_user(stripslashes($_REQUEST['user_login']), true));
				$redirect = 'users.php?usersearch='. urlencode($new_user_login) . '&update=add' . '#user-' . $user_id;
			} else {
				$redirect = add_query_arg( 'update', 'add', 'user-new.php' );
			}
			wp_redirect( $redirect );
			die();
		}
	} else {
		$user_login = preg_replace( "/\s+/", '', sanitize_user( $_REQUEST[ 'user_login' ], true ) );
		$user_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->users} WHERE user_login = %s AND user_email = %s", $user_login, $_REQUEST[ 'email' ] ) );
		if ( $user_details ) {
			// Adding an existing user to this blog
			$new_user_email = esc_html(trim($_REQUEST['email']));
			$redirect = 'user-new.php';
			$username = $user_details->user_login;
			$user_id = $user_details->ID;
			if ( ( $username != null && !is_super_admin( $user_id ) ) && ( array_key_exists($blog_id, get_blogs_of_user($user_id)) ) ) {
				$redirect = add_query_arg( array('update' => 'addexisting'), 'user-new.php' );
			} else {
				if ( isset( $_POST[ 'noconfirmation' ] ) && is_super_admin() ) {
					add_existing_user_to_blog( array( 'user_id' => $user_id, 'role' => $_REQUEST[ 'role' ] ) );
					$redirect = add_query_arg( array('update' => 'addnoconfirmation'), 'user-new.php' );
				} else {
					$newuser_key = substr( md5( $user_id ), 0, 5 );
					add_option( 'new_user_' . $newuser_key, array( 'user_id' => $user_id, 'email' => $user_details->user_email, 'role' => $_REQUEST[ 'role' ] ) );
					$message = __("Hi,\n\nYou have been invited to join '%s' at\n%s as a %s.\nPlease click the following link to confirm the invite:\n%s\n");
					wp_mail( $new_user_email, sprintf( __( '[%s] Joining confirmation' ), get_option( 'blogname' ) ),  sprintf($message, get_option('blogname'), site_url(), $_REQUEST[ 'role' ], site_url("/newbloguser/$newuser_key/")));
					$redirect = add_query_arg( array('update' => 'add'), 'user-new.php' );
				}
			}
			wp_redirect( $redirect );
			die();
		} else {
			// Adding a new user to this blog
			$user_details = wpmu_validate_user_signup( $_REQUEST[ 'user_login' ], $_REQUEST[ 'email' ] );
			unset( $user_details[ 'errors' ]->errors[ 'user_email_used' ] );
			if ( is_wp_error( $user_details[ 'errors' ] ) && !empty( $user_details[ 'errors' ]->errors ) ) {
				$add_user_errors = $user_details[ 'errors' ];
			} else {
				$new_user_login = apply_filters('pre_user_login', sanitize_user(stripslashes($_REQUEST['user_login']), true));
				if ( isset( $_POST[ 'noconfirmation' ] ) && is_super_admin() ) {
					add_filter( 'wpmu_signup_user_notification', '__return_false' ); // Disable confirmation email
				}
				wpmu_signup_user( $new_user_login, $_REQUEST[ 'email' ], array( 'add_to_blog' => $wpdb->blogid, 'new_role' => $_REQUEST[ 'role' ] ) );
				if ( isset( $_POST[ 'noconfirmation' ] ) && is_super_admin() ) {
					$key = $wpdb->get_var( $wpdb->prepare( "SELECT activation_key FROM {$wpdb->signups} WHERE user_login = %s AND user_email = %s", $new_user_login, $_REQUEST[ 'email' ] ) );
					wpmu_activate_signup( $key );
					$redirect = add_query_arg( array('update' => 'addnoconfirmation'), 'user-new.php' );
				} else {
					$redirect = add_query_arg( array('update' => 'newuserconfimation'), 'user-new.php' );
				}
				wp_redirect( $redirect );
				die();
			}
		}
	}
}

$title = __('Add New User');
$parent_file = 'users.php';

add_contextual_help($current_screen,
    '<p>' . __('To add a new user to your site, fill in the form on this screen. If you&#8217;re not sure which role to assign, you can use the link below to review the different roles and their capabilities. Here is a basic overview of roles:') . '</p>' .
    '<ul>' .
        '<li>' . __('Administrators have access to all the administration features.') . '</li>' .
        '<li>' . __('Editors can publish posts, manage posts as well as manage other people&#8217;s posts, etc.')  . '</li>' .
        '<li>' . __('Authors can publish and manage their own posts.') . '</li>' .
        '<li>' . __('Contributors can write and manage their posts but not publish posts or upload media files.') . '</li>' .
        '<li>' . __('Subscribers can read comments/comment/receive newsletters, etc.') . '</li>' .
    '</ul>' .
    '<p>' . __('You must assign a password to the new user, but don&#8217;t worry; when they log in for the first time they will be prompted to change it. The username, however, cannot be changed.') . '</p>' .
    '<p>' . __('New users will receive an email letting them know they&#8217;ve been added as a user for your site. By default, this email will also contain their password. Uncheck the box if you don&#8217;t want the password to be included in the welcome email.') . '</p>' .
    '<p>' . __('Remember to click the Add User button at the bottom of this screen when you are finished.') . '</p>' .
    '<p><strong>' . __('For more information:') . '</strong></p>' .
    '<p>' . __('<a href="http://codex.wordpress.org/Users_Add_New_SubPanel" target="_blank">Documentation on Adding New Users</a>') . '</p>' .
    '<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
);

wp_enqueue_script('wp-ajax-response');
wp_enqueue_script('user-profile');
wp_enqueue_script('password-strength-meter');

require_once ('admin-header.php');

if ( isset($_GET['update']) ) {
	$messages = array();
	if ( is_multisite() ) {
		switch ( $_GET['update'] ) {
			case "newuserconfimation":
				$messages[] = __('Invitation email sent to new user. A confirmation link must be clicked before their account is created.');
				break;
			case "add":
				$messages[] = __('Invitation email sent to user. A confirmation link must be clicked for them to be added to your site.');
				break;
			case "addnoconfirmation":
				$messages[] = __('User has been added to your site.');
				break;
			case "addexisting":
				$messages[] = __('That user is already a member of this site.');
				break;
		}
	} else {
		if ( 'add' == $_GET['update'] )
			$messages[] = __('User added.');
	}
}
?>
<div class="wrap">
<?php screen_icon(); ?>
<h2 id="add-new-user"><?php _e('Add New User') ?></h2>

<?php if ( isset($errors) && is_wp_error( $errors ) ) : ?>
	<div class="error">
		<ul>
		<?php
			foreach ( $errors->get_error_messages() as $err )
				echo "<li>$err</li>\n";
		?>
		</ul>
	</div>
<?php endif;

if ( ! empty( $messages ) ) {
	foreach ( $messages as $msg )
		echo '<div id="message" class="updated"><p>' . $msg . '</p></div>';
} ?>

<?php if ( isset($add_user_errors) && is_wp_error( $add_user_errors ) ) : ?>
	<div class="error">
		<?php
			foreach ( $add_user_errors->get_error_messages() as $message )
				echo "<p>$message</p>";
		?>
	</div>
<?php endif; ?>
<div id="ajax-response"></div>

<?php
if ( !is_multisite() ) {
	if ( get_option('users_can_register') )
		echo '<p>' . sprintf(__('Users can <a href="%1$s">register themselves</a> or you can manually create users here.'), site_url('wp-register.php')) . '</p>';
	else
		echo '<p>' . sprintf(__('Users cannot currently <a href="%1$s">register themselves</a>, but you can manually create users here.'), admin_url('options-general.php#users_can_register')) . '</p>';
} else {
	echo '<p>' . __( 'You can add new users to your site in two ways:' ) . '<ol><li> ' . __( 'Enter the username and email address of an existing user on this site.' ) . '</li><li> ' . __( 'Enter the username and the email address of a person who is not already a member of this site. Choose the username carefully, it cannot be changed.' ) . '</li></ol></p>';
	echo '<p>' . __( 'That person will be sent an email asking them to click a link confirming the invite. New users will then be sent an email with a randomly generated password and a login link.' ) . '</p>';
}
?>
<form action="#add-new-user" method="post" name="adduser" id="adduser" class="add:users: validate"<?php do_action('user_new_form_tag');?>>
<?php wp_nonce_field('add-user') ?>
<?php
//Load up the passed data, else set to a default.
foreach ( array('user_login' => 'login', 'first_name' => 'firstname', 'last_name' => 'lastname',
				'email' => 'email', 'url' => 'uri', 'role' => 'role') as $post_field => $var ) {
	$var = "new_user_$var";
	if ( ! isset($$var) )
		$$var = isset($_POST[$post_field]) ? stripslashes($_POST[$post_field]) : '';
}
$new_user_send_password = !$_POST || isset($_POST['send_password']);
?>
<table class="form-table">
	<tr class="form-field form-required">
		<th scope="row"><label for="user_login"><?php _e('Username'); ?> <span class="description"><?php _e('(required)'); ?></span></label>
		<input name="action" type="hidden" id="action" value="adduser" /></th>
		<td><input name="user_login" type="text" id="user_login" value="<?php echo esc_attr($new_user_login); ?>" aria-required="true" /></td>
	</tr>
	<tr class="form-field form-required">
		<th scope="row"><label for="email"><?php _e('E-mail'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
		<td><input name="email" type="text" id="email" value="<?php echo esc_attr($new_user_email); ?>" /></td>
	</tr>
<?php if ( !is_multisite() ) { ?>
	<tr class="form-field">
		<th scope="row"><label for="first_name"><?php _e('First Name') ?> </label></th>
		<td><input name="first_name" type="text" id="first_name" value="<?php echo esc_attr($new_user_firstname); ?>" /></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="last_name"><?php _e('Last Name') ?> </label></th>
		<td><input name="last_name" type="text" id="last_name" value="<?php echo esc_attr($new_user_lastname); ?>" /></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="url"><?php _e('Website') ?></label></th>
		<td><input name="url" type="text" id="url" class="code" value="<?php echo esc_attr($new_user_uri); ?>" /></td>
	</tr>
<?php if ( apply_filters('show_password_fields', true) ) : ?>
	<tr class="form-field form-required">
		<th scope="row"><label for="pass1"><?php _e('Password'); ?> <span class="description"><?php /* translators: password input field */_e('(twice, required)'); ?></span></label></th>
		<td><input name="pass1" type="password" id="pass1" autocomplete="off" />
		<br />
		<input name="pass2" type="password" id="pass2" autocomplete="off" />
		<br />
		<div id="pass-strength-result"><?php _e('Strength indicator'); ?></div>
		<p class="description indicator-hint"><?php _e('Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).'); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="send_password"><?php _e('Send Password?') ?></label></th>
		<td><label for="send_password"><input type="checkbox" name="send_password" id="send_password" <?php checked($new_user_send_password, true); ?> /> <?php _e('Send this password to the new user by email.'); ?></label></td>
	</tr>
<?php endif; ?>
<?php } // !is_multisite ?>
	<tr class="form-field">
		<th scope="row"><label for="role"><?php _e('Role'); ?></label></th>
		<td><select name="role" id="role">
			<?php
			if ( !$new_user_role )
				$new_user_role = !empty($current_role) ? $current_role : get_option('default_role');
			wp_dropdown_roles($new_user_role);
			?>
			</select>
		</td>
	</tr>
	<?php if ( is_multisite() && is_super_admin() ) { ?>
	<tr>
		<th scope="row"><label for="noconfirmation"><?php _e('Skip Confirmation Email') ?></label></th>
		<td><label for="noconfirmation"><input type="checkbox" name="noconfirmation" id="noconfirmation" value="1" /> <?php _e( 'Site administrators can add a user without sending the confirmation email.' ); ?></label></td>
	</tr>
	<?php } ?>
</table>
<p class="submit">
	<input name="adduser" type="submit" id="addusersub" class="button-primary" value="<?php esc_attr_e('Add User') ?>" />
</p>
</form>

</div>
<?php
include('./admin-footer.php');
?>
