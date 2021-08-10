<?php 
	$title = 'Manage Forms';
	require_once(dirname(__FILE__) . '/includes/header.php'); 
?>

<div class="col-lg-3 bg-light py-3">	

</div>

<div class="col py-3">
	<?php $formbuilder = new formbuilder(1); echo $formbuilder->display(); ?>
</div>

<script>
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
            data: ({data: JSON.stringify(groupData), method: "addGroup"}),
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
            data: ({data: JSON.stringify(inputData), method: "addInput"}),
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
            data: ({method: "addOptionSelect"}),
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
            data: ({inputId, isDefault, method: "addOptionRadio"}),
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
</script>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>