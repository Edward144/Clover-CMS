//Resize calendar
function calendar_resize(element) {
    var width = element.outerWidth();

    if(width < 576) {
        element.addClass("calendar-sm");
        element.removeClass("calendar-lg calendar-md");
    }
    else if(width < 768) {
        element.addClass("calendar-md");
        element.removeClass("calendar-lg calendar-sm");
    }
    else if(width < 992) {
        element.addClass("calendar-lg");
        element.removeClass("calendar-sm calendar-md");
    }
}

$(document).ready(function() {
    calendar_resize($(".calendar"));
    calendar_resize($(".calendarSmall"));
});

$(window).resize(function() {
    calendar_resize($(".calendar"));
    calendar_resize($(".calendarSmall"));
});