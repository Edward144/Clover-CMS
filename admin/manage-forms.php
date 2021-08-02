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
    function formbuilder_addoption_select() {
        
    }
    
    //Add option to radio
    function formbuilder_addoption_radio() {
        
    }
    
    //Delete item
    function formbuilder_deleteitem(button) {
        var type = button.val().split("× ")[1].toLowerCase();
        
        if(confirm("Are you sure you want to delete this " + type + "?")) {
            button.parents(".list-group-item").first().remove();
            formbuilder_disablesubmit();
        }
    }
    
    $(".formbuilder").on("click", "input[name='deleteGroup'], input[name='deleteInput'], input[name='deleteOption']", function() {
        formbuilder_deleteitem($(this));
    });
    
    //Prevent submit inputs from being added if one already exists
    function formbuilder_disablesubmit() {
        if($(".formbuilder").find("input[name='inputtype'][value='submit']").length) {
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