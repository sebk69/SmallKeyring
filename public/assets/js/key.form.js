/*
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

$("#generate-password").click(function() {
    if($('#password-generate-special-chars').is(':checked')) {
        var charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!()[]#&=+-*/;:!{}?<>$";
    } else {
        var charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    }
    var retVal = "";

    var length = +$("#password-generate-nbr-chars").val();
    for (var i = 0, n = charset.length; i < length; ++i) {
        retVal += charset.charAt(Math.floor(Math.random() * n));
    }

    $("#key_password").val(retVal);
})

$("#copy-password").click(function() {
    clipboard.writeText($("#key_password").val());
})

$("#show-password").click(function() {
    if($("#key_password").attr("type") == "password") {
        $("#key_password").attr("type", "text");
    } else {
        $("#key_password").attr("type", "password");
    }
})