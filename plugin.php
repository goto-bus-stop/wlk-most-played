<?php
/**
 * Plugin Name: SekshiBot Most Played
 * Description: Lists most played songs as recorded in MongoDB by SekshiBot.
 * Version: 0.3.0
 * Author: WE ♥ KPOP
 * Author URI: https://welovekpop.club/
 * License: MIT
 */

namespace WeLoveKpop\MostPlayed;

if (!defined('WPINC')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/mongo.php';
require_once plugin_dir_path(__FILE__) . 'includes/history_page.php';
require_once plugin_dir_path(__FILE__) . 'includes/mp_page.php';
require_once plugin_dir_path(__FILE__) . 'includes/options_page.php';

/**
 * "menu" hook, adds the MostPlayed options page.
 */
function menu()
{
    add_options_page(
        'SekshiBot Most Played Songs Options',
        'Most Played Songs',
        'activate_plugins',
        'wlk-most-played',
        'WeLoveKpop\MostPlayed\showOptionsPage'
    );
}

/**
 * Initialise the plugin.
 *
 * @return void
 */
function init()
{
    add_shortcode(
        'sekshi-most-played',
        'WeLoveKpop\MostPlayed\MostPlayedPage::shortcode'
    );
    add_shortcode(
        'sekshi-history',
        'WeLoveKpop\MostPlayed\HistoryPage::shortcode'
    );
}

/**
 * Registers settings for the settings page.
 */
function registerSettings()
{
    register_setting('wlkmp_options', 'wlkmp_mongo_uri');
    register_setting('wlkmp_options', 'wlkmp_mongo_name');
}

add_option('wlkmp_mongo_uri', 'mongodb://localhost:27017/');
add_option('wlkmp_mongo_name', 'sekshi');

add_action(
    'wp_ajax_most_played',
    'WeLoveKpop\MostPlayed\MostPlayedPage::ajaxHandler'
);
add_action(
    'wp_ajax_nopriv_most_played',
    'WeLoveKpop\MostPlayed\MostPlayedPage::ajaxHandler'
);
add_action('init', 'WeLoveKpop\MostPlayed\init');
if (is_admin()) {
    add_action('admin_menu', 'WeLoveKpop\MostPlayed\menu');
    add_action('admin_init', 'WeLoveKpop\MostPlayed\registerSettings');
}
