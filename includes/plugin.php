<?php

namespace WeLoveKpop\MostPlayed;

/**
 * Hooks!
 */
class Plugin
{
    /**
     * Initialise the plugin.
     *
     * @return void
     */
    public static function init()
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
     * "menu" hook, adds the options page.
     */
    public static function menu()
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
     * Registers settings for the settings page.
     */
    public static function registerSettings()
    {
        register_setting('wlkmp_options', 'wlkmp_mongo_uri');
        register_setting('wlkmp_options', 'wlkmp_mongo_name');
    }
}
