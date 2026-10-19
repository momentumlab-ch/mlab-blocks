<?php
/**
 * Ausgabe des Blocks Knöpfe.
 *
 * @package MLab_Blocks
 *
 * @var array    $attributes Attribute.
 * @var string   $content    Inhalt der inneren Blöcke.
 * @var WP_Block $block      Block.
 */

defined( 'ABSPATH' ) || exit;

echo mlab_blocks_render_knoepfe( $attributes, $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- in der Funktion escaped.
