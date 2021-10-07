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

//Load new comments
function comments_load(contentid, parentid) {
    if(parentid == 0) {
        var appendTo = $(".comments > .comment").last(); 
        var offset = $(".comments > .comment").length;
    }
    else {
        var appendTo = $("#comment" + parentid + " > .comment").last();
        var offset = $("#comment" + parentid + " > .comment").length;
    }
    
    $.ajax({
        url: "includes/classes/comments.class.php",
        method: "post",
        dataType: "html",
        data: ({loadcomments: true, contentid: contentid, parent: parentid, offset: offset}),
        success: function(data) {
            $(data).insertAfter(appendTo);
        },
        error: function(a, b, c) {
            console.log(c);
        }
    });
}