WLK - Most Played
=================

WordPress plugin that shows media stats from your plug.dj room, tracked by
[SekshiBot](https://github.com/welovekpop/SekshiBot).

## Installation

You need the `php5-mongo` PHP extension installed and enabled.

Then, clone this repo to your `wp-content/plugins` directory, or extract this
[zip file](https://github.com/welovekpop/wlk-most-played/archive/master.zip) in
`wp-content/plugins/wlk-most-played`.

After enabling it, configure your MongoDB URI and database name in the
Settings » Most Played Songs screen.

## Usage

This plugin adds several new shortcodes.

 * `[sekshi-most-played]` - Displays a sortable and paginated
   [DataTable](https://datatables.net) listing songs and their playcounts.
 * `[sekshi-history]` - Displays the most recently played songs in a format
   eerily similar to plug.dj's own.

## License

[MIT](./LICENSE)
