<?php
/**
 * Plugin Name: SekshiBot Most Played
 * Description: Lists most played songs as recorded in MongoDB by SekshiBot.
 * Version: 0.5.0
 * Author: WE ♥ KPOP
 * Author URI: https://welovekpop.club/
 * License: MIT
 */

namespace WeLoveKpop\MostPlayed;

if (!defined('WPINC')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/mongo.php';
require_once plugin_dir_path(__FILE__) . 'includes/plugin.php';
require_once plugin_dir_path(__FILE__) . 'includes/history_page.php';
require_once plugin_dir_path(__FILE__) . 'includes/mp_page.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/admin_page.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/options_page.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/media_page.php';

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
add_action(
    'wp_ajax_sekshi_history',
    'WeLoveKpop\MostPlayed\HistoryPage::ajaxHandler'
);
add_action(
    'wp_ajax_nopriv_sekshi_history',
    'WeLoveKpop\MostPlayed\HistoryPage::ajaxHandler'
);
add_action(
    'wp_ajax_wlk_admin_media',
    'WeLoveKpop\MostPlayed\Admin\MediaPage::ajaxHandler'
);
add_action('init', 'WeLoveKpop\MostPlayed\Plugin::init');
if (is_admin()) {
    add_action('admin_menu', 'WeLoveKpop\MostPlayed\Plugin::menu');
    add_action('admin_init', 'WeLoveKpop\MostPlayed\Plugin::registerSettings');
}
