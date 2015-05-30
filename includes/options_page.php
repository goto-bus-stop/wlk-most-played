<?php
/**
 * Functions for building the Plugin Options page.
 */

namespace WeLoveKpop\MostPlayed;

if (!defined('WPINC')) {
    exit;
}

function showOptionsPage()
{
    $mongo_uri = esc_attr(get_option('wlkmp_mongo_uri'));
    $mongo_name = esc_attr(get_option('wlkmp_mongo_name'));
    ?>
    <div class="wrap">
        <h2><?php echo __('SekshiBot Most Played Songs Options') ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields('wlkmp_options') ?>
            <?php do_settings_sections('wlkmp_options') ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">MongoDB URI</th>
                    <td>
                        <input type="text"
                               class="regular-text"
                               name="wlkmp_mongo_uri"
                               value="<?php echo $mongo_uri ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">MongoDB Database Name</th>
                    <td>
                        <input type="text"
                               class="regular-text"
                               name="wlkmp_mongo_name"
                               value="<?php echo $mongo_name ?>" />
                    </td>
                </tr>
            </table>
            <?php submit_button() ?>
        </form>
    </div>
    <?php
}
