<?php
/**
 * Functions for actually building the page.
 */

namespace WeLoveKpop\MostPlayed;

if (!defined('WPINC')) {
    exit;
}

/**
 * Creates a MongoDB connection.
 *
 * @return \MongoClient
 */
function getMongo()
{
    static $db;
    if (!$db) {
        $db = new \MongoClient(get_option('wlkmp_mongo_uri'));
    }
    return $db;
}

/**
 * Gets a page worth of play records.
 *
 * @param int   $start  First record to return.
 * @param int   $limit  Amount of records to return.
 * @param array $order  Array of DataTable sorting options. Not intended for
 *                      use by people. :eyes:
 * @param array $search Not used.
 *
 * @return array
 */
function getPage(
    $start = 0,
    $limit = 50,
    $order = 'desc',
    $search = []
) {

    $key = 'sekshibot-mostplayed/' . md5(
        $start . '-' . $limit . serialize($order) . implode('-', $search)
    );
    if (apc_exists($key)) {
        $json = apc_fetch($key);
        $json['fromcache'] = true;
        return $json;
    }

    $mongo = getMongo();

    $sekshiDb = $mongo->sekshi;

    $historyCollection = $sekshiDb->historyentries;
    $mediaCollection = $sekshiDb->media;

    $sortOrder = $order === 'asc'
        ? \MongoCollection::ASCENDING
        : \MongoCollection::DESCENDING;

    $pipeline = [
        [ '$match'   => [
            'time' => [ '$gte' => new \MongoDate(0) ],
            'media' => [ '$ne' => null ]
        ] ],
        [ '$group'   => [ '_id' => '$media', 'count' => [ '$sum' => 1 ] ] ],
        [ '$sort'    => [ 'count' => $sortOrder, '_id' => $sortOrder ] ],
        [ '$skip'    => $start ],
        [ '$limit'   => $limit ],
        [ '$project' => [ '_id' => 1, 'count' => 1 ] ]
    ];
    $res = $historyCollection->aggregate($pipeline);

    $ids = [];
    $playcounts = [];
    foreach ($res['result'] as $entry) {
        $ids[] = $entry['_id'];
        $playcounts[(string) $entry['_id']] = $entry['count'];
    }

    $media = $mediaCollection->find([ '_id' => [ '$in' => $ids ] ]);
    $data = [];

    foreach ($media as $m) {
        $data[] = [
            $m['author'],
            $m['title'],
            $playcounts[(string) $m['_id']]
        ];
    }

    usort(
        $data,
        function ($a, $b) use ($order) {
            // 2 = playcounts
            $value = $a[2] > $b[2] ? 1 : -1;
            return $order === 'desc' ? -$value : $value;
        }
    );

    $recordsTotal = $historyCollection->aggregate(
        [ '$match' => [ 'media' => [ '$ne' => null ] ] ],
        [ '$group' => [ '_id' => '$media' ] ],
        [ '$group' => [ '_id' => 1, 'count' => [ '$sum' => 1 ] ] ]
    )['result'][0]['count'];

    $json = [
        'data' => $data,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsTotal
    ];
    apc_store($key, $json, 5 * 60);

    return $json;
}
