<?php
/**
 * Created by PhpStorm.
 * User: dominic-scandinaro
 * Date: 2/15/14
 * Time: 8:00 AM
 *
 * file was too large to import through PHPMyAdmin and the dates were not compatible
 *
 * trip_id,starttime,stoptime,bikeid,tripduration,from_station_id,from_station_name,to_station_id,to_station_name,usertype,gender,birthyear
 *
 */
require_once '../includes/meekrodb.2.2.class.php';
$trips = file('Divvy_trips_2013.csv');

foreach ($trips as $trip){
    //pull data out and reformat dates to be MySQL ready
    $row = explode(',', $trip);
    $id = $row[0];
    $startTime = date("Y-m-d H:i:s", strtotime($row[1]));
    $endTime = date("Y-m-d H:i:s", strtotime($row[2]));
    $bikeID = $row[3];
    $tripDuration = $row[4];
    $startStationID = $row[5];
    $startStationName = $row[6];
    $endStationID = $row[7];
    $endStationName = $row[8];
    $userType = $row[9];
    $gender = $row[10];
    $birthYear = $row[11];

    //insert trip into DB
    DB::insert('trips', array(
        'id' => $id,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'bike_id'=>$bikeID,
        'trip_duration'=>$tripDuration,
        'start_station_name'=>$startStationName,
        'end_station_name'=>$endStationName,
        'start_station_id'=>$startStationID,
        'end_station_id'=>$endStationID,
        'user_type'=>$userType,
        'gender'=>$gender,
        'birth_year'=>$birthYear
    ));
}