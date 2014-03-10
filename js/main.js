$( document ).ready(function() {
    $('#break_img1').parallax("50%", 0.3);
    $('#break_img2').parallax("50%", 0.3);
    $('#break_img3').parallax("50%", 0.3);
    $('#break_img4').parallax("50%", 0.3);
    $('#break_img5').parallax("50%", 0.3);

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
});