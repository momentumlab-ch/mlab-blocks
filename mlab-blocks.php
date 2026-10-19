<?php
/**
 * Plugin Name:       MomentumLab Blocks
 * Plugin URI:        https://github.com/momentumlab-ch/mlab-blocks
 * Description:       Seiten mit Blöcken pflegen: Seitenkopf, Abschnitte, Preisliste, Galerie, Fragen und mehr. Felder für Bild, Titel, Text und Knopf, das Aussehen kommt aus dem Theme.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Author:            MomentumLab
 * Author URI:        https://momentumlab.ch
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/momentumlab-ch/mlab-blocks
 * Text Domain:       mlab-blocks
 * Domain Path:       /languages
 *
 * @package MLab_Blocks
 */

defined( 'ABSPATH' ) || exit;

define( 'MLAB_BLOCKS_VERSION', '1.0.0' );
define( 'MLAB_BLOCKS_FILE', __FILE__ );
define( 'MLAB_BLOCKS_DIR', plugin_dir_path( __FILE__ ) );
define( 'MLAB_BLOCKS_URL', plugin_dir_url( __FILE__ ) );

require_once MLAB_BLOCKS_DIR . 'inc/helpers.php';
require_once MLAB_BLOCKS_DIR . 'inc/render.php';
require_once MLAB_BLOCKS_DIR . 'inc/blocks.php';
require_once MLAB_BLOCKS_DIR . 'inc/updater.php';
