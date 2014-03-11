<?php
/**
 * Created by PhpStorm.
 * User: dominic-scandinaro
 * Date: 3/10/14
 * Time: 8:47 PM
 */

require_once 'dbcache.php';
require_once 'filter.php';

$gender = isset($_GET['gender']) ? $_GET['gender'] : '';
$age = isset($_GET['age']) ? $_GET['age'] : '';
$user_type = isset($_GET['user_type']) ? $_GET['user_type'] : '';

$filters = filter::generateWhere($gender, $age, $user_type);
$filtersNoWhere = $filters['filters_no_where'];

$male = dbcache::query("SELECT count(a.id) as result FROM trips AS a WHERE a.gender='male' $filtersNoWhere");
$female = dbcache::query("SELECT count(a.id) as result FROM trips AS a WHERE a.gender='female' $filtersNoWhere");
$male = $male[0]['result'];
$female = $female[0]['result'];

$customer = dbcache::query("SELECT count(id) as result FROM trips WHERE user_type='customer'");
$subscriber = dbcache::query("SELECT count(id) as result FROM trips WHERE user_type='subscriber'");
$customer = $customer[0]['result'];
$subscriber = $subscriber[0]['result'];

echo json_encode(array('male'=>$male, 'female'=>$female, 'subscriber'=>$subscriber, 'customer'=>$customer));