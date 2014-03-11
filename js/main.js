$( document ).ready(function() {
    if($('#IE').length > 0){
        browserManager.restrict();
    } else {
        browserManager.manage();
    }
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
        } else {
            $("#user_type").val('').attr("disabled", true);
        }
    });

    $("#age").change(function() {
        var age = $("#age").val();
        var gender = $("#gender").val();
        if (age == "" && gender == "") {
            $("#user_type").attr("disabled", false);
        } else {
            $("#user_type").val('').attr("disabled", true);
        }
    });

    $("#user_type").change(function() {
        var user_type = $("#user_type").val();
        if (user_type == "customer") {
            $("#age").attr("disabled", true);
            $("#gender").attr("disabled", true);
        } else {
            $("#age").attr("disabled", false);
            $("#gender").attr("disabled", false);
        }
    });

    loadIntroData();
    loadCharts('','','');

});


function loadIntroData(){
    //intro data
    $.get("includes/generate_intro_data.php", function(data) {
        data = $.parseJSON(data);
        var pieData1 = [
            {
                value: parseInt(data.male),
                color: "#a0c7c5"
            },
            {
                value : parseInt(data.female),
                color : "#61A19F"
            }
        ];
        var genderChart = new Chart(document.getElementById("genderCanvas").getContext("2d")).Pie(pieData1);
        displayChartPercent('malePercent', data.male, 'femalePercent', data.female);

        var pieData2 = [
            {
                value: parseInt(data.customer),
                color: "#E9B055"
            },
            {
                value : parseInt(data.subscriber),
                color : "#D4791C"
            }
        ];
        var user_typeChart = new Chart(document.getElementById("user_typeCanvas").getContext("2d")).Pie(pieData2);
        displayChartPercent('customerPercent', data.customer, 'subscriberPercent', data.subscriber);
    });
}


function loadCharts(gender, age, user_type){
    //intro data
    $.get("includes/generate_intro_data.php?age=" + age, function(data) {
        data = $.parseJSON(data);
        var pieData1 = [
            {
                value: parseInt(data.male),
                color: "#a0c7c5"
            },
            {
                value : parseInt(data.female),
                color : "#61A19F"
            }
        ];
        var genderChart = new Chart(document.getElementById("genderCanvas").getContext("2d")).Pie(pieData1);
        displayChartPercent('malePercent', data.male, 'femalePercent', data.female);
    });

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
        var lowestVal = 0;
        $.each(data, function(i, item) {
            acumLabels.push(item.label);
            acumNums.push(item.num);
            if (parseInt(item.num)<lowestVal) {
                lowestVal = item.num;
            }
        });

        var barChartData = {
            labels : acumLabels,
            datasets : [{
                fillColor : "rgba(151,187,205,0.5)",
                strokeColor : "rgba(151,187,205,1)",
                data : acumNums
            }]
        };
        lowestVal = Math.ceil(lowestVal/100)*100 - 100;
        var scaleStepWidth = Math.ceil((Math.abs(lowestVal)/8)/25)*25;
        var scaleSteps = Math.ceil((Math.abs(lowestVal)*2/scaleStepWidth)) + 2;
        var popChart = new Chart(document.getElementById("popCanvas").getContext("2d")).Bar(barChartData, {scaleOverride : true, scaleSteps : scaleSteps, scaleStepWidth : scaleStepWidth , scaleStartValue : lowestVal - 100});
    });

    //overage chart
    $.get("includes/generate_overunder_data.php?gender=" + gender + "&age=" + age + "&user_type=" + user_type, function(data) {
        data = $.parseJSON(data);
        var pieData = [
            {
                value: parseInt(data.under),
                color: "#E16652"
            },
            {
                value : parseInt(data.over),
                color : "#BA533f"
            }
        ];
        var overageChart = new Chart(document.getElementById("overageCanvas").getContext("2d")).Pie(pieData);
        displayChartPercent('underPercent', data.under, 'overPercent', data.over);
    });

}

function displayChartPercent(leftDiv, leftVal, rightDiv, rightVal){
    var leftInt = parseInt(leftVal);
    var rightInt = parseInt(rightVal);
    var total = leftInt+rightInt;
    $('#'+leftDiv).html( Math.round(leftInt/total*100)+"%" );
    $('#'+rightDiv).html( Math.round(rightInt/total*100)+"%" );
}

var browserManager = {
    restrict: function(){
        $('#filter_data').hide();
        $('body').scrollTop(0).css({'overflow':'hidden'});
        $(document).bind('scroll',function () {
            window.scrollTo(0,0);
        });
        $('header').after('<div id="bad-browser">Sorry, but this site does not support Internet Explorer or Mobile devices.</div>');
    },
    unrestrict:function(){
        $('#filter_data').show();
        $(document).unbind('scroll');
        $('body').css({'overflow':'visible'});
        $('#bad-browser').remove();
    },
    manage:function(){
        $.get("includes/browser_denial.php", function(data) {
            if(parseInt(data) == 1){
                browserManager.restrict();
            } else {
                browserManager.unrestrict();
            }
        });
    }
};
