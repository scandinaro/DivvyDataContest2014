<?php
/**
 * Created by PhpStorm.
 * User: dominic-scandinaro
 * Date: 3/10/14
 * Time: 8:47 PM
 */

require_once 'dbcache.php';

$male = dbcache::query("SELECT count(id) as result FROM trips WHERE gender='male'");
$female = dbcache::query("SELECT count(id) as result FROM trips WHERE gender='female'");
$male = $male[0]['result'];
$female = $female[0]['result'];

$customer = dbcache::query("SELECT count(id) as result FROM trips WHERE user_type='customer'");
$subscriber = dbcache::query("SELECT count(id) as result FROM trips WHERE user_type='subscriber'");
$customer = $customer[0]['result'];
$subscriber = $subscriber[0]['result'];

echo json_encode(array('male'=>$male, 'female'=>$female, 'subscriber'=>$subscriber, 'customer'=>$customer));