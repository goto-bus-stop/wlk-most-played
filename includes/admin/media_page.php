<?php

namespace WeLoveKpop\MostPlayed\Admin;

use WeLoveKpop\MostPlayed\Mongo;

class MediaPage extends AdminPage
{
    /**
     * Default page size.
     *
     * @var integer
     */
    const PAGE_SIZE = 100;

    /**
     * Page title.
     *
     * @var string
     */
    protected $title = 'SekshiBot Media';

    /**
     * Initialises the media list page.
     */
    public function __construct($params = [])
    {
        $this->params = $params;
        $this->buildFilter();
    }

    /**
     * Convert media duration to "MM:SS".
     *
     * @param integer $d  Duration in seconds.
     * @return string
     */
    protected function seconds($d)
    {
        $m = floor($d / 60);
        $d %= 60;
        return $m . ':' . ($d < 10 ? '0' . $d : $d);
    }

    /**
     * Builds a MongoDB query match for the current page parameters.
     *
     * @return array
     */
    protected function buildFilter()
    {
        $this->filter = [];

        if (isset($this->params['search'])) {
            $rx = new \MongoRegex('/' . preg_quote($this->params['search'], '/') . '/i');
            $this->filter = array_merge($this->filter, [
                '$or' => [
                    [ 'author' => $rx ],
                    [ 'title' => $rx ]
                ]
            ]);
        }
        if (isset($this->params['plays'])) {
            $playsFilter = [];
            if (isset($this->params['plays']['min'])) {
                $playsFilter['$gte'] = (int) $this->params['plays']['min'];
            }
            if (isset($this->params['plays']['max'])) {
                $playsFilter['$lte'] = (int) $this->params['plays']['max'];
            }

            $result = Mongo::collection('historyentries')->aggregate([
                [ '$group' => [ '_id' => '$media', 'count' => [ '$sum' => 1 ] ] ],
                [ '$match' => [ 'count' => $playsFilter ] ]
            ])['result'];

            $ids = [];
            foreach ($result as $entry) {
                $ids[] = $entry['_id'];
            }

            $this->filter = array_merge($this->filter, [
                '_id' => [ '$in' => $ids ]
            ]);
        }

        return $this->filter;
    }

    /**
     * Finds paginated media items from MongoDB, sorted by Artist name and Song
     * title.
     *
     * @param integer $start  Page start index.
     * @param integer $limit  Amount of items to return.
     * @return \MongoCursor
     */
    public function getMedia($start = 0, $limit = self::PAGE_SIZE)
    {
        return Mongo::collection('media')
            ->find($this->filter)
            ->sort([ 'author' => 1, 'title' => 1 ])
            ->skip($start)
            ->limit($limit);
    }

    /**
     * Attaches play counts to media items.
     *
     * @param \Iterator $media
     * @return array
     */
    public function getPlaycounts($_media)
    {
        $media = [];
        $ids = [];
        foreach ($_media as $m) {
            $media[] = $m;
            $ids[] = $m['_id'];
        }

        $result = Mongo::collection('historyentries')->aggregate([
            [ '$match' => [ 'media' => [ '$in' => $ids ] ] ],
            [ '$group' => [ '_id' => '$media', 'count' => [ '$sum' => 1 ] ] ]
        ])['result'];

        $counts = [];
        foreach ($result as $i) {
            $counts[(string) $i['_id']] = $i['count'];
        }

        foreach ($media as &$m) {
            $m['plays'] = isset($counts[(string) $m['_id']]) ? $counts[(string) $m['_id']] : 0;
        }

        return $media;
    }

    /**
     * Renders a DataTable with a page worth of media records.
     *
     * @param integer $start
     * @param integer $limit
     * @return string
     */
    public function render($start = 0, $limit = self::PAGE_SIZE)
    {
        $medias = $this->getMedia($start, $limit);
        $medias = $this->getPlaycounts($medias);
        $count = Mongo::collection('media')->count($this->filter);

        $page = floor($start / $limit) + 1;
        $pages = floor($count / $limit);
        if ($page > 1) {
            $prev = '<a href="' . add_query_arg([ 'start' => $start - $limit ]) . '">Prev</a>';
        }
        else {
            $prev = 'Prev';
        }
        if ($page <= $pages) {
            $next = '<a href="' . add_query_arg([ 'start' => $start + $limit ]) . '">Next</a>';
        }
        else {
            $next = 'Next';
        }
        $pagination = '
            <div style="width: 25%; float: left; text-align: left">
                ' . $prev . '
            </div>
            <div style="width: 50%; float: left; text-align: center">
                ' . $page . '/' . $pages . '
            </div>
            <div style="width: 25%; float: left; text-align: right">
                ' . $next . '
            </div>
        ';

        $header = '
            <th>CID</th>
            <th>Artist</th>
            <th>Title</th>
            <th>Duration</th>
            <th>Plays</th>
            <th>Source</th>
        ';

        $html = '
            ' . $pagination . '
            <table id="sekshibot-media" style="width: 100%">
                <thead> <tr>' . $header . '</tr> </thead>
                <tbody>
        ';
        foreach ($medias as $media) {
            $html .= '
                <tr data-cid="' . esc_attr($media['cid']) . '">
                    <td data-name="cid">     ' . esc_html($media['cid']) . '</td>
                    <td data-name="author">  ' . esc_html($media['author']) . '</td>
                    <td data-name="title">   ' . esc_html($media['title']) . '</td>
                    <td data-name="duration">' . $this->seconds($media['duration']) . '</td>
                    <td data-name="plays">   ' . esc_html($media['plays']) . '</td>
                    <td data-name="format">  ' . ($media['format'] === 1 ? 'YouTube' : 'SoundCloud') . '</td>
                </tr>
            ';
        }
        $html .= '
                </tbody>
                <tfoot> <tr>' . $header . '</tr> </tfoot>
            </table>
            ' . $pagination . '
        ';
        return parent::renderPage($html);
    }

    /**
     * Enqueue things for Wordpress.
     *
     * @return void
     */
    public function enqueue()
    {
        wp_enqueue_script(
            'wlk-admin-media',
            plugins_url('../../js/admin_media.js', __FILE__),
            [ 'jquery' ]
        );
        wp_localize_script(
            'wlk-admin-media',
            '_mp_admmedia',
            [ 'ajax_url' => admin_url('admin-ajax.php') ]
        );
    }

    /**
     * Render a response for DataTables.
     *
     * @return void
     */
    public static function ajaxHandler()
    {
        $cid = $_POST['cid'];
        $prop = $_POST['prop'];
        $value = stripslashes($_POST['value']);

        try {
            Mongo::collection('media')->update(
                [ 'cid' => $cid ],
                [ '$set' => [ $prop => $value ] ]
            );
            echo json_encode([ 'ok' => $value ]);
        } catch (\Exception $e) {
            echo json_encode([ 'error' => $e->getMessage() ]);
        }

        wp_die();
    }

    /**
     * Render the first page.
     *
     * @return void
     */
    public static function show()
    {
        $start = isset($_GET['start']) ? (int) $_GET['start'] : 0;
        $p = new self();
        $p->enqueue();
        echo $p->render($start);
    }
}
