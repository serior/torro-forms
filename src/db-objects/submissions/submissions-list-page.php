<?php
/**
 * Submissions list page class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Submissions;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Models_List_Page;
use Leaves_And_Love\Plugin_Lib\Components\Admin_Pages;

/**
 * Class representing the submissions list page in the admin.
 *
 * @since 1.0.0
 */
class Submissions_List_Page extends Models_List_Page {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string             $slug          Page slug.
	 * @param Admin_Pages        $manager       Admin page manager instance.
	 * @param Submission_Manager $model_manager Model manager instance.
	 */
	public function __construct( $slug, $manager, $model_manager ) {
		$this->list_table_class_name = Submissions_List_Table::class;

		$this->edit_page_slug = $manager->get_prefix() . 'edit_submission';

		$this->icon_url = 'dashicons-tag';

		parent::__construct( $slug, $manager, $model_manager );

		if ( ! empty( $_GET['page'] ) && $slug === $_GET['page'] && ! empty( $_GET['form_id'] ) ) {
			/* translators: %s: form title */
			$this->title = sprintf( __( 'Submissions for form &#8220;%s&#8221;', 'torro-forms' ), get_the_title( (int) $_GET['form_id'] ) );
		}
	}

	/**
	 * Renders the list page header.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render_header() {
		$capabilities = $this->model_manager->capabilities();

		$new_page_url = '';
		if ( ! empty( $this->edit_page_slug ) ) {
			$new_page_url = add_query_arg( 'page', $this->edit_page_slug, $this->url );
		}

		?>
		<h1 class="wp-heading-inline">
			<?php echo $this->title; ?>
		</h1>

		<?php if ( ! empty( $_GET['form_id'] ) ) : ?>
			<a href="<?php echo esc_url( $this->url ); ?>" class="page-title-action"><?php _ex( 'Back to Overview', 'submission admin list', 'torro-forms' ); ?></a>
		<?php elseif ( ! empty( $new_page_url ) && $capabilities && $capabilities->user_can_create() ) : ?>
			<a href="<?php echo esc_url( $new_page_url ); ?>" class="page-title-action"><?php echo $this->model_manager->get_message( 'list_page_add_new' ); ?></a>
		<?php endif; ?>

		<?php if ( isset( $_REQUEST['s'] ) && strlen( $_REQUEST['s'] ) ) : ?>
			<span class="subtitle"><?php printf( $this->model_manager->get_message( 'list_page_search_results_for' ), esc_attr( wp_unslash( $_REQUEST['s'] ) ) ); ?></span>
		<?php endif; ?>

		<hr class="wp-header-end">

		<?php

		$this->print_current_message( 'bulk_action' );
		$this->print_current_message( 'row_action' );
	}

	/**
	 * Renders the list page form.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render_form() {
		$this->list_table->views();

		?>
		<form id="<?php echo $this->model_manager->get_plural_slug(); ?>-filter" method="get">

			<?php $this->list_table->search_box( $this->model_manager->get_message( 'list_page_search_items' ), $this->model_manager->get_singular_slug() ); ?>

			<?php
			if ( $this->parent_slug && false !== strpos( $this->parent_slug, '?' ) ) {
				$query_string = wp_parse_url( self_admin_url( $this->parent_slug ), PHP_URL_QUERY );
				if ( $query_string ) {
					wp_parse_str( $query_string, $query_vars );
					foreach ( $query_vars as $key => $value ) {
						?>
						<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
						<?php
					}
				}
			}
			?>

			<input type="hidden" name="page" value="<?php echo $this->slug; ?>" />

			<?php if ( ! empty( $_GET['form_id'] ) ) : ?>
				<input type="hidden" name="form_id" value="<?php echo (int) $_GET['form_id']; ?>" />
			<?php endif; ?>

			<?php if ( method_exists( $this->model_manager, 'get_author_property' ) && ( $author_property = $this->model_manager->get_author_property() ) && ! empty( $_REQUEST[ $author_property ] ) ) : ?>
				<input type="hidden" name="<?php echo $author_property; ?>" value="<?php echo esc_attr( $_REQUEST[ $author_property ] ); ?>" />
			<?php endif; ?>

			<?php $this->list_table->display(); ?>
		</form>
		<?php
	}
}
