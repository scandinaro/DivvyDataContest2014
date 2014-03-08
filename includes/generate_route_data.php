<?php
/**
 * Created by 
 * User: denis.oconnor
 * Date: 2014-03-08
 */

require_once(__DIR__.'/dbcache.php');
header('Content-type: application/json');

$bike_id = set_input_value('bike_id', (empty($_REQUEST['bike_id']) ? '' : $_REQUEST['bike_id']), array(1, 3003), $_REQUEST['strict']);
$end_date = set_input_value('end_time', (empty($_REQUEST['end_date']) ? '' : strtotime($_REQUEST['end_date'])), array(strtotime('2013-06-27'), strtotime('2014-01-01')), $_REQUEST['strict']);
$start_date = set_input_value('start_time', (empty($_REQUEST['start_date']) ? '' : strtotime($_REQUEST['start_date'])), array(strtotime('2013-06-27'), strtotime($end_date)), $_REQUEST['strict']);

$origins = dbcache::query(
    "SELECT t.start_station_id AS start_id, s.latitude AS  `lat` , s.longitude AS  `long`
      FROM trips AS t
      INNER JOIN stations AS s ON s.id = t.start_station_id
      WHERE t.bike_id ='$bike_id'
      AND DATE_FORMAT( start_time, '%Y-%m-%d') >=  '$start_date'
      AND DATE_FORMAT( start_time, '%Y-%m-%d') <=  '$end_date'
      ORDER BY start_time ASC "
);
if(empty($origins)){
    $return = array(
        'bikeId' => $bike_id,
        'startDate' => $start_date,
        'endDate' => $end_date,
        'error' => true
    );
    echo json_encode($return);
    exit;
}
$destinations = dbcache::query(
    "SELECT t.end_station_id AS end_id, s.latitude AS  `lat` , s.longitude AS  `long`
     FROM trips AS t
     INNER JOIN stations AS s ON s.id = t.end_station_id
     WHERE t.bike_id ='$bike_id'
     AND DATE_FORMAT( start_time, '%Y-%m-%d') >=  '$start_date'
     AND DATE_FORMAT( start_time, '%Y-%m-%d') <=  '$end_date'
     ORDER BY start_time ASC "
);

$bike_way_points = '';
for($i = 0; $i <= (count($destinations)-1); $i++){
    $bike_way_points .= '['.$destinations[$i]['lat'].', '.$destinations[$i]['long'].'],';
    if($i+1 > (count($origins)-1))
        break;
    else
        $bike_way_points .= '['.$origins[$i+1]['lat'].', '.$origins[$i+1]['long'].'],';
}
$bike_way_points = substr($bike_way_points, 0, -1);

$return = array(
    'bikeOrigin' => '['.$origins[0]['lat'].', '.$origins[0]['long'].']',
    'bikeDestination' => '['.$destinations[count($destinations)-1]['lat'].', '.$destinations[count($destinations)-1]['long'].']',
    'bikeWaypoints' => '['.$bike_way_points.']',
    'bikeId' => $bike_id,
    'startDate' => $start_date,
    'endDate' => $end_date,
    'error' => false
);

echo json_encode($return);
exit;

function set_input_value($field, $value, $min_max, $random=false){
    $time_fields = array('start_time', 'end_time');
    $value = (empty($value) || $random) ? mt_rand($min_max[0], $min_max[1]) : $value;
    if(in_array($field, $time_fields)){
        $value = date('Y-m-d', $value);
    }

    $existing_data = dbcache::query("SELECT * FROM trips WHERE $field='$value'");
    if(empty($existing_data)){
        return set_input_value($field, (in_array($field, $time_fields) ? strtotime($value) : $value), $min_max, true);
    } else {
        return $value;
    }
}