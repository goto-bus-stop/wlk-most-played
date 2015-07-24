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
 * Most played list ajax handler.
 *
 * @return void
 */
function ajaxHandler()
{
    $mp = new MostPlayedPage();
    echo $mp->datatables(
        isset($_POST['start']) ? (int) $_POST['start'] : 0,
        isset($_POST['limit']) ? (int) $_POST['limit'] : 50,
        isset($_POST['order']) ? $_POST['order'][0]['dir'] : 'desc',
        isset($_POST['search']) ? $_POST['search'] : []
    );
    wp_die();
}

/**
 * Shortcode for the most played listing. Mostly adds javascript and a wrapper
 * div.
 *
 * @return string
 */
function shortcodeMostPlayed()
{
    $mp = new MostPlayedPage();

    wp_enqueue_script(
        'datatables',
        'https://cdn.datatables.net/1.10.5/js/jquery.dataTables.min.js',
        [ 'jquery' ]
    );
    wp_enqueue_style(
        'datatables-css',
        plugins_url('css/datatables.css', __FILE__)
    );
    wp_enqueue_script(
        'sekshi-most-played',
        plugins_url('js/load.js', __FILE__),
        [ 'jquery', 'datatables' ]
    );
    wp_localize_script(
        'sekshi-most-played',
        '_mp_ajax',
        [ 'ajax_url' => admin_url('admin-ajax.php') ]
    );

    return $mp->render();
}

function shortcodeHistory()
{
    wp_enqueue_style(
        'wlkmp-history-css',
        plugins_url('css/history.css', __FILE__)
    );
    $p = new HistoryPage();
    return $p->render();
}

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
    add_shortcode('sekshi-most-played', 'WeLoveKpop\MostPlayed\shortcodeMostPlayed');
    add_shortcode('sekshi-history', 'WeLoveKpop\MostPlayed\shortcodeHistory');
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

add_action('wp_ajax_most_played', 'WeLoveKpop\MostPlayed\ajaxHandler');
add_action('wp_ajax_nopriv_most_played', 'WeLoveKpop\MostPlayed\ajaxHandler');
add_action('init', 'WeLoveKpop\MostPlayed\init');
if (is_admin()) {
    add_action('admin_menu', 'WeLoveKpop\MostPlayed\menu');
    add_action('admin_init', 'WeLoveKpop\MostPlayed\registerSettings');
}
