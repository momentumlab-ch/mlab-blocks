<?php
/**
 * Registriert die Blöcke aus blocks/<name>/block.json und das gemeinsame Editor-Skript.
 *
 * @package MLab_Blocks
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'mlab_blocks_register', 9 );
add_filter( 'block_categories_all', 'mlab_blocks_category' );
add_action( 'enqueue_block_editor_assets', 'mlab_blocks_editor_config' );

/**
 * Ordner aller Blöcke.
 *
 * @return list<string>
 */
function mlab_blocks_dirs(): array {
	$dirs = glob( MLAB_BLOCKS_DIR . 'blocks/*', GLOB_ONLYDIR );

	return is_array( $dirs ) ? $dirs : array();
}

/**
 * Editor-Skript und -Stil zuerst, dann die Blöcke.
 */
function mlab_blocks_register(): void {
	wp_register_script(
		'mlab-blocks-editor',
		MLAB_BLOCKS_URL . 'assets/js/editor.js',
		array( 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n', 'wp-server-side-render', 'wp-data' ),
		MLAB_BLOCKS_VERSION,
		true
	);
	wp_register_style( 'mlab-blocks-editor', MLAB_BLOCKS_URL . 'assets/css/editor.css', array(), MLAB_BLOCKS_VERSION );
	wp_set_script_translations( 'mlab-blocks-editor', 'mlab-blocks', MLAB_BLOCKS_DIR . 'languages' );

	foreach ( mlab_blocks_dirs() as $dir ) {
		register_block_type( $dir );
	}
}

/**
 * Felder der Blöcke, Angebote und Bilder aus dem Design für das Editor-Skript.
 */
function mlab_blocks_editor_config(): void {
	$config = array();
	foreach ( mlab_blocks_dirs() as $dir ) {
		$meta = wp_json_file_decode( $dir . '/block.json', array( 'associative' => true ) );
		if ( is_array( $meta ) && isset( $meta['name'] ) ) {
			$config[] = array(
				'name'  => $meta['name'],
				'title' => (string) ( $meta['title'] ?? '' ),
			) + (array) ( $meta['mlab'] ?? array() );
		}
	}

	wp_add_inline_script(
		'mlab-blocks-editor',
		'window.mlabBlocks = ' . wp_json_encode(
			array(
				'blocks'  => $config,
				'offers'  => mlab_blocks_offers(),
				'presets' => mlab_blocks_presets(),
			)
		) . ';',
		'before'
	);
}

/**
 * Eigene Kategorie «MomentumLab» zuoberst im Block-Einfüger.
 *
 * @param array<int, array<string, mixed>> $categories Kategorien.
 * @return array<int, array<string, mixed>>
 */
function mlab_blocks_category( array $categories ): array {
	array_unshift(
		$categories,
		array(
			'slug'  => 'mlab-blocks',
			'title' => __( 'MomentumLab', 'mlab-blocks' ),
			'icon'  => null,
		)
	);

	return $categories;
}
