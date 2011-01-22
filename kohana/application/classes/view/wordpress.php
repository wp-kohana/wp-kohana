<?php defined('SYSPATH') or die('No direct script access.');

abstract class View_Wordpress extends View_Website
{
	protected $_layout = 'layout/default';

	public function logged_in()
	{
		return ($this->auth->loaded());
	}

	public function language_attributes()
	{
		ob_start();

		language_attributes();

		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function bloginfo()
	{
		return function($string){
			return get_bloginfo($string);
		};
	}

	public function is_singular()
	{
		return is_singular();
	}

	public function comment_reply_script()
	{
		if ( ! is_singular() || ! get_option('thread_comments'))
			return FALSE;

		ob_start();

		wp_enqueue_script('comment-reply');

		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function body_class()
	{
		ob_start();

		body_class();

		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function wp_enqueue_script()
	{
		return function($string){
			ob_start();

			wp_enqueue_script($string);

			$content = ob_get_contents();
			ob_end_clean();

			return $content;
		};
	}

	public function wp_head()
	{
		ob_start();

		wp_head();

		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function wp_footer()
	{
		ob_start();

		wp_footer();

		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function get_sidebar()
	{
		return function($string){
			ob_start();

			if ($string == '-')
			{
				get_sidebar();
			}
			else
			{
				get_sidebar($string);
			}

			$content = ob_get_contents();
			ob_end_clean();

			return $content;
		};
	}

	public function the_content()
	{
		return get_the_content();
	}

	public function loop_index()
	{
		ob_start();

		get_template_part('loop', 'index');

		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function comments_template()
	{
		ob_start();

		comments_template('', TRUE);

		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function the_posts()
	{
		if ( ! have_posts())
			return FALSE;

		$data = array();

		while (have_posts())
		{
			the_post();

			$item = array();

			ob_start();
			previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'twentyten' ) . '</span> %title' );
			$item['previous_post_link'] = ob_get_contents();
			ob_end_clean();

			ob_start();
			next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'twentyten' ) . '</span>' );
			$item['next_post_link'] = ob_get_contents();
			ob_end_clean();

			$item['posted_on'] = get_the_date();
			$item['author'] = get_the_author();

			ob_start();
			twentyten_posted_in();
			$item['twentyten_posted_in'] = ob_get_contents();
			ob_end_clean();

			if (($has_author_meta = (bool)get_the_author_meta('description')) === TRUE)
			{
				$item['avatar'] = get_avatar(get_the_author_meta('user_email' ), apply_filters('twentyten_author_bio_avatar_size', 60));
				$item['author_description'] = sprintf(esc_attr__('About %s', 'twentyten'), get_the_author());
				$item['author_description'] = get_the_author_meta('description');
				$item['author_url'] = get_author_posts_url(get_the_author_meta('ID'));
				$item['author_all_posts'] = sprintf( __( 'View all posts by %s <span class="meta-nav">&rarr;</span>', 'twentyten'), get_the_author());
			}

			ob_start();
			the_ID();
			$item['the_ID'] = ob_get_contents();
			ob_end_clean();

			ob_start();
			post_class();
			$item['post_class'] = ob_get_contents();
			ob_end_clean();

			$item['is_front_page'] = is_front_page();

			ob_start();
			the_title();
			$item['the_title'] = ob_get_contents();
			ob_end_clean();

			ob_start();
			the_content();
			$item['the_content'] = ob_get_contents();
			ob_end_clean();

			ob_start();
			wp_link_pages(array('before' => '<div class="page-link">'.__('Pages:', 'twentyten'), 'after' => '</div>'));
			$item['wp_link_pages'] = ob_get_contents();
			ob_end_clean();

			ob_start();
			edit_post_link( __('Edit', 'twentyten'), '<span class="edit-link">', '</span>');
			$item['edit_post_link'] = ob_get_contents();
			ob_end_clean();

			ob_start();
			comments_template( '', true );
			$item['comments_template'] = ob_get_contents();
			ob_end_clean();

			$data[] = $item;
		}

		return $this->the_posts = $data;
	}
}
