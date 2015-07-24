<?php
/**
 * Contains the Most Played page class.
 */

namespace WeLoveKpop\MostPlayed;

/**
 * Class for rendering the most played page.
 */
class MostPlayedPage
{
    /**
     * Initialises the Most Played page.
     */
    public function __construct()
    {
        $this->history = getMongoDb()->historyentries;
        $this->media = getMongoDb()->media;
    }

    /**
     * Memoized count($media).
     *
     * @return integer
     */
    protected function countMedia()
    {
        static $m = null;
        if (is_null($m)) {
            $m = $this->media->count();
        }
        return $m;
    }

    /**
     * Finds & sorts the most played media, optionally filtered by a search
     * query.
     *
     * @param integer $start  First record to return.
     * @param integer $limit  Amount of records to return.
     * @param integer $order  Sort order. A \MongoCollection::*SCENDING constant.
     * @param string  $search Search query.
     *
     * @return array Results. ['data'] contains media entries with an additional
     *               ['plays'] property. ['count'] contains the total result
     *               count.
     */
    protected function getMostPlayed(
        $start = 0,
        $limit = 50,
        $order = \MongoCollection::DESCENDING,
        $search = ''
    ) {
        $match = [
            'time' => [ '$gte' => new \MongoDate(0) ],
            'media' => [ '$ne' => null ]
        ];

        $ids = [];
        if (!empty($search)) {
            $rx = new \MongoRegex('/' . preg_quote($search, '/') . '/i');
            $query = $this->media->find(
                [
                    '$or' => [
                        [ 'author' => $rx ],
                        [ 'title' => $rx ]
                    ]
                ],
                [ '_id' => 1 ]
            );
            foreach ($query as $m) {
                $ids[] = $m['_id'];
            }
            $match['media'] = [ '$in' => $ids ];
        }

        $pipeline = [
            [ '$match'   => $match ],
            [ '$group'   => [ '_id' => '$media', 'count' => [ '$sum' => 1 ] ] ],
            [ '$sort'    => [ 'count' => $order, '_id' => $order ] ],
            [ '$skip'    => $start ],
            [ '$limit'   => $limit ],
            [ '$project' => [ '_id' => 1, 'count' => 1 ] ]
        ];
        $res = $this->history->aggregate($pipeline);

        $playcounts = [];
        foreach ($res['result'] as $entry) {
            $playcounts[(string) $entry['_id']] = $entry['count'];
        }

        $medias = iterator_to_array(
            $this->media->find(
                [ '_id' => [ '$in' => array_column($res['result'], '_id') ] ]
            )
        );

        $sorted = array_map(
            function ($media) use ($playcounts) {
                $media['plays'] = $playcounts[$media['_id']];
                return $media;
            },
            $medias
        );
        usort(
            $sorted,
            function ($a, $b) {
                return $a['plays'] > $b['plays'] ? 1 : -1;
            }
        );

        $count = $search ? count($ids) : $this->countMedia();

        $data = $order === \MongoCollection::ASCENDING
            ? $sorted
            : array_reverse($sorted);

        return compact('data', 'count');
    }

    /**
     * Builds most played page JSON objects for DataTables.
     *
     * @param integer $start  See getMostPlayed.
     * @param integer $limit  See getMostPlayed.
     * @param string  $order  Sort order, either "desc" or "asc".
     * @param array   $search DataTables search object. Only the ['value']
     *                        property is supported.
     *
     * @return string JSON.
     */
    public function datatables(
        $start = 0,
        $limit = 50,
        $order = 'desc',
        $search = []
    ) {
        $order = $order === 'asc'
            ? \MongoCollection::ASCENDING
            : \MongoCollection::DESCENDING;
        $search = !empty($search['value']) ? $search['value'] : null;

        $mostPlayed = $this->getMostPlayed($start, $limit, $order, $search);
        $data = array_map(
            function ($m) {
                return array_map(
                    'esc_html',
                    [ $m['author'], $m['title'], $m['plays'] ]
                );
            },
            $mostPlayed['data']
        );

        $json = [
            'data' => $data,
            'recordsTotal' => $this->countMedia(),
            'recordsFiltered' => $mostPlayed['count']
        ];

        return json_encode($json);
    }

    /**
     * Renders initial table HTML. Not much going on! :)
     *
     * @return string Empty table.
     */
    public function render()
    {
        return '
            <table id="most-played">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Artist</th>
                        <th>Title</th>
                        <th>Play Count</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        ';
    }

    /**
     * Shortcode handler for the most played listing. Mostly adds javascript
     * and a wrapper div.
     *
     * @return string
     */
    public static function shortcode()
    {
        $mp = new self();

        wp_enqueue_script(
            'datatables',
            'https://cdn.datatables.net/1.10.5/js/jquery.dataTables.min.js',
            [ 'jquery' ]
        );
        wp_enqueue_style(
            'datatables-css',
            plugins_url('../css/datatables.css', __FILE__)
        );
        wp_enqueue_script(
            'sekshi-most-played',
            plugins_url('../js/load.js', __FILE__),
            [ 'jquery', 'datatables' ]
        );
        wp_localize_script(
            'sekshi-most-played',
            '_mp_ajax',
            [ 'ajax_url' => admin_url('admin-ajax.php') ]
        );

        return $mp->render();
    }

    /**
     * Most played list ajax handler.
     *
     * @return void
     */
    public static function ajaxHandler()
    {
        $mp = new self();
        echo $mp->datatables(
            isset($_POST['start']) ? (int) $_POST['start'] : 0,
            isset($_POST['limit']) ? (int) $_POST['limit'] : 50,
            isset($_POST['order']) ? $_POST['order'][0]['dir'] : 'desc',
            isset($_POST['search']) ? $_POST['search'] : []
        );
        wp_die();
    }
}
