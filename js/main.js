//Expand navbar dropdowns
$(".navbar .dropdown-toggle").click(function() {
    e.preventDefault();
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

//Validate forms - Identical to admin.js, make sure to update in both places
$("form").submit(function() {
	var valid = true;
	var passChar = 8;
	
	$(this).find(".invalid-feedback").remove();
	$(this).find(".is-invalid").removeClass("is-invalid");
	
	//Validate Passwords
	if($(this).find("input[name='password']").length && $(this).find("input[name='passwordConf']").length) {
		var pass = $(this).find("input[name='password']");
		var passConf = $(this).find("input[name='passwordConf']");
		
		if(pass.val().length || passConf.val().length) {
			if(pass.val().length < passChar) {
				pass.addClass("is-invalid");
				$("<div class='invalid-feedback'>Password must be at least " + passChar + " characters</div>").insertAfter(pass);

				valid = false;
			}
			else if(!/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/.test(pass.val())) {
				pass.addClass("is-invalid");
				$("<div class='invalid-feedback'>Password must contain at least one lowercase, one uppercase and one digit</div>").insertAfter(pass);

				valid = false;
			}
			else if(pass.val() != passConf.val()) {
				pass.addClass("is-invalid");
				passConf.addClass("is-invalid");
				$("<div class='invalid-feedback'>Passwords do not match</div>").insertAfter(passConf);

				valid = false;
			}
		}
	}
    
    //Validate Urls
    if($(this).find("input[name='url']").length) {
        var url = $(this).find("input[name='url']");
        
        if(!/^[a-zA-Z0-9\:\/\-\_\+\?\&\=\#\.]+$/.test(url.val())) {
            url.addClass("is-invalid");
            $("<div class='invalid-feedback'>Url contains invalid characters. Allowed characters are A-Z, 0-9, :, /, -, _, +, ?, &, =, #, .</div>").insertAfter(url);
            
            valid = false;
        }
    }
	
	if(valid === false) {
		event.preventDefault();
		return;
	}
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
                if(data['valid'] === false) {
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

//Toggle password update fields in myaccount
$("#manageaccount #togglePassword").click(function() {
    if($(this).attr("aria-expanded") === "true") {
        $(this).text("Cancel Password Update");
        $("#updatePassword").find("input[type='password']").val("");
    }
    else {
        $(this).text("Update Password");
    }
});

//Change comment to edit textbox
$(".commentEdit").click(function() {
    event.preventDefault();
    
    var comment = $(this).parents(".comment").first();
    var commentBody = comment.children(".commentBody").first();
    var commentContent = commentContent = commentBody.text();
    
    if($(this).text() == "Edit") {
        $(this).text("Cancel Edit");
        
        $("<a href='#' class='commentUpdate me-3'>Save edit</a>").insertAfter($(this));
        
        commentBody.html(
            "<form class='editComment' action='includes/actions/editcomment' method='post'>" + 
                "<input type='hidden' name='id' value='" + comment.attr("id").split("comment")[1] + "'>" + 
                "<input type='hidden' name='editComment'>" + 
                "<input type='hidden' name='returnurl' value='" + window.location.href.split("#")[0] + "'>" +
                "<textarea class='form-control' name='comment' required>" + commentContent + "</textarea>" + 
            "</form>"
        );
    }
    else {
        $(this).text("Edit");
        $(this).siblings(".commentUpdate").remove();
        
        commentBody.html(commentContent)
    }
});

//Submit comment edit form
$(".comment").on("click", ".commentUpdate", function() {
    event.preventDefault();
    
    var form = $(this).parents(".comment").first().find(".editComment").first();
    
    if(form.find("textarea[name='comment']").val() != "") {
        form.submit();
    }
});

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