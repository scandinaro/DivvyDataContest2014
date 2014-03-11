var directionsDisplay, map;
var directionsService = new google.maps.DirectionsService();
var bikeOrigin, bikeDestination, bikeWaypoints=[], routeData;
var waypointLimit = 7;


function initializeRouting() {
    var originData = JSON.parse(routeData.bikeOrigin);
    var destinationData = JSON.parse(routeData.bikeDestination);
    var waypointData = JSON.parse(routeData.bikeWaypoints);
    bikeOrigin = new google.maps.LatLng(originData[0], originData[1]);
    bikeDestination = new google.maps.LatLng(destinationData[0], destinationData[1]);
    $(waypointData).each(function(idx, val){
        if(idx%(Math.ceil(waypointData.length/waypointLimit)) == 0 || waypointData.length < waypointLimit){
            // Because Google maps seems to only allow 7 waypoints per request, so we're going to have to space these out. Also, things will be a lot less cluttered.
            bikeWaypoints.push({
                location: new google.maps.LatLng(val[0], val[1]),
                stopover: true
            });
        }
    });

    var bikeMidpoint = midPoint(bikeOrigin['d'], bikeOrigin['e'], bikeDestination['d'], bikeDestination['e']);
    directionsDisplay = new google.maps.DirectionsRenderer();
    routeMidPoint = new google.maps.LatLng(bikeMidpoint['lat'], bikeMidpoint['long']);
    var mapOptions = {
        zoom: 12,
        center: routeMidPoint
    };
    map = new google.maps.Map(document.getElementById('routing-map-canvas'), mapOptions);
    directionsDisplay.setMap(map);
    setTimeout(calcRoute(), 3000);
}

function calcRoute() {
    var request = {
        origin: bikeOrigin,
        destination: bikeDestination,
        waypoints: bikeWaypoints,
        optimizeWaypoints: true,
        travelMode: google.maps.TravelMode["BICYCLING"]
    };
    directionsService.route(request, function(response, status) {
        if (status == google.maps.DirectionsStatus.OK) {
            directionsDisplay.setDirections(response);
        }
    });
}

function getRouteData (){
    $.ajax({
        type: 'POST',
        url: 'includes/generate_route_data.php?x='+nocache(),
        cache: false,
        data: {
            bike_date : $('#bike-day').val(),
            bike_id: $('#bike-id').val()
        },
        beforeSend:function(){
            routeData=undefined;
        },
        success:function(data){
            // successful request
            if(data == ''){
                // 'Time to panic!!! This should not happen'
            } else if(parseInt(data) == 0) {
                routeData = '';
            } else {
                routeData = data;
            }
        },
        error:function(){}
    });
    isRouteDataAvailable();
}

function isRouteDataAvailable(){
    if(typeof routeData == 'undefined'){
        setTimeout(isRouteDataAvailable, 3000);
    } else {
        if(!routeData.error){
            $('#bike-day').val(routeData.bikeDate);
            $('#bike-id').val(routeData.bikeId);
            initializeRouting();
        } else {
            //Data could not be obtained
            $('#bike-day').val(routeData.bikeDate).css({backgroundColor: '#E67777'});
            $('#bike-id').val(routeData.bikeId).css({backgroundColor: '#E67777'});
            $('#routing-map-canvas')
                .html("<h1 style='padding-top: 150px;color: #ba533f;'>OOPs!</h1><br/><h4>It seems that we don't have any data available for Bike:"+routeData.bikeId+" on: "+routeData.bikeDate+".</h4>")
                .css({textAlign: 'center'});
        }
    }
}

function midPoint(lat1,lon1,lat2,lon2){

    var dLon = Math.radians(lon2 - lon1);

    //convert to radians
    lat1 = Math.radians(lat1);
    lat2 = Math.radians(lat2);
    lon1 = Math.radians(lon1);

    var Bx = Math.cos(lat2) * Math.cos(dLon);
    var By = Math.cos(lat2) * Math.sin(dLon);
    var lat3 = Math.atan2(Math.sin(lat1) + Math.sin(lat2), Math.sqrt((Math.cos(lat1) + Bx) * (Math.cos(lat1) + Bx) + By * By));
    var lon3 = lon1 + Math.atan2(By, Math.cos(lat1) + Bx);

    //print out in degrees
    return {lat:Math.degrees(lat3), long: Math.degrees(lon3)};
}

// Converts from degrees to radians.
Math.radians = function(degrees) {
    return degrees * Math.PI / 180;
};

// Converts from radians to degrees.
Math.degrees = function(radians) {
    return radians * 180 / Math.PI;
};

$(document).ready(function(){
    $('#submit-route').click(getRouteData);
});

function nocache(){
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}

google.maps.event.addDomListener(window, 'load', getRouteData);