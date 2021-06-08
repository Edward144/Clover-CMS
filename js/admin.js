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

$(document).ready(function() {
	sidebarcollapse();
});