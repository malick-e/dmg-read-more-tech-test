<?php
/**
 * WP-CLI command for DMG Read More block.
 *
 * Command: wp dmg-read-more search
 *
 * This command uses a single, prepared SQL query instead of wp_query
 * to avoid building WP_Post objects for better performance.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

if ( ! class_exists( 'DMG_Read_More_CLI_Command', false ) ) {
	class DMG_Read_More_CLI_Command {
		private $block_marker = '<!-- wp:dmg/read-more';

		/**
		 * Search for published posts containing the DMG Read More block within a date range.
		 *
		 * ##OPTIONS:
		 * [--date-after=<date>]
		 * : Inclusive lower bound. Accepts 'Y-m-d' or 'Y-m-d H:i:s'. Defaults to 30 days ago at 00:00:00.
		 *
		 * [--date-before=<date>]
		 * : Inclusive upper bound. Accepts 'Y-m-d' or 'Y-m-d H:i:s'. Defaults to now.
		 *
		 * ##EXAMPLES:
		 *     wp dmg-read-more search
		 *     wp dmg-read-more search --date-after=2025-01-01 --date-before=2025-02-01
		 */
		public function search( $args, $assoc_args ) {
			try {
				list( $after, $before ) = $this->parse_dates( $assoc_args );
				$this->search_sql( $after, $before );
			} catch ( Exception $e ) {
				\WP_CLI::warning( 'Error executing search: ' . $e->getMessage() );
			}
		}

		/**
		 * SQL query to output matching IDs.
		 */
		private function search_sql( $after, $before ) {
			global $wpdb;
			$sql = $wpdb->prepare(
				"SELECT ID
				 FROM {$wpdb->posts}
				 WHERE
				     post_type='post' AND
				     post_status='publish' AND
				     post_date >= %s AND
				     post_date <= %s AND
				     INSTR(post_content, %s) > 0
				 ORDER BY post_date DESC, ID DESC",
				$after,
				$before,
				$this->block_marker
			);
			$ids = $wpdb->get_col( $sql );
			if ( empty( $ids ) ) {
				\WP_CLI::log( sprintf( 'No posts found between %s and %s containing the dmg/read-more block.', $after, $before ) );
				return;
			}
			foreach ( $ids as $id ) {
				\WP_CLI::line( (string) $id );
			}
		}

		/**
		 * Parse and validate date range.
		 */
		private function parse_dates( $assoc_args ) {
			$default_after_ts  = strtotime( '-30 days midnight', current_time( 'timestamp' ) );
			$default_before_ts = current_time( 'timestamp' );
			$after_input  = isset( $assoc_args['date-after'] ) ? $assoc_args['date-after'] : null;
			$before_input = isset( $assoc_args['date-before'] ) ? $assoc_args['date-before'] : null;
			$after  = $this->normalize_date( $after_input,  date( 'Y-m-d 00:00:00', $default_after_ts ) );
			$before = $this->normalize_date( $before_input, date( 'Y-m-d H:i:s', $default_before_ts ) );
			if ( strtotime( $after ) === false || strtotime( $before ) === false ) {
				throw new Exception( 'Invalid date format. Use Y-m-d or Y-m-d H:i:s.' );
			}
			if ( strtotime( $after ) > strtotime( $before ) ) {
				throw new Exception( 'date-after must be earlier than or equal to date-before.' );
			}
			return array( $after, $before );
		}

		/**
		 * Normalize date input (append H:i:s as midnight if it isn't included).
		 */
		private function normalize_date( $input, $fallback ) {
			if ( null === $input || '' === trim( $input ) ) {
				return $fallback;
			}
			$input = trim( $input );
			if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $input ) ) {
				return $input . ' 00:00:00';
			}
			if ( preg_match( '/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}$/', $input ) ) {
				return $input;
			}
			$ts = strtotime( $input );
			return $ts ? date( 'Y-m-d H:i:s', $ts ) : $fallback;
		}
	}

	\WP_CLI::add_command( 'dmg-read-more', 'DMG_Read_More_CLI_Command' );
}
