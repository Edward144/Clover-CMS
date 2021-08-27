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
	var url = $("#" + field_id).val();
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
$("input[name='deleteContent']").click(function() {
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
    
    if(analytics.val().length && !/^[A-Z]{2}\-[0-9]{8}\-[0-9]$/.test(analytics.val())) {
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

$(document).ready(function() {
    formbuilder_disablesubmit();
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

////Carousel

//Show Controls
function carousel_loadcontrols(carousel) {
    carousel.find(".carousel-item:not(.additionalSlide)").each(function() {
        if(!$(this).find(".carouselControls").length) {
            $(
                "<div class='carouselControls'>" +
                    "<button type='button' class='btn btn-dark' name='carouselImage' data-toggle='tooltip' data-placement='bottom' title='Choose Image'><span class='fas fa-image'></span></button>" +
                    "<button type='button' class='btn btn-dark' name='carouselDelete' data-toggle='tooltip' data-placement='bottom' title='Delete Slide'><span class='fas fa-trash'></span></button>" +
                "</div>"
            ).prependTo($(this));
            
            $("[data-toggle='tooltip']").tooltip();
        }
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
            
            json[i] = {
                "image": image,
                "title": title,
                "tagline": tagline,
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
    var url = $("#" + field_id).val();
    $("#img" + field_id).attr("src", url);
    $("#" + field_id).remove();
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

//Text Colour

//Position Image