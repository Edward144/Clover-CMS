//Toggle sidebar
$(".sidebar").on("click", "#toggleSidebar", function() {
	var sidebar = $(this).parents(".sidebar").first();
	
	if(sidebar.hasClass("collapse-left")) {
		sidebar.removeClass("collapse-left");
		//sidebar.find(".floatingToggle").remove();
	}
	else {
		sidebar.addClass("collapse-left");
		//$(this).clone().addClass("floatingToggle").prependTo(sidebar);
	}
});

//Collapse sidebar by default on smaller devices 
function sidebarcollapse() {
	if($(window).width() < 768) {
		var sidebar = $(".wrapper > .main > .sidebar");
		
		sidebar.hide().addClass("collapse-left");
			
		sidebar.on("transitionend", function() {
			sidebar.show();
		});
	}
}

//Split full url from media manager
function responsive_filemanager_callback(field_id) {
	var url = $("#" + field_id).val().split(root_dir)[1];
	$("input[name='" + field_id + "']").val(url).trigger("change");
}

$(document).ready(function() {
	sidebarcollapse();
});

//Validate forms
$("form").submit(function() {
	var valid = true;
	var passChar = 8;
	
	$(this).find(".invalid-feedback").remove();
	$(this).find(".is-invalid").removeClass("is-invalid");
	
	//Validate Passwords
	if($(this).find("input[name='password']") && $(this).find("input[name='passwordConf']")) {
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
	
	if(valid == false) {
		event.preventDefault();
		return;
	}
});

//Confirm inputs
$("input[type='submit'][data-confirm]").click(function() {
	if(!confirm($(this).attr("data-confirm"))) {
		event.preventDefault();
		return;
	}
});