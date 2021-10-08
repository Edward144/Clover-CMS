<?php

    if(isset($_POST['recaptchaverify'])) {        
        $valid = true;
        $responses = json_decode($_POST['responses'], true);
        
        foreach($responses as $response) {
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'secret' => '6LfwNLkcAAAAALzqGeeDjfV-8BPgsfmjOJ0CLI_G',
                'response' => $response
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            
            $result = json_decode(curl_exec($ch), true);
            
            if($result['success'] == false) {
                var_dump($result); exit();
                $valid = false;
                break;
            }
            
            curl_close($ch);
        }
        
        echo json_encode($valid);
        
        exit();
    }

?>

<!DOCTYPE html>
<html lang="">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title></title>
        <link rel="stylesheet" href="">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    </head>

    <body>        
        <form id="test">
            <div id="recaptcha1" class="recaptcha"></div>
            <br>
            <div id="recaptcha2" class="recaptcha"></div>
            <br>
            <input type="submit">
        </form>
        
        <script>
            var recaptchaWidgets = [];
            var recaptchaSitekey = "6LfwNLkcAAAAAL_SoG3kEZmWyVw3Gee9gQVQhugF";
            
            var recaptchaOnload = function() {
                $(".recaptcha").each(function() {
                    var recaptchaId = $(this).attr("id");
                    
                    if(!$(this).html().length) {
                        recaptchaWidgets[recaptchaId.split("recaptcha")[1]] = grecaptcha.render(recaptchaId, {
                            "sitekey": recaptchaSitekey
                        });
                    }
                });
            }
            
            $("#test").submit(function() {
                event.preventDefault();
                
                var form = $(this);
                var valid = false;
                var recaptchas = $(this).find(".recaptcha");
                var responses = [];
                
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
                        if(data[0] == false) {
                            $.each(recaptchas, function() {
                                grecaptcha.reset(recaptchaWidgets[$(this).attr("id").split("recaptcha")[1]]);
                            });
                            
                            event.preventDefault();
                            return false;
                        }
                        else {
                            form.unbind("submit").submit();
                        }
                    }
                });
            });
        </script>
        
        <script src="https://www.google.com/recaptcha/api.js?onload=recaptchaOnload&render=explicit" async defer></script>
    </body>
</html>