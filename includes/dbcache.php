<?php
/**
 * Created by PhpStorm.
 * User: dominic-scandinaro
 * Date: 3/6/14
 * Time: 3:22 PM
 */
require_once 'includes/meekrodb.2.2.class.php';

class dbcache {
    public static function query($query){
        $queryHash = md5($query);
        $results = DB::queryFirstField("SELECT result FROM query_cache WHERE id='$queryHash'");
        if (!$results){
            $results = DB::query($query);
            //insert into cache
            DB::insert('query_cache', array(
                'id' => $queryHash,
                'result' => serialize($results)
            ));
        }
        else {
            $results = unserialize($results);
        }

        return $results;
    }
} 