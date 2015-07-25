<?php
/**
 * Functions for dealing with mongodb connections.
 */
namespace WeLoveKpop\MostPlayed;

class Mongo {

    protected static $client;

    /**
     * Creates a MongoDB connection.
     *
     * @return \MongoClient
     */
    public static function getInstance()
    {
        if (is_null(self::$client)) {
            self::$client = new \MongoClient(get_option('wlkmp_mongo_uri'));
        }
        return self::$client;
    }

    /**
     * Returns a MongoDB database.
     *
     * @return \MongoDB
     */
    public static function db($dbName = null)
    {
        if (is_null($dbName)) {
            $dbName = get_option('wlkmp_mongo_name');
        }
        return self::getInstance()->{$dbName};
    }

    /**
     * Returns a MongoDB collection.
     *
     * @return \MongoCollection
     */
    public static function collection($collectionName, $db = null)
    {
        return self::db($db)->{$collectionName};
    }

}
