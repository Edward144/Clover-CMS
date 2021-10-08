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

//Comments Recaptcha
var recaptchaWidgets = [];

function comments_loadcaptcha(element) {
    var recaptchaId = element.attr("id");

    if(!element.html().length) {
        recaptchaWidgets[recaptchaId.split("recaptcha")[1]] = grecaptcha.render(recaptchaId, {
            "sitekey": recaptchaSitekey
        });
    }
}

var recaptchaOnload = function() {
    var element = $("#recaptcha0");
    var recaptchaId = element.attr("id");

    if(!element.html().length) {
        recaptchaWidgets[recaptchaId.split("recaptcha")[1]] = grecaptcha.render(recaptchaId, {
            "sitekey": recaptchaSitekey
        });
    }
}

function comments_verifycaptcha(form) {
    var valid = false;
    var recaptchas = form.find(".recaptcha");
    var responses = [];
    
    if(recaptchas.length > 0) {
        event.preventDefault();
        form.find(".alert").remove();
        
        $.each(recaptchas, function() {
            var response = grecaptcha.getResponse(recaptchaWidgets[$(this).attr("id").split("recaptcha")[1]]);
            responses.push(response);
        });

        $.ajax({
            url: root_dir + "includes/actions/verifycaptcha.php",
            method: "post", 
            dataType: "json",
            data: ({responses: JSON.stringify(responses), recaptchaverify: true}),
            success: function(data) {
                if(data['valid'] == false) {
                    $.each(recaptchas, function() {
                        grecaptcha.reset(recaptchaWidgets[$(this).attr("id").split("recaptcha")[1]]);
                    });
                    
                    $("<div class='mt-3 mb-0 py-2 alert alert-" + data['status'] + "'>" + data['message'] + "</div>").insertAfter($("form").find(":submit").parents(".form-group").first());

                    event.preventDefault();
                    return false;
                }
                else {
                    form.unbind("submit").submit();
                }
            }
        });
    }
};

$(".postComment").submit(function() {
    comments_verifycaptcha($(this));
});

$(".comments").on("click", "a.commentReply", function() {
    if($(this).attr("aria-expanded") == "true") {
        var captchas = $(this).parents("h6").first().siblings(".commentForm").find(".recaptcha");
        
        $.each(captchas, function() {
            comments_loadcaptcha($(this));
        });
    }
});