<?php
/**
 * Content Change Logger
 *
 * Logs all WordPress content changes including posts, pages, taxonomies, attachments, etc.
 *
 * @package FlatWP
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FlatWP Content Logger Class
 */
class FlatWP_Content_Logger {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		// Post lifecycle hooks
		add_action( 'transition_post_status', array( $this, 'log_post_status_change' ), 10, 3 );
		add_action( 'wp_insert_post', array( $this, 'log_post_insert' ), 10, 3 );
		add_action( 'before_delete_post', array( $this, 'log_post_delete' ), 10, 2 );
		add_action( 'post_updated', array( $this, 'log_post_update' ), 10, 3 );

		// Taxonomy hooks
		add_action( 'created_term', array( $this, 'log_term_created' ), 10, 3 );
		add_action( 'edited_term', array( $this, 'log_term_edited' ), 10, 3 );
		add_action( 'delete_term', array( $this, 'log_term_deleted' ), 10, 4 );

		// Attachment hooks
		add_action( 'add_attachment', array( $this, 'log_attachment_added' ), 10, 1 );
		add_action( 'delete_attachment', array( $this, 'log_attachment_deleted' ), 10, 2 );

		// Bulk action hooks
		add_action( 'load-edit.php', array( $this, 'detect_bulk_actions' ) );

