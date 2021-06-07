//Generate password
$("input[name='generate']").click(function() {			
	$.ajax({
		url: window.location.pathname,
		method: "post",
		dataType: "json",
		data: ({generate: true}),
		success: function(data) {
			$(".formBody .alert").remove();
			$("input[name='password'],input[name='passwordConf']").val(data["password"]);
			$(".formBody").append("<div class='alert alert-" + data["status"] + " mb-0 mt-3'>" + data["message"] + "</div>");
		}
	});
});

//Validate password reset
$("#adminLogin.reset").submit(function() {
	var chars = 8;
	var pass = $(this).find("input[name='password']");
	var passConf = $(this).find("input[name='passwordConf']");
	
	$(this).find(".has-invalid-feedback, .is-invalid").removeClass("has-invalid-feedback is-invalid");
	$(this).find(".invalid-feedback").remove();
	
	if(pass.val().length < chars) {
		pass.addClass("is-invalid");
		pass.parent(".input-group").addClass("has-invalid-feedback");
		$("<div class='invalid-feedback'>Password must be at least " + chars + " characters</div>").insertAfter(pass);
	}
	else if(/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])+/.test(pass.val()) == false) {
		pass.addClass("is-invalid");
		pass.parent(".input-group").addClass("has-invalid-feedback");
		$("<div class='invalid-feedback'>Password must contain at least one lowercase, one uppercase and one numeric character</div>").insertAfter(pass);
	}
	else if(pass.val() != passConf.val()) {
		passConf.addClass("is-invalid");
		passConf.parent(".input-group").addClass("has-invalid-feedback");
		$("<div class='invalid-feedback'>Password's do not match</div>").insertAfter(passConf);
	}
	
	if($(this).find(".is-invalid").length) {
		event.preventDefault();
		return;
	}
});