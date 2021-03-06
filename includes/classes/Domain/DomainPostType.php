<?php

namespace WPDM\Domain;

use WPDM\PostType;

class DomainPostType extends PostType {
	/**
	 * Register the post type
	 */
	public function register() {
		parent::register();

		add_filter( 'manage_posts_columns', array( $this, 'posts_columns_head' ), 11, 2 );
		add_action( 'manage_posts_custom_column', array( $this, 'posts_columns_content' ), 10, 2 );
	}

	/**
	 * Get the post type name
	 *
	 * @return string
	 */
	public function get_name() {
		return WPDM_POST_TYPE_DOMAIN;
	}

	/**
	 * Get the post type singular label
	 *
	 * @return string
	 */
	public function get_singular_label() {
		return 'Domain';
	}

	/**
	 * Get the post type plural label
	 *
	 * @return string
	 */
	public function get_plural_label() {
		return 'Domains';
	}

	/**
	 * Get the post type options.
	 *
	 * @return array
	 */
	public function get_options() {
		$options = parent::get_options();

		$options['menu_icon']     = 'dashicons-admin-site';
		$options['menu_position'] = 5;
		$options['public']        = false;
		$options['show_in_rest']  = false;

		if ( ! isset( $options['taxonomies'] ) ) {
			$options['taxonomies'] = array();
		}

		$options['taxonomies'][] = 'category';
		$options['taxonomies'][] = 'post_tag';
		$options['supports']     = array( 'title', 'thumbnail' );

		return $options;
	}

	/**
	 * Add custom head in post columns
	 *
	 * @param $defaults
	 *
	 * @return array
	 */
	function posts_columns_head( $defaults, $post_type ) {
		if ( WPDM_POST_TYPE_DOMAIN !== $post_type ) {
			return $defaults;
		}

		$inserted['permalink'] = __( 'Permalink', 'ck' );
		$inserted['status']    = __( 'Status', 'ck' );

		$defaults = array_slice( $defaults, 0, 2, true ) +
					$inserted +
					array_slice( $defaults, 2, null, true );

		return $defaults;
	}

	/**
	 * Add custom content to the post columns
	 *
	 * @param $column_name
	 * @param $post_id
	 */
	function posts_columns_content( $column_name, $post_id ) {
		if ( 'permalink' === $column_name ) {
			$post      = get_post( $post_id );
			$permalink = get_post_meta( $post_id, 'wpdm_domain_url', true );
			$uptime    = get_post_meta( $post_id, \WPDM\UPTIME_META_KEY, true );

			if ( 'publish' === $post->post_status ) {
				$permalink = sprintf( '<a href="%1$s" target="_blank">%1$s</a>', esc_url( $permalink ) );
			}

			echo wp_kses_post( $permalink );
			?>
			<div class="item">
				<span class="label"><?php esc_html_e( 'Server Status:', 'wpdm' ); ?></span>
				<span class="value"><?php echo ( 1 === (int) $uptime ) ? 'Good' : 'Down'; ?></span>
			</div>
			<?php
		}

		if ( 'status' === $column_name ) {
			$last_checked = get_post_meta( $post_id, \WPDM\LAST_UPDATE_META_KEY, true );
			$expired_date = get_post_meta( $post_id, \WPDM\EXPIRED_DATE_META_KEY, true );
			$nameservers  = get_post_meta( $post_id, \WPDM\NAMESERVERS_META_KEY, true );
			$google_index = get_post_meta( $post_id, \WPDM\GOOGLE_INDEX_META_KEY, true );

			if ( (int) $google_index > 0 ) {
				$google_index = $google_index . ' results';
			} else {
				$google_index = __( 'No Index', 'wpdm' );
			}
			?>
			<div class="statuswrap">
				<div class="item">
					<span class="label"><?php esc_html_e( 'Last Checked:', 'wpdm' ); ?></span>
					<span class="value"><?php echo esc_html( date( 'F j, Y', (int) $last_checked ) ); ?></span>
				</div>
				<div class="item">
					<span class="label"><?php esc_html_e( 'Expired:', 'wpdm' ); ?></span>
					<span class="value"><?php echo esc_html( date( 'F j, Y', strtotime( $expired_date ) ) ); ?></span>
				</div>
				<div class="item">
					<span class="label"><?php esc_html_e( 'Nameservers:', 'wpdm' ); ?></span>
					<span class="value"><br/>
						<?php
						if ( is_array( $nameservers ) ) {
							foreach ( $nameservers as $nameserver ) {
								echo esc_html( $nameserver ) . '<br/>';
							}
						} else {
							echo esc_html( $nameservers );
						}
						?>
					</span>
				</div>

				<div class="item">
					<span class="label"><?php esc_html_e( 'Google Index:', 'wpdm' ); ?></span>
					<span class="value"><?php echo esc_html( $google_index ); ?></span>
				</div>
			</div>
			<?php
		}
	}
}
