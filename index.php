<?php
require_once 'includes/dbcache.php';

//TODO handle locking user filter to only subscribers once age or gender are selected

//get filters
$gender = isset($_POST['gender']) ? $_POST['gender'] : '';
$age = isset($_POST['age']) ? $_POST['age'] : '';
$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : '';

$filters = '';
//setup where statement
if ($gender != '' || $age != '' || $user_type != ''){
    $filters = 'WHERE ';
    if ($gender != ''){
        if ($gender == 'm'){
            $filters .= "a.gender='Male' AND ";
        }
        elseif ($gender == 'f'){
            $filters .= "a.gender='Female' AND ";
        }
    }
    if ($age != ''){
        if ($age == 18){
            $filters .= "a.birth_year>=YEAR(DATE_SUB(NOW(),INTERVAL 18 YEAR)) AND ";
        }
        elseif ($age == 19){
            $filters .= "a.birth_year<=YEAR(DATE_SUB(NOW(),INTERVAL 19 YEAR)) AND a.birth_year>=YEAR(DATE_SUB(NOW(),INTERVAL 21 YEAR)) AND ";
        }
        elseif ($age == 22){
            $filters .= "a.birth_year<=YEAR(DATE_SUB(NOW(),INTERVAL 22 YEAR)) AND a.birth_year>=YEAR(DATE_SUB(NOW(),INTERVAL 30 YEAR)) AND ";
        }
        elseif ($age == 31){
            $filters .= "a.birth_year<=YEAR(DATE_SUB(NOW(),INTERVAL 31 YEAR)) AND a.birth_year>=YEAR(DATE_SUB(NOW(),INTERVAL 40 YEAR)) AND ";
        }
        elseif ($age == 41){
            $filters .= "a.birth_year<=YEAR(DATE_SUB(NOW(),INTERVAL 41 YEAR)) AND a.birth_year>=YEAR(DATE_SUB(NOW(),INTERVAL 50 YEAR)) AND ";
        }
        elseif ($age == 51){
            $filters .= "a.birth_year<=YEAR(DATE_SUB(NOW(),INTERVAL 51 YEAR)) AND ";
        }
    }
    if ($user_type != ''){
        if ($user_type == 'customer'){
            $filters .= "a.user_type='Customer' AND ";
        }
        elseif ($user_type == 'subscriber'){
            $filters .= "a.user_type='Subscriber' AND ";
        }
    }

    $filters = substr($filters, 0, strlen($filters)-5);
}

