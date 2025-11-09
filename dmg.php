<?php
/**
 * Plugin Name:       DMG Read More
 * Plugin URI:        https://www.dmgmedia.co.uk/
 * Description:       Read More block for DMG tech test
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      8.1
 * Author:            Malick Elgmati
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dmg
 *
 * @package ReadMore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load textdomain for translations.
 */
add_action(
	'plugins_loaded',
	function () {
		load_plugin_textdomain( 'dmg', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

/**
 * Register the block from metadata.
 */
function dmg_read_more_block_init() {
	$block_dir = __DIR__ . '/build/dmg';
	register_block_type_from_metadata(
		$block_dir,
		array(
			'render_callback' => 'dmg_read_more_render_callback',
		)
	);
}
add_action( 'init', 'dmg_read_more_block_init' );

/**
 * Server-side render callback for Read More block.
 *
 * @param array $attributes Block attributes.
 * @return string Rendered HTML.
 */
function dmg_read_more_render_callback( $attributes ) {
	if ( empty( $attributes['postId'] ) || ! is_numeric( $attributes['postId'] ) ) {
		return '';
	}

	$post_id = (int) $attributes['postId'];
	$post    = get_post( $post_id );

	if ( ! $post || 'publish' !== $post->post_status ) {
		return '';
	}

	$title     = esc_html( get_the_title( $post ) );
	$permalink = esc_url( get_permalink( $post ) );
	$prefix    = esc_html__( 'Read More: ', 'dmg' );

	return sprintf(
		'<p class="dmg-read-more">%s<a href="%s">%s</a></p>',
		$prefix,
		$permalink,
		$title
	);
}

/**
 * WP-CLI: dmg-read-more search
 *
 * Usage:
 *   wp dmg-read-more search [--date-after=<Y-m-d>|<Y-m-d H:i:s>] [--date-before=<Y-m-d>|<Y-m-d H:i:s>]
 *
 * Examples:
 *   wp dmg-read-more search
 *   wp dmg-read-more search --date-after=2025-01-01 --date-before=2025-02-01
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/commands/class-dmg-read-more-cli.php';
}
