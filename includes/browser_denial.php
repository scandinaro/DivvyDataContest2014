<?php
/**
 * Created by 
 * User: denis.oconnor
 * Date: 2014-03-10
 */

require_once(__DIR__.'/Mobile_Detect.php');
$detect = new Mobile_Detect;
if($detect->isMobile() || $detect->isIE()){
    echo 1;
} else {
    echo 0;
}