WLK - Most Played
=================

WordPress plugin that lists the most played songs in your plug.dj room, tracked
by [SekshiBot](https://github.com/welovekpop/SekshiBot).

## Installation

You need the `php5-mongo` and `php5-apcu` PHP extensions installed and enabled.

Then, clone this repo to your `wp-content/plugins` directory, or extract this
[zip file](https://github.com/welovekpop/wlk-most-played/archive/master.zip) in
`wp-content/plugins/wlk-most-played`.

After enabling it, configure your MongoDB URI in the Settings » Most Played
Songs screen.

## Usage

This plugin adds a single new shortcode.

 * `[sekshi-most-played]` - Displays a sortable and paginated
   [DataTable](https://datatables.net) listing songs and their playcounts.

## License

[MIT](./LICENSE)
