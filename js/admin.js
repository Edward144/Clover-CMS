//Toggle sidebar
$(".sidebar").on("click", "#toggleSidebar", function() {
	var sidebar = $(this).parents(".sidebar").first();
	
	if(sidebar.hasClass("collapse-left")) {
		sidebar.removeClass("collapse-left");
	}
	else {
		sidebar.addClass("collapse-left");
	}
});

//Collapse sidebar by default on smaller devices 
function sidebarcollapse() {
	if($(window).width() < 768) {
		var sidebar = $(".wrapper > .main > .sidebar");
		
		sidebar.hide().addClass("collapse-left");
	
        setTimeout(function() {
            sidebar.show();
        }, 70);
        
        //This doesn't work as we are hiding the element first, so the transition end isn't detected
		/*sidebar.on("transitionend webkitTransitionEnd oTransitionEnd", function() {
			sidebar.css("visibility", "");
		});*/
	}
}

//Split full url from media manager
function responsive_filemanager_callback(field_id) {
	var url = $("#" + field_id).val().split(location.protocol + "//" + location.hostname + root_dir)[1];
    $("input[name='" + field_id + "']").val(url).trigger("change");
}

$(document).ready(function() {
	sidebarcollapse();
});

//Validate forms - Identical to main.js, make sure to update in both places
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
	
	if(valid == false) {
		event.preventDefault();
		return;
	}
});

