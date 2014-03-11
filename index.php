<?php
require_once 'includes/dbcache.php';

//get filters
$gender = isset($_POST['gender']) ? $_POST['gender'] : '';
$age = isset($_POST['age']) ? $_POST['age'] : '';
$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : '';

$filters = '';
$filtersNoWhere = '';
//setup where statement
if ($gender != '' || $age != '' || $user_type != ''){
    $filters = array();
    if (!empty($gender)){
        if ($gender == 'm'){
            $filters[] = "a.gender='Male'";
        }
        elseif ($gender == 'f'){
            $filters[] = "a.gender='Female'";
        }
    }
    if (!empty($age)){
        if ($age == 18){
            $filters[] = "a.birth_year>=YEAR(DATE_SUB(NOW(),INTERVAL 18 YEAR))";
        }
        elseif ($age == 19){
            $filters[] = "a.birth_year<=YEAR(DATE_SUB(NOW(),INTERVAL 19 YEAR))";
            $filters[] = "a.birth_year>=YEAR(DATE_SUB(NOW(),INTERVAL 21 YEAR))";
        }
        elseif ($age == 22){
            $filters[] = "a.birth_year<=YEAR(DATE_SUB(NOW(),INTERVAL 22 YEAR))";
            $filters[] = "a.birth_year>=YEAR(DATE_SUB(NOW(),INTERVAL 30 YEAR))";
        }
        elseif ($age == 31){
            $filters[] = "a.birth_year<=YEAR(DATE_SUB(NOW(),INTERVAL 31 YEAR))";
            $filters[] = "a.birth_year>=YEAR(DATE_SUB(NOW(),INTERVAL 40 YEAR))";
        }
        elseif ($age == 41){
            $filters[] = "a.birth_year<=YEAR(DATE_SUB(NOW(),INTERVAL 41 YEAR))";
            $filters[] = "a.birth_year>=YEAR(DATE_SUB(NOW(),INTERVAL 50 YEAR))";
        }
        elseif ($age == 51){
            $filters[] = "a.birth_year<=YEAR(DATE_SUB(NOW(),INTERVAL 51 YEAR))";
        }
    }
    if (!empty($user_type)){
        if ($user_type == 'customer'){
            $filters[] = "a.user_type='Customer'";
        }
        elseif ($user_type == 'subscriber'){
            $filters[] = "a.user_type='Subscriber'";
        }
    }

    $filters = "WHERE ".implode(" AND ", $filters);
    $filtersNoWhere = str_replace("WHERE", "AND", $filters);
}

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

//get overage data
$over = dbcache::query("SELECT count(a.id) AS over
    FROM trips AS a
    WHERE a.trip_duration/60>30 $filtersNoWhere");
$over = $over[0]['over'];

$under = dbcache::query("SELECT count(a.id) AS under
    FROM trips AS a
    WHERE a.trip_duration/60<30 $filtersNoWhere");
$under = $under[0]['under'];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Enjoy Chicago - See the city with Divvy</title>
    <link href='http://fonts.googleapis.com/css?family=Arbutus+Slab|IM+Fell+Great+Primer|IM+Fell+Great+Primer+SC' rel='stylesheet' type='text/css'>
    <link href="css/main.css" rel="stylesheet" type="text/css" />
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=visualization"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script type="text/javascript" src="js/jquery.parallax-1.1.3.js"></script>
    <script type="text/javascript" src="js/main.js"></script>
    <script src="js/Chart.min.js"></script>
<script>
// heat map shit
var mapData = [<?php
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
?>];
// END - heat map shit

</script>
<script type="text/javascript" src="js/google.api.heatmap.js"></script>
<script type="text/javascript" src="js/google.api.routing.js"></script>
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
        <h2>Intro</h2>
        <h3>placeholder...</h3>
        
    </div>
    <div id="break_img1" class="break_img">
    	<p>the City Beautiful</p>
    </div>
    <div class="graph-wrapper">
        <div class="corner1"></div>
        <div class="corner2"></div>
        <div class="corner3"></div>
        <div class="corner4"></div>
        <h2>Choropleth map</h2>
        <h3>The proper name for the so-called "heat-map"</h3>
        <div id="heat-map-canvas"></div>
    </div>
	<div id="break_img2" class="break_img">
    	<p>the City in a Garden</p>
    </div>
    <div class="graph-wrapper">
        <div class="corner1"></div>
        <div class="corner2"></div>
        <div class="corner3"></div>
        <div class="corner4"></div>
        <h2>Bike Accumulation by Station</h2>
        <h3># of rides that end at each station - start at each station</h3>
        <canvas id="popCanvas" height="500px" width="900px"></canvas>
    </div>
	<div id="break_img3" class="break_img">
    	<p>the Jewel of the Midwest</p>
    </div>
    <div class="graph-wrapper">
        <div class="corner1"></div>
        <div class="corner2"></div>
        <div class="corner3"></div>
        <div class="corner4"></div>
        <h2>Estimated Bike Routing</h2>
        <h3>Just an estimated potential route Divvy bikes may have taken.</h3>
        <div id="routing-map-canvas"></div>
        <div>
            <label>Select a start date to view a potential route: <input type="date" name="bike-day-start" id="bike-day-start" /></label><br/>
            <label>Select a end date to view a potential route: <input type="date" name="bike-day-end" id="bike-day-end" /></label><br/>
            <label>What Divvy would you like to follow: <input type="number" name="bike-id" id="bike-id" placeholder="1-3003" /><br/>
            <em>Note: not all numbers are valid.</em></label><br/>
            <button type="button" id="submit-route">Submit</button>
        </div>
    </div>
	<div id="break_img4" class="break_img">
    	<p>the Paris on the Prairie</p>
    </div>
    <div class="graph-wrapper">
        <div class="corner1"></div>
        <div class="corner2"></div>
        <div class="corner3"></div>
        <div class="corner4"></div>
        <h2>The Over/Under</h2>
        <h3>Comparison of rides under and over the 30 minute cutoff where the overage fee begins.</h3>
        <canvas id="overageCanvas" height="500px" width="900px"></canvas>
        <div class="legend">
            <div id="overLegend">Over 30 minutes</div>
            <div id="underLegend">Under 30 minutes</div>
        </div>
    </div>
    <div id="break_img5" class="break_img">
    	<p>a Marvel of Engineering</p>
    </div>
    <script>
        // Station Popularity
        var barChartData = {
            labels : [<?=$popLabels?>],
            datasets : [{
                fillColor : "rgba(151,187,205,0.5)",
                strokeColor : "rgba(151,187,205,1)",
                data : [<?=$popNums?>]
            }]
        };

        var popChart = new Chart(document.getElementById("popCanvas").getContext("2d")).Bar(barChartData);

        //overage chart
        var pieData = [
            {
                value: <?=$under?>,
                color: "#E9B055"
            },
            {
                value : <?=$over?>,
                color : "#61A19F"
            }
        ];

        var overageChart = new Chart(document.getElementById("overageCanvas").getContext("2d")).Pie(pieData);
    </script>
</body>
</html>