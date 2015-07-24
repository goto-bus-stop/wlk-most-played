<?php
/**
 * Functions for actually building the page.
 */

namespace WeLoveKpop\MostPlayed;

if (!defined('WPINC')) {
    exit;
}

class HistoryPage
{
    public function __construct()
    {
        $this->collection = getMongoDb()->historyentries;
        $this->media = getMongoDb()->media;
        $this->users = getMongoDb()->users;
    }

    protected function getLast($n)
    {
        $entries = iterator_to_array(
            $this->collection
                ->find([ 'media' => [ '$ne' => null ] ])
                ->sort([ 'time' => -1 ])
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

    public function render($n = 50)
    {
        $data = $this->getLast($n);

        $html = '<div class="history">';
        foreach ($data as $item) {
            $media = $item['media'];
            $dj = $item['dj'];
            $entry = $item['entry'];

            $score = $entry['score'];
            $time = $entry['time']->toDateTime()->format('Y-m-d H:i:s');

            $html .= '
                <div class="history-entry">
                    <img src="' . esc_attr($media['image']) . '" alt="Image">
                    <div class="meta">
                        <span class="media">' . esc_html($media['author']) . ' - ' . esc_html($media['title']) . '</span>
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
        $html .= '</div>';

        return $html;
    }
}
