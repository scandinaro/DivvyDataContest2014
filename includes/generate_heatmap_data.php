<?php
/**
 * Created by PhpStorm.
 * User: dominic-scandinaro
 * Date: 3/9/14
 * Time: 10:21 PM
 */

require_once 'dbcache.php';
require_once 'filter.php';

$gender = isset($_GET['gender']) ? $_GET['gender'] : '';
$age = isset($_GET['age']) ? $_GET['age'] : '';
$user_type = isset($_GET['user_type']) ? $_GET['user_type'] : '';

$filters = filter::generateWhere($gender, $age, $user_type);
$filters = $filters['filters'];

$results = dbcache::query("SELECT b.id, b.name, b.latitude, b.longitude, ceil(count(a.id)/500) as num_trips
        FROM trips AS a
        LEFT JOIN stations AS b
        ON a.start_station_id = b.id
        $filters
        GROUP BY b.id");
$mapData = array();
foreach ($results as $row) {
    $num_trips = $row['num_trips'];
    for ($i=0; $i<$num_trips; $i++){
        $mapData [] = array('latitude'=>$row['latitude'], 'longitude'=>$row['longitude']);
    }
}
echo json_encode($mapData);