<?php
/**
 * Functions for actually building the page.
 */

namespace WeLoveKpop\MostPlayed;

class HistoryPage
{
    public function __construct()
    {
        $this->collection = Mongo::collection('historyentries');
        $this->media = Mongo::collection('media');
        $this->users = Mongo::collection('users');
    }

    protected function getFilter()
    {
        return [ 'media' => [ '$ne' => null ] ];
    }

    protected function countFiltered()
    {
        static $c = null;
        if (is_null($c)) {
            $c = $this->collection->count($this->getFilter());
        }
        return $c;
    }

    protected function getLast($start, $n)
    {
        $entries = iterator_to_array(
            $this->collection
                ->find($this->getFilter())
                ->sort([ 'time' => -1 ])
                ->skip($start)
                ->limit($n)
        );
        $medias = $this->media->find([
            '_id' => [ '$in' => array_column($entries, 'media') ]
        ]);
        $users = $this->users->find([
            '_id' => [ '$in' => array_column($entries, 'dj') ]
        ]);
        return array_map(function ($entry) use ($medias, $users) {
            $media = null;
            foreach ($medias as $m) {
                if ($m['_id'] == $entry['media']) {
                    $media = $m;
                    break;
                }
            }
            $dj = null;
            foreach ($users as $user) {
                if ($user['_id'] == $entry['dj']) {
                    $dj = $user;
                    break;
                }
            }
            return compact('entry', 'media', 'dj');
        }, $entries);
    }

    public function render($start = 0, $n = 50)
    {
        $data = $this->getLast($start, $n);

        $html = '<div class="history">';
        $html .= '<div class="history-list">';
        foreach ($data as $item) {
            $media = $item['media'];
            $dj = $item['dj'];
            $entry = $item['entry'];

            $score = $entry['score'];
            $title = $media['author'] . ' - ' . $media['title'];
            $time = date('Y-m-d H:i:s', $entry['time']->sec);

            $html .= '
                <div class="history-entry">
                    <img src="' . esc_attr($media['image']) . '" alt="Image">
                    <div class="meta">
                        <span class="media">' . esc_html($title) . '</span>
                        <span class="dj">' . esc_html($dj['username']) . '</span>
                        <span class="time">' . esc_html($time) . '</span>
                    </div>
                    <div class="score">
                        <div class="woots">
                            <i class="icon-woot"></i><span>' . esc_html($score['positive']) . '</span>
                        </div>
                        <div class="grabs">
                            <i class="icon-grab"></i><span>' . esc_html($score['grabs']) . '</span>
                        </div>
                        <div class="mehs">
                            <i class="icon-meh"></i><span>' . esc_html($score['negative']) . '</span>
                        </div>
                        <div class="listeners">
                            <i class="icon-listeners"></i><span>' . esc_html($score['listeners']) . '</span>
                        </div>
                    </div>
                </div>
            ';
        }

        $html .= '
                </div>
                <div class="paginate">
                    <button class="previous">Previous</button>
                    <button class="next">Next</button>
                </div>
            </div>
        ';

        return $html;
    }

    /**
     * Enqueue things for wordpress.
     *
     * @return void
     */
    public function enqueue()
    {
        wp_enqueue_style(
            'wlkmp-history-css',
            plugins_url('../css/history.css', __FILE__)
        );
        wp_enqueue_script(
            'sekshi-history',
            plugins_url('../js/history.js', __FILE__),
            [ 'jquery' ]
        );
        wp_localize_script(
            'sekshi-history',
            '_sekshi_history',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'page' => 0,
                'lastPage' => ceil($this->countFiltered() / 50)
            ]
        );
    }

    /**
     * Shortcode handler for the history list.
     *
     * @return string
     */
    public static function shortcode()
    {
        $p = new self();
        $p->enqueue();
        return $p->render();
    }

    /**
     * History list ajax handler.
     *
     * @return void
     */
    public static function ajaxHandler()
    {
        $p = new self();
        echo $p->render(isset($_POST['page']) ? (int) $_POST['page'] * 50 : 0);
        wp_die();
    }
}
