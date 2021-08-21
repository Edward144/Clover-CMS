//Expand navbar dropdowns
$(".navbar .dropdown-toggle").click(function() {
    $(this).parent(".nav-item").toggleClass("exp");
});

//Sticky header
function sticky(element) {
    var elementHeight = element.outerHeight();
    var elementTop = element.offset()['top'];
    
    if($(window).scrollTop() > elementHeight + elementTop) {
        element.addClass("sticky");
    }
    else if($(window).scrollTop() == 0) {
        element.removeClass("sticky");
    }
    
    if(element.attr("id") == "pageHeader" && element.siblings(".main") && element.hasClass("sticky")) {
        element.siblings(".main").first().css({
            "margin-top" : elementHeight
        });
    }
    else {
        element.siblings(".main").first().css({
            "margin-top" : ""
        });
    }
}

$(window).scroll(function() {
    sticky($("#pageHeader"));
});

$(document).ready(function() {
    sticky($("#pageHeader"));
});