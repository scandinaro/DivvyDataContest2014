var map, heatmap;

var mapData = [];

function initialize() {
    var mapOptions = {
        zoom: 11,
        center: new google.maps.LatLng(41.889710, -87.629788),
        mapTypeId: google.maps.MapTypeId.SATELLITE
    };

    map = new google.maps.Map(document.getElementById('heat-map-canvas'), mapOptions);

    var pointArray = new google.maps.MVCArray(mapData);

    heatmap = new google.maps.visualization.HeatmapLayer({
        data: pointArray
    });

    heatmap.setMap(map);
}

google.maps.event.addDomListener(window, 'load', initialize);