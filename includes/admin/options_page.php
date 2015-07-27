<?php

namespace WeLoveKpop\MostPlayed\Admin;

use WeLoveKpop\MostPlayed\Mongo;

class OptionsPage extends AdminPage
{
    /**
     * Page title.
     *
     * @var string
     */
    protected $title = 'SekshiBot Options';

    public function render()
    {
        $mongo_uri = get_option('wlkmp_mongo_uri');
        $mongo_name = get_option('wlkmp_mongo_name');
        ob_start();
?>
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
                               value="<?php echo esc_attr($mongo_uri) ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">MongoDB Database Name</th>
                    <td>
                        <input type="text"
                               class="regular-text"
                               name="wlkmp_mongo_name"
                               value="<?php echo esc_attr($mongo_name) ?>" />
                    </td>
                </tr>
            </table>
            <?php submit_button() ?>
        </form>
<?php
        return parent::renderPage(ob_get_clean());
    }
}
