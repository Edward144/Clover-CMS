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