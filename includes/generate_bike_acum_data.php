<?php
/**
 * Created by PhpStorm.
 * User: dominic-scandinaro
 * Date: 3/9/14
 * Time: 10:30 PM
 */

require_once 'dbcache.php';
require_once 'filter.php';

$gender = isset($_GET['gender']) ? $_GET['gender'] : '';
$age = isset($_GET['age']) ? $_GET['age'] : '';
$user_type = isset($_GET['user_type']) ? $_GET['user_type'] : '';

$filters = filter::generateWhere($gender, $age, $user_type);
$filters = $filters['filters'];

//get popChart data
$popResults = dbcache::query("SELECT a.start_station_id as station_id, a.start_station_name AS station_name, count(a.id) AS starts, b.ends, b.ends - count(a.id) AS diff
        FROM trips AS a
        LEFT JOIN
        (SELECT a.end_station_id, a.end_station_name, count(a.id) AS ends FROM trips AS a $filters GROUP BY a.end_station_id) AS b
        ON a.start_station_id = b.end_station_id
        $filters
        GROUP by a.start_station_id
        HAVING diff IS NOT NULL
        ORDER BY diff DESC");

$results = array();

//TODO handle less than 14 results
if (count($popResults) >= 14){
    for ($i=0;$i<=7;$i++){
        $results[] = array('label'=>$popResults[$i]['station_name'], 'num'=> $popResults[$i]['diff']);
    }
    for ($i=count($popResults)-7;$i<=count($popResults)-1;$i++){
        $results[] = array('label'=>$popResults[$i]['station_name'], 'num'=> $popResults[$i]['diff']);
    }
}

echo json_encode($results);