//Validate urls on keyup
$("input[name='url']").keyup(function() {
    var url = $(this);
    
    url.removeClass("is-invalid");
    url.siblings(".invalid-feedback").remove();
    
    if(!/^[a-zA-Z0-9\:\/\-\_\+\?\&\=\#\.]+$/.test(url.val())) {
        url.addClass("is-invalid");
        $("<div class='invalid-feedback'>Url contains invalid characters. Allowed characters are A-Z, 0-9, :, /, -, _, +, ?, &, =, #, .</div>").insertAfter(url);
    }
});

//Confirm inputs
$("input[type='submit'][data-confirm]").click(function() {
	if(!confirm($(this).attr("data-confirm"))) {
		event.preventDefault();
		return;
	}
});

//Clear search || Return to list
$("input[name='clearSearch'],input[name='returnList']").click(function() {
	window.location.href = window.location.href.split("?")[0];
});

//Delete content
$("input[name='deleteContent'], input[name='deleteEvent']").click(function() {
    var btn = $(this);
	if(confirm("Are you sure you want to delete this content?")) {
		if(btn.attr("data-id").length) {
			$.ajax({
				url: window.location.pathname,
				method: "post",
				dataType: "json",
				data: ({id: $(this).attr("data-id"), method: "deleteContent"}),
				success: function(data) {
					if(data["status"] == "success") {
						location.reload();
					}
					else {
						var message = "<div class='alert alert-" + data["status"] + "'>" + data["message"] + "</div>";
                        
						if(btn.parents("table").first().length > 0) {
							btn.parents("table").first().find(".alert").remove();
							$(message).insertBefore(btn.parents("table").first());
						}
						else {
							btn.parents("form").first().find(".alert").remove();
							$(message).appendTo(btn.parents("form").first());
						}
					}
				}
			});
		}
	}
});

//Count input characters
$(".countChars").keyup(function() {
    var limit = $(this).attr("maxlength");
    var chars = $(this).val().length;
    
    if($(this).siblings("small.charCount").length) {
        $(this).siblings("small.charCount").text(chars + " of " + limit);
    }
    else {
        $("<small class='charCount d-block text-end'>" + chars + " of " + limit + "</small>").insertAfter($(this));
    }
});

//Validate other settings
$("#otherSettings").submit(function() {
    var valid = true;
    var analytics = $(this).find("input[name='googleAnalytics']");
    
    //e.g.                         UA-123456789-0                                          G-A1BC2DEF34
    if(analytics.val().length && (!/^[A-Z]{2}\-[0-9]{8}\-[0-9]$/.test(analytics.val()) && !/^[A-Z]{1}\-[A-Z0-9]{10}$/.test(analytics.val()))) {
        analytics.addClass("is-invalid");
        $("<div class='invalid-feedback'>Value does not appear to be valid</div>").insertAfter(analytics);

        valid = false;
    }
    
    if(valid == false) {
        event.preventDefault();
        return;
    }
});

//Change navigation menu
$("select[name='chooseMenu']").change(function() {
    window.location.href = window.location.href.split('manage-navigation/')[0] + "manage-navigation/" + $(this).val();
});

//Select existing page to insert
$("#insertNavigation select[name='existing']").change(function() {
    var option = $(this).children(":selected");
    var name = option.attr("data-name");
    var url = option.attr("data-url");
    
    $("#insertNavigation").find("input[name='name']").val(name);
    $("#insertNavigation").find("input[name='url']").val(url);
});

//Edit existing navigation item
$(".structure").on("click", "button[name='edit']", function() {
    $(this).parents(".navigationLevel").first().find(".modal").first().modal("show");
});

//Delete existing navigation item 
$(".structure").on("click", "button[name='delete']", function() {
    if(confirm("Are you sure you want to delete this item?")) {
        $(this).parents(".navigationLevel").first().find("input[name='delete']").first().val(1);
        $(this).parents(".navigationLevel").first().hide();
    }
});

//Re-order existing navigation items
$(".structureItems").sortable({
    items: ".navigationLevel",
    connectWith: ".structureItems",
    dropOnEmpty: true,
    stop: function() {
        $(".structure .navigationLevel").each(function() {
            var position = $(this).prevAll(".navigationLevel").length;
            var edit = $(this).find(".modal").first();
            
            if($(this).parent().is(".structure")) {
                var parent = 0;
            }
            else {
                var parent = $(this).parents(".navigationLevel").first().attr("data-id");
            }
                
            edit.find("input[name='parent']").val(parent);
            edit.find("input[name='position']").val(position);
        });
    }
});

//Save navigation structure
$(".structure input[name='saveStructure']").click(function() {
    var json = {};
    var btn = $(this);
    var structure = $(this).parents(".structure").first();
    var i = 0;
    
    structure.find(".alert").remove();
    
    structure.find(".navigationLevel").each(function() {
        var form = $(this).children(".modal").first();
        
        json[i] = {
            "id": form.find("input[name='id']").val(),
            "name": form.find("input[name='name']").val(),
            "url": form.find("input[name='url']").val(),
            "visible": form.find("select[name='visible']").val(),
            "parent": form.find("input[name='parent']").val(),
            "position": form.find("input[name='position']").val(),
            "delete": form.find("input[name='delete']").val()
        }
        
        i++;
    });
    
    if(structure.find(".is-invalid").length || structure.find(".invalid-feedback").length) {
        event.preventDefault();
        $("<div class='alert alert-danger mt-3'>You have entered invalid information. Please check your inputs and try again.</div>").insertAfter(btn.parent(".form-group"));
        
        return;
    }
    
    structure.find("input[name='json']").val(JSON.stringify(json));
});

//Forms
function formbuilder_generaterandom() {
    var random = btoa(new Date().getTime()).split("=")[0];
    return random;
}

//Save structure
function formbuilder_save(output, builder = "", debug = false) {
    var structure = {};

    if(builder == "") {
        builder = $(".formbuilder").first();
    }
    else {
        builder = builder;
    }

    //Get form info
    structure['formid'] = builder.children(".list-group").children(".actions").find("input[name='formid']").val();
    structure['action'] = builder.children(".list-group").children(".actions").find("input[name='action']").val();
    structure['method'] = builder.children(".list-group").children(".actions").find("select[name='method']").val();

    //Loop groups
    var groups = [];
    var gi = 0;

    builder.children(".list-group").children(".list-group-item:not(.actions)").each(function() {            
        //Loop inputs
        var inputs = [];
        var ii = 0;

        $(this).children(".inputs").children(".input").each(function() {                
            var inputValues = {};

            //Add parameters
            $(this).find("input:not([type='button']):not([type='submit']), textarea, checkbox").each(function() {
                if(!$(this).parents(".list-group-item").first().hasClass("option")) {
                    var index = $(this).attr("name");

                    if($(this).is(":checkbox")) {
                        if($(this).is(":checked")) {
                            var value = true;
                        }
                        else {
                            var value = false;
                        }
                    }
                    else {
                        var value = $(this).val();
                    }

                    inputValues[index] = value;
                }
            });

            //Add options (select, radio)
            if($(this).find(".options").length) {
                var options = [];

                $(this).find(".option").each(function() {
                    if($(this).find(":radio").length) {
                        //Radio, also add default
                        if($(this).find(":radio:checked").length) {
                            var isDefault = true;
                        }
                        else {
                            var isDefault = false;
                        }

                        options.push({
                            "value": $(this).find("input[name='optionvalue']").first().val(),
                            "default": isDefault
                        });
                    }
                    else {
                        //Select, only needs value
                        options.push({
                            "value": $(this).find("input[name='optionvalue']").first().val()
                        });
                    }
                });

                inputValues['options'] = options;
            }

            inputs[ii] = inputValues;

            ii++;
        });

        groups[gi] = {
            "groupid": $(this).find("input[name='groupid']").first().val(),
            "name": $(this).find("input[name='groupname']").first().val(),
            "inputs": inputs
        }

        gi++;
    });

    structure['groups'] = groups;

    if(debug == true) {
        console.log(structure);
    }
    else {
        output.val(JSON.stringify(structure));
    }
}

$("#manageForm").submit(function() {
    formbuilder_save($(this).find("input[name='structure']"));
});

//Add group
function formbuilder_addgroup(button) {
    var groupCount = $(".formbuilder").children(".groups").children(".group").length + 1;
    var groupData = {
        "groupid": formbuilder_generaterandom(),
        "name": "Group " + groupCount,
        "inputs": []
    };

    $.ajax({
        url: root_dir + "includes/classes/formbuilder.class.php",
        method: "post",
        dataType: "json",
        data: ({id: $(this).parent(".structure").first().find("input[name='formid']").val(), data: JSON.stringify(groupData), formbuilder_method: "addGroup"}),
        success: function(data) {
            $(data).insertBefore(button.parents(".list-group-item").first());
            formbuilder_disablesubmit();
            formbuilder_sortable($(".groups"));
        }
    });
}

$(".formbuilder").on("click", "input[name='addGroup']", function() {
    formbuilder_addgroup($(this));
});

//Add input
function formbuilder_addinput(button, input = "", inputData = {}) {
    if(input == "") {
        inputData["type"] = button.siblings("select[name='inputType']").val();
    }
    else {
        inputData["type"] = input;
    }

    inputData["inputid"] = formbuilder_generaterandom();

    $.ajax({
        url: root_dir + "includes/classes/formbuilder.class.php",
        method: "post",
        dataType: "json",
        data: ({id: $(this).parent(".structure").first().find("input[name='formid']").val(), data: JSON.stringify(inputData), formbuilder_method: "addInput"}),
        success: function(data) {
            $(data).insertBefore(button.parents(".list-group-item").first());
            formbuilder_disablesubmit();
            formbuilder_sortable($(".inputs"));
        }
    });
}

$(".formbuilder").on("click", "input[name='addInput']", function() {
    formbuilder_addinput($(this));
});

//Add option to select
function formbuilder_addoption_select(button) {
    $.ajax({
        url: root_dir + "includes/classes/formbuilder.class.php",
        method: "post",
        dataType: "json",
        data: ({id: $(this).parent(".structure").first().find("input[name='formid']").val(), formbuilder_method: "addOptionSelect"}),
        success: function(data) {
            $(data).insertBefore(button.parents(".list-group-item").first());
        }
    });
}

$(".formbuilder").on("click", "input[name='addOptionSelect']", function() {
    formbuilder_addoption_select($(this));
});

//Add option to radio
function formbuilder_addoption_radio(button) {
    var count = button.parents(".list-group").first().children(".list-group-item:not(.actions)").length;
    var inputId = button.parents(".input").first().find("input[name='inputid']").first().val();

    if(count > 0) {
        var isDefault = false;
    }
    else {
        var isDefault = true;
    }

    $.ajax({
        url: root_dir + "includes/classes/formbuilder.class.php",
        method: "post",
        dataType: "json",
        data: ({id: $(this).parent(".structure").first().find("input[name='formid']").val(), inputId, isDefault, formbuilder_method: "addOptionRadio"}),
        success: function(data) {
            $(data).insertBefore(button.parents(".list-group-item").first());
        }
    });
}

$(".formbuilder").on("click", "input[name='addOptionRadio']", function() {
    formbuilder_addoption_radio($(this));
});

//Delete item
function formbuilder_deleteitem(button) {
    var type = button.val().split("× ")[1].toLowerCase();
    var parent = button.parents(".list-group").first();
    var isChecked = button.parent(".input-group").find("input[type='radio']:checked").length;

    if(confirm("Are you sure you want to delete this " + type + "?")) {
        button.parents(".list-group-item").first().remove();

        if(type == "option" && isChecked > 0) {
            parent.find("input[type='radio']").first().prop("checked", true);
        }

        formbuilder_disablesubmit();
    }
}

$(".formbuilder").on("click", "input[name='deleteGroup'], input[name='deleteInput'], input[name='deleteOption']", function() {
    formbuilder_deleteitem($(this));
});

//Prevent submit inputs from being added if one already exists
function formbuilder_disablesubmit() {
    if($(".formbuilder").find("input[name='type'][value='submit']").length) {
        $(".formbuilder select[name='inputType']").children("option[value='input_submit']").prop("disabled", true);
        $(".formbuilder select[name='inputType']").children("option").prop("selected", false);
    }
    else {
        $(".formbuilder select[name='inputType']").children("option[value='input_submit']").prop("disabled", false);
    }
}

//Re-order form builder elements
function formbuilder_sortable(element) {
    element.sortable({
        cancel: ".actions",
        items: ".list-group-item:not(.actions)",
        containment: "parent",
        cancel: "input,textarea,select"
    });
}

$(document).ready(function() {
    formbuilder_disablesubmit();
    formbuilder_sortable($(".groups"));
    formbuilder_sortable($(".inputs"));
});

$(".formbuilder").on("click", "input[name='expander']", function() {
    if($(this).val() == "Expand") {
        $(this).val("Collapse");
    }
    else {
        $(this).val("Expand");
    }
});

//Delete form
$("input[name='deleteForm']").click(function() {
    var id = $(this).attr("data-id");
    $(".contentInner").find(".alert" ).remove();
    
    if(confirm("Are you sure you want to delete this form?")) {
        $.ajax({
            url: window.location.href,
            method: "post",
            dataType: "json",
            data: ({id: id, deleteForm: true}),
            success: function(data) {
                if(data['status'] == 'success') {
                    window.location.reload();
                }
                else {
                    $("<div class='alert alert-" + data['status'] + " mt-3'>" + data['deletemsg'] + "</div>").appendTo($(".contentInner > div:first-child"));
                }
            }
        });
    }
});

//Confirm role deletion
$(".deleteRole").submit(function() {
    if(!confirm("Are you sure you want to delete this role? All users set to this role will be changed to Standard users.")) {
        event.preventDefault();
        return;
    }
});

//Delete Comment
$("input[name='deleteComment']").click(function() {
    var btn = $(this);
    
	if(confirm("Are you sure you want to delete this comment? Any replies will also be hidden.")) {
		if(btn.attr("data-id").length) {
			$.ajax({
				url: window.location.pathname,
				method: "post",
				dataType: "json",
				data: ({id: $(this).attr("data-id"), method: "deleteComment"}),
				success: function(data) {
					if(data["status"] == "success") {
						location.reload();
					}
					else {
						var message = "<div class='alert alert-" + data["status"] + "'>" + data["message"] + "</div>";
                        
						if(btn.parents("table").first().length > 0) {
							btn.parents("table").first().find(".alert").remove();
							$(message).insertBefore(btn.parents("table").first());
						}
						else {
							btn.parents("form").first().find(".alert").remove();
							$(message).appendTo(btn.parents("form").first());
						}
					}
				}
			});
		}
	}
});

//Modify Comment
$("input[name='modifyComment']").click(function() {
    $(this).parents("table").first().find(".alert").remove();
    
    var btn = $(this);
    var id = btn.attr("data-id");
    var comment = btn.parents("tr").first().find("textarea[name='comment']").val();
    
    if(btn.siblings(".form-check").children("input[name='approved']").is(":checked")) {
        var approved = 1;
    }
    else {
        var approved = 0;
    }
    
    if(btn.attr("data-id").length) {
        $.ajax({
            url: window.location.pathname,
            method: "post",
            dataType: "json",
            data: ({id: id, approved: approved, comment: comment, method: "modifyComment"}),
            success: function(data) {
                if(data["status"] == "success") {
                    location.reload();
                }
                else {
                    var message = "<div class='alert alert-" + data["status"] + "'>" + data["message"] + "</div>";

                    if(btn.parents("table").first().length > 0) {
                        btn.parents("table").first().find(".alert").remove();
                        $(message).insertBefore(btn.parents("table").first());
                    }
                    else {
                        btn.parents("form").first().find(".alert").remove();
                        $(message).appendTo(btn.parents("form").first());
                    }
                }
            }
        });
    }
});

////Carousel

//Show Controls
function carousel_loadcontrols(carousel) {
    carousel.find(".carousel-item:not(.additionalSlide)").each(function() {
        if(!$(this).find(".carouselControls").length) {
            $(
                "<div class='carouselControls'>" +
                    "<button type='button' class='btn btn-dark btnTop' name='carouselTextT' data-toggle='tooltip' data-placement='bottom' title='Text Align Top'><span class='fas fa-grip-lines'></span></button>" +
                    "<button type='button' class='btn btn-dark' name='carouselTextM' data-toggle='tooltip' data-placement='bottom' title='Text Align Middle'><span class='fas fa-grip-lines'></span></button>" +
                    "<button type='button' class='btn btn-dark btnBottom' name='carouselTextB' data-toggle='tooltip' data-placement='bottom' title='Text Align Bottom'><span class='fas fa-grip-lines'></span></button>" +
                    "<button type='button' class='btn btn-dark' name='carouselTextL' data-toggle='tooltip' data-placement='bottom' title='Text Align Left'><span class='fas fa-align-left'></span></button>" +
                    "<button type='button' class='btn btn-dark' name='carouselTextC' data-toggle='tooltip' data-placement='bottom' title='Text Align Center'><span class='fas fa-align-center'></span></button>" +
                    "<button type='button' class='btn btn-dark' name='carouselTextR' data-toggle='tooltip' data-placement='bottom' title='Text Align Right'><span class='fas fa-align-right'></span></button>" +
                    "<button type='button' class='btn btn-dark btnVertical' name='carouselTitleColor' data-toggle='tooltip' data-placement='bottom' title='Title Colour'><span class='fas fa-heading'></span><span class='fas fa-palette'></span></button>" +
                    "<button type='button' class='btn btn-dark btnVertical' name='carouselTaglineColor' data-toggle='tooltip' data-placement='bottom' title='Tagline Colour'><span class='fas fa-paragraph'></span><span class='fas fa-palette'></span></button>" +
                    "<button type='button' class='btn btn-dark' name='carouselImage' data-toggle='tooltip' data-placement='bottom' title='Choose Image'><span class='fas fa-image'></span></button>" +
                    "<button type='button' class='btn btn-danger' name='carouselDelete' data-toggle='tooltip' data-placement='bottom' title='Delete Slide'><span class='fas fa-trash'></span></button>" +
                "</div>"
            ).prependTo($(this));
            
            $("[data-toggle='tooltip']").tooltip();
        }
        
        carousel_checkimage($(this));
    });
}

$(document).ready(function() {
    if($(".carousel.builder").length) {
        carousel_loadcontrols($(".carousel.builder"));
    }
});

//Save Carousel
function carousel_save(carousel) {
    var savelocation = $("input[name='carousel']");
    
    if(carousel.length && savelocation.length) {
        var json = [];
        var i = 0;
        
        carousel.find(".carousel-item:not(.additionalSlide)").each(function() {
            var image = $(this).find("img.background").attr("src");
            var title = $(this).find("input[name='carouselTitle']").val();
            var tagline = $(this).find("input[name='carouselTagline']").val();
            var titlecolor = $(this).find("input[name='carouselTitle']").css("color");
            var taglinecolor = $(this).find("input[name='carouselTagline']").css("color");
            var position = $(this).find("img.background").css("object-position");
            var textalign = $(this).find("input[name='carouselTitle']").css("text-align");
            var verticalalign = $(this).find(".carousel-item-inner").css("justify-content");
            
            json[i] = {
                "image": image,
                "imageposition": position,
                "title": title,
                "titlecolor": titlecolor,
                "tagline": tagline,
                "taglinecolor": taglinecolor,
                "textalign": textalign,
                "verticalalign": verticalalign
            };
            
            i++;
        });
        
        savelocation.val(JSON.stringify(json));
    }
    else {
        console.log("Err: Carousel cannot be saved");
    }
}

$("#manageContent").submit(function() {
    if($(this).find(".carousel.builder").length) {
        carousel_save($(this).find(".carousel.builder"));
    }
});

//Regenerate Carousel
function carousel_regen(carousel) {
    var savelocation = $("input[name='carousel']");
    var carouseljson = savelocation.val();
    
    $.ajax({
        url: window.location.pathname,
        method: "post",
        dataType: "json",
        data: ({carouselid: carousel.attr("id").split("carousel")[1], carouseldata: carouseljson, method: "carouselRegen"}),
        success: function(data) {
            carousel.html($(data).html());
            carousel_loadcontrols(carousel);
            
            if($("input[name='carousel']").length > 1) {
                $("input[name='carousel']").last().remove();
            }
        }
    });
}

//Add Slide
function carousel_addslide(carousel) {
    var savelocation = $("input[name='carousel']");
    
    if(carousel.length) {
        carousel_save(carousel, savelocation);
        
        var carouseljson = JSON.parse(savelocation.val());
        var carouselcount = carouseljson.length
        
        carouseljson[carouselcount] = {
            "image": ""
        }
        
        savelocation.val(JSON.stringify(carouseljson));
        carousel_regen(carousel);
        
        $(document).ajaxComplete(function() {         
            carousel.find(".carousel-item.active").removeClass("active");
            
            if(!carousel.find(".carousel-item:not(.additionalSlide)").length) {
                carousel.find(".carousel-item").last().addClass("active");
            }
            else {
                carousel.find(".carousel-item:not(.additionalSlide)").last().addClass("active");
            }
        });
    }
}

$("body").on("click", "button[name='addCarousel']", function() {
    var carousel = $(this).parents(".carousel").first();
    carousel_addslide(carousel);
});

//Delete Slide
function carousel_deleteslide(item) {
    var savelocation = $("input[name='carousel']");
    var carousel = item.parents(".carousel").first();
    
    if(confirm("Are you sure you want to delete this slide?")) {
        item.remove();
        $(".tooltip").remove();
        carousel_save(carousel, savelocation);
        carousel_regen(carousel);
    }
}

$("body").on("click", "button[name='carouselDelete']", function() {
    carousel_deleteslide($(this).parents(".carousel-item").first());
});

//Change Image
function carousel_rf_callback(field_id) {
    var url = $("#" + field_id).val().split(location.protocol + "//" + location.hostname + root_dir)[1];
    
    $("#img" + field_id).attr("src", url);
    $("#" + field_id).remove();
    
    carousel_checkimage($("#img" + field_id).parents(".carousel-item").first());
}

function carousel_selectimage(item) {
    var random = btoa(new Date()).split("=")[0];
    
    item.find("img.background").remove();
    item.prepend("<input type='hidden' id='" + random + "' name='" + random + "'>");
    item.prepend("<img src='' class='background' id='img" + random + "'>");
    
    $.fancybox.open({
        src: "js/responsive_filemanager/filemanager/dialog.php?type=1&field_id=" + random + "&callback=carousel_rf_callback",
        type: "iframe"
    });
}

$("body").on("click", "button[name='carouselImage']", function() {
    carousel_selectimage($(this).parent(".carouselControls").siblings(".carousel-item-inner").first());
});

//Position Text
function carousel_textvertical(item, alignment) {
    if(item.hasClass("carousel-item-inner")) {
        switch(alignment) {
            case "flex-start":
            case "center":
            case "flex-end":
                item.css("justify-content", alignment);
                break;
            case "centre":
            case "middle":
                item.css("justify-content", "center");
                break;
            case "top":
            case "start":
                item.css("justify-content", "flex-start");
                break;
            case "bottom":
            case "end":
                item.css("justify-content", "flex-end");
                break;
            default:
                item.css("justify-content", "");
                break;
        }
    }
}

$(".carousel.builder").on("click", "button[name='carouselTextT'],button[name='carouselTextM'],button[name='carouselTextB']", function() {
    var position = $(this).attr("name").split("carouselText")[1];
    var inner = $(this).parents(".carousel-item").find(".carousel-item-inner").first();
    
    switch(position) {
        case "T":
            carousel_textvertical(inner, "flex-start");
            break;
        case "M":
            carousel_textvertical(inner, "center");
            break;
        case "B":
            carousel_textvertical(inner, "flex-end");
            break;
        default: 
            break;
    }
});

function carousel_textalign(item, alignment) {    
    switch(alignment) {
        case "left":
        case "center":
        case "right":
            item.css("text-align", alignment);
            break;
        case "centre":
        case "middle":
            item.css("text-align", "center");
            break;
        case "start":
            item.css("text-align", "left");
            break;
        case "end":
            item.css("text-align", "right");
            break;
        default:
            item.css("text-align", "");
            break;
    }
}

$(".carousel.builder").on("click", "button[name='carouselTextL'],button[name='carouselTextC'],button[name='carouselTextR']", function() {
    var position = $(this).attr("name").split("carouselText")[1];
    var title = $(this).parents(".carousel-item").find("input[name='carouselTitle']").first();
    var tagline = $(this).parents(".carousel-item").find("input[name='carouselTagline']").first();
    
    switch(position) {
        case "L":
            carousel_textalign(title, "left");
            carousel_textalign(tagline, "left");
            break;
        case "C":
            carousel_textalign(title, "center");
            carousel_textalign(tagline, "center");
            break;
        case "R":
            carousel_textalign(title, "right");
            carousel_textalign(tagline, "right");
            break;
        default: 
            carousel_textalign(title, "");
            carousel_textalign(tagline, "");
            break;
    }
});

//Text Colour
function carousel_textcolor(item, color) {
    var validrgb = "([0-9]|[0-9][0-9]|[0-1][0-9][0-9]|2[0-4][0-9]|25[0-5])";
    var alpha = "([0-1]|0\.[0-9]{1,2})";
    var commas = "(,|,\\s)";
    var pattern = "^rgb\\(";
    
    //Build RGB pattern
    for(var i = 0; i <= 2; i++) {
        pattern += validrgb
        
        if(i < 2) {
            pattern += commas;
        }
    }
    
    pattern += "\\)$";
    
    var rgbReg = new RegExp(pattern, "gi");
    var rgbaReg = new RegExp(pattern.replace(new RegExp("rgb", "gi"), "rgba").replace("\\)$", commas + alpha + "\\)$"), "gi");
    
    if(/^\#([\da-fA-F]{3}){1,2}$/.test(color)) {
        item.css("color", color);
    } //Hex
    else if(rgbReg.test(color)) {
        item.css("color", color);
    } //RGB
    else if(rgbaReg.test(color)) {
        item.css("color", color);
    } //RGBA
    else if(CSS_COLORS.includes(color.toLowerCase())) {
        item.css("color", color.toLowerCase());
    } //Name
    else {
        item.css("color", "");
    } //Invalid
}

var carousel_textcolour = carousel_textcolor;

function componentToHex(c) {
    var hex = c.toString(16);
    return hex.length == 1 ? "0" + hex : hex;
}

function rgbToHex(r, g, b) {
    return "#" + componentToHex(r) + componentToHex(g) + componentToHex(b);
}

$(".carousel.builder").on("click", "button[name='carouselTitleColor'],button[name='carouselTaglineColor']", function() {
    var btn = $(this);
    var type = "";
    var currColor = "";
    
    if($(this).attr("name").split("carousel")[1] == "TitleColor") {
        type = "Title";
        currColor = $(this).parents(".carousel-item").first().find("input[name='carouselTitle']").css("color");
    }
    else if($(this).attr("name").split("carousel")[1] == "TaglineColor") {
        type = "Tagline";
        currColor = $(this).parents(".carousel-item").first().find("input[name='carouselTagline']").css("color");
    }
    
    //Convert RGB / RGBA to Hex
    if(currColor.indexOf("rgb") >= 0 || currColor.indexOf("rgba") >= 0) {
        var colors = currColor.replace(new RegExp(/\s/, "g"), "").match(/[0-9]{0,3}/g);
        var i = 1;
        var r;
        var g;
        var b;
        
        $.each(colors, function(key, color) {
            if(color.length && i <= 3) {
                switch(i) {
                    case 1:
                        r = color;
                        break;
                    case 2:
                        g = color;
                        break;
                    case 3:
                        b = color;
                        break;
                }
                
                i++;
            }
        });
        
        if(r.length && g.length && b.length) {
            currColor = rgbToHex(parseInt(r), parseInt(g), parseInt(b));
        }
    }
    
    if(!$(this).next(".carouselPicker").length) {
        $(this).addClass("hasInput");
        $("<div class='carouselPicker'><input type='color' class='form-control form-control-color p-0' colorformat='rgb' name='carouselPicker" + type + "' data-color='" + currColor + "' value='" + currColor + "'></div>").insertAfter($(this));
    }
    else {
        $(this).removeClass("hasInput");
        $(this).next(".carouselPicker").remove();
    }
});

$(".carousel.builder").on("change", "input[type='color']", function() {
    carousel_textcolor($(this).parents(".carousel-item").first().find("input[name='carousel" + $(this).attr("name").split("carouselPicker")[1] + "']"), $(this).val());
})

//Position Image
function carousel_checkimage(item) {
    var image = item.find("img.background");
    
    if(image.length && image.attr("src").length && !item.find(".carouselControls button[name='carouselImageH']").length && !item.find(".carouselControls button[name='carouselImageV']").length) {
        $(
            "<button type='button' class='btn btn-dark' name='carouselImageH' data-toggle='tooltip' data-placement='bottom' title='Image Horizontal'><span class='fas fa-arrows-alt-h'></span></button>" +
            "<button type='button' class='btn btn-dark' name='carouselImageV' data-toggle='tooltip' data-placement='bottom' title='Image Vertical'><span class='fas fa-arrows-alt-v'></span></button>"
        ).insertBefore(item.find(".carouselControls button[name='carouselDelete']"));
    }
}

function carousel_imageposition(item, horizontal, vertical) {
    var objectPosition = item.css("object-position");
    var currentHorz = objectPosition.split(" ")[0].trim();
    var currentVert = objectPosition.split(" ")[1].trim();
    var newPosition = "";
    
    if(horizontal == "" && vertical == "") {
        item.css("object-position", "");
    }
    else {
        if(!/^(\-?)[\d]+(px|pt|em|rem|\%){1}$/.test(horizontal)) {
            horizontal = currentHorz;
        }

        if(!/^(\-?)[\d]+(px|pt|em|rem|\%){1}$/.test(vertical)) {
            vertical = currentVert;
        }
        
        newPosition = horizontal + " " + vertical;
        item.css("object-position", newPosition);
    }
}

$(".carousel.builder").on("click", "button[name='carouselImageH'],button[name='carouselImageV']", function() {
    var objectPosition = $(this).parents(".carousel-item").first().find("img.background").css("object-position");
    var imageValue = "";
    var type = $(this).attr("name").split("carouselImage")[1];
    
    
    if(type == "H") {
        imageValue = objectPosition.split(" ")[0];
        type = "Horizontal";
    }
    else if(type == "V") {
        imageValue = objectPosition.split(" ")[1];
        type = "Vertical";
    }
    
    if(!$(this).next(".carouselInput").length) {
        $(this).addClass("hasInput");
        $("<div class='carouselInput'><input type='text' class='form-control py-0 px-1' name='carouselImage" + type + "' placeholder='px, pt, em, rem, %' value='" + imageValue + "'></div>").insertAfter($(this));
    }
    else {
        $(this).removeClass("hasInput");
        $(this).next(".carouselInput").remove();
    }
});

$(".carousel.builder").on("keyup", "input[name='carouselImageHorizontal'],input[name='carouselImageVertical']", function() {
    var horz = "";
    var vert = "";
    var controls = $(this).parents(".carouselControls").first();
    
    if($(this).attr("name").split("carouselImage")[1] == "Horizontal") {
        horz = $(this).val();
        vert = (controls.find("input[name='carouselImageVertical']").length ? controls.find("input[name='carouselImageVertical']").val() : "");
    }
    else if($(this).attr("name").split("carouselImage")[1] == "Vertical") {
        horz = (controls.find("input[name='carouselImageHorizontal']").length ? controls.find("input[name='carouselImageHorizontal']").val() : "");
        vert = $(this).val();
    }
    
    horz = (horz.length ? horz : "50%");
    vert = (vert.length ? vert : "50%");
    
    carousel_imageposition($(this).parents(".carousel-item").first().find("img.background"), horz, vert);
});

$(document).keydown(function(e) {
    if($(".carousel.builder input[name='carouselImageHorizontal'],.carousel.builder input[name='carouselImageVertical']").is(":focus")) {
        var input = $(":focus");
        
        if(/^(\-?)[\d]+(px|pt|em|rem|\%){1}$/.test(input.val())) {
            var inputnumber = parseInt(input.val());
            var inputsuffix = input.val().split(inputnumber)[1];
            
            switch(e.which) {
                case 38:
                    inputnumber += 1;
                    input.val(inputnumber + inputsuffix);
                    break;
                case 40:
                    inputnumber -= 1;
                    input.val(inputnumber + inputsuffix);
                    break;
            }
        }
    }
});