		// Comment hooks (optional but useful)
		add_action( 'comment_post', array( $this, 'log_comment_added' ), 10, 3 );
		add_action( 'deleted_comment', array( $this, 'log_comment_deleted' ), 10, 2 );
		add_action( 'transition_comment_status', array( $this, 'log_comment_status_change' ), 10, 3 );
	}

	/**
	 * Log post status transitions
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	public function log_post_status_change( $new_status, $old_status, $post ) {
		// Skip if no actual change
		if ( $new_status === $old_status ) {
			return;
		}

		// Skip auto-drafts and revisions
		if ( wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) || $new_status === 'auto-draft' ) {
			return;
		}

		$action = $this->get_status_change_action( $old_status, $new_status );
		$post_type_obj = get_post_type_object( $post->post_type );
		$post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : $post->post_type;

		if ( flatwp()->logger ) {
			flatwp()->logger->log_user_action(
				sprintf( '%s "%s" %s (%s → %s)', $post_type_label, $post->post_title, $action, $old_status, $new_status ),
				array(
					'action'       => 'status_change',
					'post_id'      => $post->ID,
					'post_type'    => $post->post_type,
					'post_title'   => $post->post_title,
					'old_status'   => $old_status,
					'new_status'   => $new_status,
					'user_id'      => get_current_user_id(),
				)
			);
		}
	}

	/**
	 * Log new post insertions
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an update.
	 */
	public function log_post_insert( $post_id, $post, $update ) {
		// Only log new posts, not updates
		if ( $update ) {
			return;
		}

		// Skip auto-drafts and revisions
		if ( wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) || $post->post_status === 'auto-draft' ) {
			return;
		}

		$post_type_obj = get_post_type_object( $post->post_type );
		$post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : $post->post_type;

		if ( flatwp()->logger ) {
			flatwp()->logger->log_user_action(
				sprintf( 'New %s created: "%s"', $post_type_label, $post->post_title ),
				array(
					'action'     => 'created',
					'post_id'    => $post->ID,
					'post_type'  => $post->post_type,
					'post_title' => $post->post_title,
					'status'     => $post->post_status,
					'user_id'    => get_current_user_id(),
				)
			);
		}
	}

	/**
	 * Log post updates
	 *
	 * @param int     $post_id     Post ID.
	 * @param WP_Post $post_after  Post object after update.
	 * @param WP_Post $post_before Post object before update.
	 */
	public function log_post_update( $post_id, $post_after, $post_before ) {
		// Skip revisions and autosaves
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Detect what changed
		$changes = $this->detect_post_changes( $post_before, $post_after );

		if ( empty( $changes ) ) {
			return;
		}

		$post_type_obj = get_post_type_object( $post_after->post_type );
		$post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : $post_after->post_type;

		if ( flatwp()->logger ) {
			flatwp()->logger->log_user_action(
				sprintf( '%s "%s" updated: %s', $post_type_label, $post_after->post_title, implode( ', ', $changes ) ),
				array(
					'action'     => 'updated',
					'post_id'    => $post_id,
					'post_type'  => $post_after->post_type,
					'post_title' => $post_after->post_title,
					'changes'    => $changes,
					'user_id'    => get_current_user_id(),
				)
			);
		}
	}

	/**
	 * Log post deletion
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function log_post_delete( $post_id, $post ) {
		// Skip revisions
		if ( wp_is_post_revision( $post ) ) {
			return;
		}

		$post_type_obj = get_post_type_object( $post->post_type );
		$post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : $post->post_type;

		if ( flatwp()->logger ) {
			flatwp()->logger->log_user_action(
				sprintf( '%s "%s" deleted', $post_type_label, $post->post_title ),
				array(
					'action'     => 'deleted',
					'post_id'    => $post_id,
					'post_type'  => $post->post_type,
					'post_title' => $post->post_title,
					'user_id'    => get_current_user_id(),
				)
			);
		}
	}

	/**
	 * Log term creation
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public function log_term_created( $term_id, $tt_id, $taxonomy ) {
		$term = get_term( $term_id, $taxonomy );

		if ( ! $term || is_wp_error( $term ) ) {
			return;
		}

		$taxonomy_obj = get_taxonomy( $taxonomy );
		$taxonomy_label = $taxonomy_obj ? $taxonomy_obj->labels->singular_name : $taxonomy;

		if ( flatwp()->logger ) {
			flatwp()->logger->log_user_action(
				sprintf( '%s created: "%s"', $taxonomy_label, $term->name ),
				array(
					'action'   => 'term_created',
					'term_id'  => $term_id,
					'taxonomy' => $taxonomy,
					'name'     => $term->name,
					'slug'     => $term->slug,
					'user_id'  => get_current_user_id(),
				)
			);
		}
	}

	/**
	 * Log term edits
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public function log_term_edited( $term_id, $tt_id, $taxonomy ) {
		$term = get_term( $term_id, $taxonomy );

		if ( ! $term || is_wp_error( $term ) ) {
			return;
		}

		$taxonomy_obj = get_taxonomy( $taxonomy );
		$taxonomy_label = $taxonomy_obj ? $taxonomy_obj->labels->singular_name : $taxonomy;

		if ( flatwp()->logger ) {
			flatwp()->logger->log_user_action(
				sprintf( '%s updated: "%s"', $taxonomy_label, $term->name ),
				array(
					'action'   => 'term_updated',
					'term_id'  => $term_id,
					'taxonomy' => $taxonomy,
					'name'     => $term->name,
					'slug'     => $term->slug,
					'user_id'  => get_current_user_id(),
				)
			);
		}
	}

	/**
	 * Log term deletion
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @param object $deleted_term Deleted term object.
	 */
	public function log_term_deleted( $term_id, $tt_id, $taxonomy, $deleted_term ) {
		$taxonomy_obj = get_taxonomy( $taxonomy );
		$taxonomy_label = $taxonomy_obj ? $taxonomy_obj->labels->singular_name : $taxonomy;

		if ( flatwp()->logger ) {
			flatwp()->logger->log_user_action(
				sprintf( '%s deleted: "%s"', $taxonomy_label, $deleted_term->name ),
				array(
					'action'   => 'term_deleted',
					'term_id'  => $term_id,
					'taxonomy' => $taxonomy,
					'name'     => $deleted_term->name,
					'slug'     => $deleted_term->slug,
					'user_id'  => get_current_user_id(),
				)
			);
		}
	}

	/**
	 * Log attachment addition
	 *
	 * @param int $post_id Attachment ID.
	 */
	public function log_attachment_added( $post_id ) {
		$attachment = get_post( $post_id );

		if ( ! $attachment ) {
			return;
		}

		$file_type = wp_check_filetype( get_attached_file( $post_id ) );

		if ( flatwp()->logger ) {
			flatwp()->logger->log_user_action(
				sprintf( 'Media uploaded: "%s"', $attachment->post_title ),
				array(
					'action'    => 'media_uploaded',
					'post_id'   => $post_id,
					'filename'  => basename( get_attached_file( $post_id ) ),
					'mime_type' => $file_type['type'],
					'user_id'   => get_current_user_id(),
				)
			);
		}
	}

	/**
	 * Log attachment deletion
	 *
	 * @param int     $post_id Attachment ID.
	 * @param WP_Post $post    Post object.
	 */
	public function log_attachment_deleted( $post_id, $post ) {
		if ( flatwp()->logger ) {
			flatwp()->logger->log_user_action(
				sprintf( 'Media deleted: "%s"', $post->post_title ),
				array(
					'action'   => 'media_deleted',
					'post_id'  => $post_id,
					'filename' => basename( get_attached_file( $post_id ) ),
					'user_id'  => get_current_user_id(),
				)
			);
		}
	}

	/**
	 * Detect bulk actions on edit.php page
	 */
	public function detect_bulk_actions() {
		// Check if this is a bulk action
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified by WordPress core in admin list tables
		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : false;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified by WordPress core in admin list tables
		$action2 = isset( $_REQUEST['action2'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) ) : false;
		$bulk_action = $action !== -1 ? $action : $action2;

		if ( ! $bulk_action || $bulk_action === -1 ) {
			return;
		}

		// Get selected posts
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified by WordPress core in admin list tables
		$post_ids = isset( $_REQUEST['post'] ) ? array_map( 'intval', (array) wp_unslash( $_REQUEST['post'] ) ) : array();

		if ( empty( $post_ids ) ) {
			return;
		}

		$post_count = count( $post_ids );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified by WordPress core in admin list tables
		$post_type = isset( $_REQUEST['post_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_type'] ) ) : 'post';
		$post_type_obj = get_post_type_object( $post_type );
		$post_type_label = $post_type_obj ? $post_type_obj->labels->name : $post_type;

		// Log the bulk action
		if ( flatwp()->logger ) {
			flatwp()->logger->log_user_action(
				sprintf( 'Bulk action "%s" performed on %d %s', $bulk_action, $post_count, strtolower( $post_type_label ) ),
				array(
					'action'     => 'bulk_' . $bulk_action,
					'post_ids'   => $post_ids,
					'post_type'  => $post_type,
					'post_count' => $post_count,
					'user_id'    => get_current_user_id(),
				)
			);
		}
	}

	/**
	 * Log comment addition
	 *
	 * @param int        $comment_id Comment ID.
	 * @param int|string $approved   Comment approval status.
	 * @param array      $commentdata Comment data.
	 */
	public function log_comment_added( $comment_id, $approved, $commentdata ) {
		$comment = get_comment( $comment_id );
		$post = get_post( $comment->comment_post_ID );

		if ( flatwp()->logger ) {
			flatwp()->logger->log_user_action(
				sprintf( 'Comment added on "%s"', $post->post_title ),
				array(
					'action'      => 'comment_added',
					'comment_id'  => $comment_id,
					'post_id'     => $comment->comment_post_ID,
					'post_title'  => $post->post_title,
					'author'      => $comment->comment_author,
					'status'      => $approved,
					'user_id'     => get_current_user_id(),
				)
			);
		}
	}

	/**
	 * Log comment deletion
	 *
	 * @param int        $comment_id Comment ID.
	 * @param WP_Comment $comment    Comment object.
	 */
	public function log_comment_deleted( $comment_id, $comment ) {
		$post = get_post( $comment->comment_post_ID );

		if ( flatwp()->logger ) {
			flatwp()->logger->log_user_action(
				sprintf( 'Comment deleted from "%s"', $post->post_title ),
				array(
					'action'     => 'comment_deleted',
					'comment_id' => $comment_id,
					'post_id'    => $comment->comment_post_ID,
					'post_title' => $post->post_title,
					'user_id'    => get_current_user_id(),
				)
			);
		}
	}

	/**
	 * Log comment status changes
	 *
	 * @param string     $new_status New comment status.
	 * @param string     $old_status Old comment status.
	 * @param WP_Comment $comment     Comment object.
	 */
	public function log_comment_status_change( $new_status, $old_status, $comment ) {
		// Skip if no actual change
		if ( $new_status === $old_status ) {
			return;
		}

		$post = get_post( $comment->comment_post_ID );

		if ( flatwp()->logger ) {
			flatwp()->logger->log_user_action(
				sprintf( 'Comment status changed on "%s": %s → %s', $post->post_title, $old_status, $new_status ),
				array(
					'action'     => 'comment_status_changed',
					'comment_id' => $comment->comment_ID,
					'post_id'    => $comment->comment_post_ID,
					'post_title' => $post->post_title,
					'old_status' => $old_status,
					'new_status' => $new_status,
					'user_id'    => get_current_user_id(),
				)
			);
		}
	}

	/**
	 * Get human-readable action from status change
	 *
	 * @param string $old_status Old status.
	 * @param string $new_status New status.
	 * @return string Action description.
	 */
	private function get_status_change_action( $old_status, $new_status ) {
		$actions = array(
			'publish'  => 'published',
			'draft'    => 'saved as draft',
			'pending'  => 'set to pending review',
			'private'  => 'made private',
			'trash'    => 'moved to trash',
			'future'   => 'scheduled',
		);

		return isset( $actions[ $new_status ] ) ? $actions[ $new_status ] : "changed to {$new_status}";
	}

	/**
	 * Detect changes between post versions
	 *
	 * @param WP_Post $post_before Post before update.
	 * @param WP_Post $post_after  Post after update.
	 * @return array List of changes.
	 */
	private function detect_post_changes( $post_before, $post_after ) {
		$changes = array();

		// Check title
		if ( $post_before->post_title !== $post_after->post_title ) {
			$changes[] = 'title';
		}

		// Check content
		if ( $post_before->post_content !== $post_after->post_content ) {
			$changes[] = 'content';
		}

		// Check excerpt
		if ( $post_before->post_excerpt !== $post_after->post_excerpt ) {
			$changes[] = 'excerpt';
		}

		// Check featured image
		$old_thumbnail = get_post_thumbnail_id( $post_before->ID );
		$new_thumbnail = get_post_thumbnail_id( $post_after->ID );
		if ( $old_thumbnail !== $new_thumbnail ) {
			$changes[] = 'featured image';
		}

		return $changes;
	}
}