//get popChart data
//check DB cache for results
$popResults = dbcache::query("SELECT a.start_station_id as station_id, a.start_station_name AS station_name, count(a.id) AS starts, b.ends, count(a.id) - b.ends AS diff
        FROM trips AS a
        LEFT JOIN
        (SELECT a.end_station_id, a.end_station_name, count(a.id) AS ends FROM trips AS a $filters GROUP BY a.end_station_id) AS b
        ON a.start_station_id = b.end_station_id
        $filters
        GROUP by a.start_station_id
        HAVING diff IS NOT NULL
        ORDER BY diff DESC");

$popLabels = '';
$popNums = '';

//TODO handle less than 14 results
if (count($popResults) >= 14){
    for ($i=0;$i<=7;$i++){
        $popLabels .= '"' . $popResults[$i]['station_name'] . '",';
        $popNums .= $popResults[$i]['diff'] . ',';
    }
    for ($i=count($popResults)-7;$i<=count($popResults)-1;$i++){
        $popLabels .= '"' . $popResults[$i]['station_name'] . '",';
        $popNums .= $popResults[$i]['diff'] . ',';
    }

    $popLabels = substr($popLabels, 0, strlen($popLabels)-1);
    $popNums = substr($popNums, 0, strlen($popNums)-1);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Enjoy Chicago - See the city with Divvy</title>
    <link href="css/main.css" rel="stylesheet" type="text/css"  />
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=visualization"></script>
    <script src="js/Chart.min.js"></script>
<script>
// heat map shit
var map, pointarray, heatmap;

var mapData = [
    <?php
        $results = dbcache::query("SELECT b.id, b.name, b.latitude, b.longitude, ceil(count(a.id)/500) as num_trips
            FROM trips AS a
            LEFT JOIN stations AS b
            ON a.start_station_id = b.id
            $filters
            GROUP BY b.id");
        $mapData = '';
        foreach ($results as $row) {
            $num_trips = $row['num_trips'];
            for ($i=0; $i<$num_trips; $i++){
                $mapData .= "new google.maps.LatLng(" . $row['latitude'] . ", " . $row['longitude'] . "),";
            }
        }
        //trim last coma
        $mapData = substr($mapData,0, strlen($mapData)-1);
        echo $mapData;
    ?>
];

function initialize() {
    var mapOptions = {
        zoom: 11,
        center: new google.maps.LatLng(41.889710, -87.629788),
        mapTypeId: google.maps.MapTypeId.SATELLITE
    };

    map = new google.maps.Map(document.getElementById('map-canvas'),
        mapOptions);

    var pointArray = new google.maps.MVCArray(mapData);

    heatmap = new google.maps.visualization.HeatmapLayer({
        data: pointArray
    });

    heatmap.setMap(map);
}

google.maps.event.addDomListener(window, 'load', initialize);

</script>
</head>

<body>
    <header>
        <div class="wrapper">
            <div class="logo">See the City with Divvy</div>
            <form action="index.php" method="post">

                <select id="gender" name="gender">
                    <option value="">all genders</option>
                    <option <?php if ($gender == 'm') echo 'SELECTED ';?>value="m">male</option>
                    <option <?php if ($gender == 'f') echo 'SELECTED ';?>value="f">female</option>
                </select>

                <select id="age" name="age">
                    <option value="">all ages</option>
                    <option <?php if ($age == 18) echo 'SELECTED ';?>value="18">18 and under</option>
                    <option <?php if ($age == 19) echo 'SELECTED ';?>value="19">19-21</option>
                    <option <?php if ($age == 22) echo 'SELECTED ';?>value="22">22-30</option>
                    <option <?php if ($age == 31) echo 'SELECTED ';?>value="31">31-40</option>
                    <option <?php if ($age == 41) echo 'SELECTED ';?>value="41">41-50</option>
                    <option <?php if ($age == 51) echo 'SELECTED ';?>value="51">51+</option>
                </select>

                <select id="user_type" name="user_type">
                    <option value="">all users</option>
                    <option <?php if ($user_type == 'customer') echo 'SELECTED ';?>value="customer">customer</option>
                    <option <?php if ($user_type == 'subscriber') echo 'SELECTED ';?>value="subscriber">subscriber</option>
                </select>

                <input type="submit" value="filter" />

            </form>
        </div>
        <div class="clear"></div>
    </header>

    <div class="graph-wrapper top">
        <div class="corner1"></div>
        <div class="corner2"></div>
        <div class="corner3"></div>
        <div class="corner4"></div>
        <h2>Choropleth map</h2>
        <h3>The proper name for the so-called "heat-map"</h3>
        <div id="map-canvas"></div>
    </div>

    <div class="graph-wrapper">
        <div class="corner1"></div>
        <div class="corner2"></div>
        <div class="corner3"></div>
        <div class="corner4"></div>
        <h2>Station Popularity</h2>
        <h3># of rides that start at each station vs end at each station</h3>
        <canvas id="popCanvas" height="500px" width="900px"></canvas>
    </div>

    <script>
        // Station Popularity
        var barChartData = {
            labels : [<?=$popLabels?>],
            datasets : [
                {
                    fillColor : "rgba(151,187,205,0.5)",
                    strokeColor : "rgba(151,187,205,1)",
                    data : [<?=$popNums?>]
                }
            ]

        }

        var popChart = new Chart(document.getElementById("popCanvas").getContext("2d")).Bar(barChartData, {animation : false});
    </script>
</body>
</html>