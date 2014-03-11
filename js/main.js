$( document ).ready(function() {
    $('#break_img1').parallax("50%", 0.3);
    $('#break_img2').parallax("50%", 0.3);
    $('#break_img3').parallax("50%", 0.3);
    $('#break_img4').parallax("50%", 0.3);
    $('#break_img5').parallax("50%", 0.3);

    $("#filter_data").submit(function( event ) {
        loadCharts($("#gender").val(), $("#age").val(), $("#user_type").val());
        event.preventDefault();
    });

    $("#gender").change(function() {
        var gender = $("#gender").val();
        var age = $("#age").val();
        if (gender == "" && age == "") {
            $("#user_type").attr("disabled", false);
        }
        else {
            $("#user_type").val('');
            $("#user_type").attr("disabled", true);
        }
    });

    $("#age").change(function() {
        var age = $("#age").val();
        var gender = $("#gender").val();
        if (age == "" && gender == "") {
            $("#user_type").attr("disabled", false);
        }
        else {
            $("#user_type").val('');
            $("#user_type").attr("disabled", true);
        }
    });

    $("#user_type").change(function() {
        var user_type = $("#user_type").val();
        if (user_type == "customer") {
            $("#age").attr("disabled", true);
            $("#gender").attr("disabled", true);
        }
        else {
            $("#age").attr("disabled", false);
            $("#gender").attr("disabled", false);
        }
    });

    loadCharts('','','');

});

function loadCharts(gender, age, user_type){
    //heat map
    mapData = [];
    $.get("includes/generate_heatmap_data.php?gender=" + gender + "&age=" + age + "&user_type=" + user_type, function(data) {
        data = $.parseJSON(data);
        $.each(data, function(i, item) {
            mapData.push(new google.maps.LatLng(item.latitude, item.longitude));
        });

        var pointArray = new google.maps.MVCArray(mapData);

        heatmap = new google.maps.visualization.HeatmapLayer({
            data: pointArray
        });

        heatmap.setMap(map);
    });

    // bike accumulation
    var pop_labels, pop_nums = '';
    $.get("includes/generate_bike_acum_data.php?gender=" + gender + "&age=" + age + "&user_type=" + user_type, function(data) {
        data = $.parseJSON(data);
        var acumLabels = [];
        var acumNums = [];
        $.each(data, function(i, item) {
            acumLabels.push(item.label);
            acumNums.push(item.num);
        });

        var barChartData = {
            labels : acumLabels,
            datasets : [{
                fillColor : "rgba(151,187,205,0.5)",
                strokeColor : "rgba(151,187,205,1)",
                data : acumNums
            }]
        };
        var popChart = new Chart(document.getElementById("popCanvas").getContext("2d")).Bar(barChartData);
    });

    //overage chart
    var over = 0, under = 0;
    $.get("includes/generate_overunder_data.php?gender=" + gender + "&age=" + age + "&user_type=" + user_type, function(data) {
        data = $.parseJSON(data);
        var underVal = parseInt(data.under);
        var overVal = parseInt(data.over);
        var total = overVal+underVal;
        var pieData = [
            {
                value: underVal,
                color: "#E9B055"
            },
            {
                value : overVal,
                color : "#61A19F"
            }
        ];
        var overageChart = new Chart(document.getElementById("overageCanvas").getContext("2d")).Pie(pieData);
        $('#underPercent').html( Math.round((underVal/total)*100)+'%' );
        $('#overPercent').html( Math.round((overVal/total)*100)+'%' );
    });



}