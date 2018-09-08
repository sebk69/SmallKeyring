/*
 *  This file is a part of SmallKeyring
 *  Copyright 2018 - Sébastien Kus
 *  Under GNU GPL V3 licence
 */

$(".enable-select").change(function (e) {
    $(this).parent("form").submit();
})