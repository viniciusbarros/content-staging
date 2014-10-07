<?php
namespace Me\Stenberg\Content\Staging\View;

use Me\Stenberg\Content\Staging\Models\Batch;
use Me\Stenberg\Content\Staging\Models\Post;
use WP_List_Table;

class Post_Table extends WP_List_Table {

	/**
	 * @var Batch
	 */
	private $batch;

	/**
	 * @param Batch $batch
	 */
	public function __construct( Batch $batch ) {

		// Set parent defaults.
		parent::__construct( array(
			'singular'  => 'post',
			'plural'    => 'posts',
			'ajax'      => false
		) );

		$this->batch = $batch;
	}

	/**
	 * Called if a column does not have a method that provides logic for
	 * rendering that column.
	 *
	 * @param Post $post
	 * @param array $column_name
	 * @return string Text or HTML to be placed inside the column.
	 */
	public function column_default( Post $post, $column_name ) {
		switch( $column_name ) {
			case 'post_modified':
				return call_user_func( array( $post, 'get_modified' ) );
			default:
				return '';
		}
	}

	/**
	 * Render the 'post_title' column.
	 *
	 * @param Post $post
	 * @return string HTML to be rendered inside column.
	 */
	public function column_post_title( Post $post ) {
		return sprintf(
			'<strong><span class="row-title">%s</span></strong>',
			$post->get_title()
		);
	}

	/**
	 * Display checkbox (e.g. for bulk actions). The checkbox should have the
	 * value of the post ID.
	 *
	 * @param Post $post
	 * @return string Text to be placed inside the column.
	 */
	public function column_cb( Post $post ) {
		return sprintf(
			'<input type="checkbox" class="sme-select-post" name="%s[]" value="%s"/>',
			$this->_args['plural'],
			$post->get_id()
		);
	}

	/**
	 * Set the table's columns and titles.
	 *
	 * The column named 'cb' will display checkboxes. Make sure to create a
	 * column_cb method for setting up the checkbox column.
	 *
	 * @return array An associative array:
	 * Key = Column name
	 * Value = Column title (except for key 'cb')
	 */
	public function get_columns() {
		return array(
			'cb'            => '<input type="checkbox" />',
			'post_title'    => 'Post Title',
			'post_modified' => 'Modified',
		);
	}

	/**
	 * Make columns sortable.
	 *
	 * @return array An associative array containing sortable columns:
	 * Key = Column name
	 * Value = array( value from database (most likely), bool )
	 */
	public function get_sortable_columns() {
		return array(
			'post_title'    => array( 'post_title', false ),
			'post_modified' => array( 'post_modified', false ),
		);
	}

	/**
	 * Prepare posts for being displayed.
	 */
	public function prepare_items() {

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
	}

}