<?php
/**
 * Functions for dealing with mongodb connections.
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
function getMongoConnection()
{
    static $client;
    if (!$client) {
        $client = new \MongoClient(get_option('wlkmp_mongo_uri'));
    }
    return $client;
}

/**
 * Returns a MongoDB database.
 *
 * @return \MongoDB
 */
function getMongoDb($dbName = null)
{
    if (is_null($dbName)) {
        $dbName = get_option('wlkmp_mongo_name');
    }
    return getMongoConnection()->{$dbName};
}
