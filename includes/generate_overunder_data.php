<?php
/**
 * Created by PhpStorm.
 * User: dominic-scandinaro
 * Date: 3/9/14
 * Time: 10:32 PM
 */

require_once 'dbcache.php';
require_once 'filter.php';

$gender = isset($_GET['gender']) ? $_GET['gender'] : '';
$age = isset($_GET['age']) ? $_GET['age'] : '';
$user_type = isset($_GET['user_type']) ? $_GET['user_type'] : '';

$filters = filter::generateWhere($gender, $age, $user_type);
$filtersNoWhere = $filters['filters_no_where'];

//get overage data
$over = dbcache::query("SELECT count(a.id) AS over
    FROM trips AS a
    WHERE a.trip_duration/60>30 $filtersNoWhere");
$over = $over[0]['over'];

$under = dbcache::query("SELECT count(a.id) AS under
    FROM trips AS a
    WHERE a.trip_duration/60<30 $filtersNoWhere");
$under = $under[0]['under'];

echo json_encode(array('over'=>$over, 'under'=>$under));
