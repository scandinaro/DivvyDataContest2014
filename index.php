<?php
require_once 'includes/meekrodb.2.2.class.php';

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
        elseif ($age == 50){
            $filters .= "a.birth_year<=YEAR(DATE_SUB(NOW(),INTERVAL 50 YEAR)) AND ";
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

//    <option value="19">19-21</option>
//                <option value="22">22-30</option>
//                <option value="31">31-40</option>
//                <option value="41">41-50</option>
//                <option value="50">50+</option>
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Enjoy Chicago - See the city with Divvy</title>
<link href="css/main.css" rel="stylesheet" type="text/css"  />
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=visualization"></script>
<script>
// heat map shit
var map, pointarray, heatmap;

var mapData = [
    <?php
        $results = DB::query("SELECT b.id, b.name, b.latitude, b.longitude, ceil(count(a.id)/500) as num_trips
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
        <form action="index.php" method="post">

            <select id="gender" name="gender">
                <option value="">all genders</option>
                <option value="m">male</option>
                <option value="f">female</option>
            </select>

            <select id="age" name="age">
                <option value="">all ages</option>
                <option value="18">18 and under</option>
                <option value="19">19-21</option>
                <option value="22">22-30</option>
                <option value="31">31-40</option>
                <option value="41">41-50</option>
                <option value="50">50+</option>
            </select>

            <select id="user_type" name="customer_type">
                <option value="">all users</option>
                <option value="customer">customer</option>
                <option value="subscriber">subscriber</option>
            </select>

            <input type="submit" value="filter" />

        </form>
    </header>
    <div class="graph-wrapper">
        <h2>Choropleth map</h2>
        <h3>The proper name for the so-called "heat-map"</h3>
        <div id="map-canvas"></div>
    </div>
</body>
</html>