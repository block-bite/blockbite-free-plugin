<?php

/**
 * Plugin Name:       Blockbite
 * Description:       Tailwind Designer [Beta][Developent]
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Version: 1.3.20
 * Author:            Blockbite
 * Author URI:        https://www.block-bite.com
 * Plugin URI:        https://www.block-bite.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       blockbite
 *
 */

// include autoloader from composer
require_once __DIR__ . '/vendor/autoload.php';


use Dotenv\Dotenv;
use Blockbite\Blockbite\Editor;
use Blockbite\Blockbite\Frontend;
use Blockbite\Blockbite\Plugin;
use Blockbite\Blockbite\Settings;




$envPath = __DIR__ . '/.env';
// skip loading .env file if it doesn't exist
if (file_exists($envPath)) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}


if (!defined('BLOCKBITE_PLUGIN_URL')) {
	define('BLOCKBITE_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('BLOCKBITE_PLUGIN_VERSION')) {
	define('BLOCKBITE_PLUGIN_VERSION', '1.0.52');
}

if (!defined('BLOCKBITE_ITEMS_VERSION')) {
	define('BLOCKBITE_ITEMS_VERSION', '1.0.0');
}


define('BLOCKBITE_MAIN_FILE', __FILE__);
define('BLOCKBITE_ICON_DIR', 'resources/svg/');
define('BLOCKBITE_ICON_URI', 'resources/svg/');
define('BLOCKBITE_PLUGIN_DIR', plugin_dir_path(__FILE__));

if (!defined('BLOCKBITE_PLATFORM_URL')) {
    define('BLOCKBITE_PLATFORM_URL', $_ENV['PLATFORM_URL'] ?? 'https://block-bite.com'); 
}



// Create instances of Plugin and Editor classes
$plugin = new Blockbite\Blockbite\Plugin(
	new Blockbite\Blockbite\Editor(),
	new Blockbite\Blockbite\Frontend(),
	new Blockbite\Blockbite\Settings()
);

add_action('plugins_loaded', function () use ($plugin) {
	$plugin->init();
});
