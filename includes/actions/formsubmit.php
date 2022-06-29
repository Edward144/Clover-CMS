<?php

    require_once(dirname(__DIR__) . '/database.php');
    require_once(dirname(__DIR__) . '/functions.php');

    //Get form details
    $form = $mysqli->prepare("SELECT * FROM `forms` WHERE id = ?");
    $form->bind_param('i', $_POST['formid']);
    $form->execute();
    $formResult = $form->get_result();
    
    if($formResult->num_rows > 0) {
        $form = $formResult->fetch_assoc();
    }
    else {
        $form = [];
        $form['name'] = 'a form';
    }

    //Get our captcha keys
    $captcha = $mysqli->query("SELECT name, value FROM `settings` WHERE name = 'recaptcha_sitekey_v3' OR name = 'recaptcha_secretkey_v3recaptcha_secretkey_v2' OR name = 'email'");
                        
    if($captcha->num_rows > 0) {
        $cptch = [];

        while($row = $captcha->fetch_assoc()) {
            $cptch[$row['name']] = $row['value'];
        }

        $sitekey = $cptch['recaptcha_sitekey_v3'];
        $secretkey = $cptch['recaptcha_secretkey_v3'];

        //Validate the captcha response
        $allowsend = false;

        if(isset($_POST['g-recaptcha-response'])) {
            $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secretkey . '&response=' . $_POST['g-recaptcha-response'] . '&remoteid=' . $_SERVER['REMOTE_ADDR']);
            $response = json_decode($response);

            if($response->success == true && $response->score >= 0.5) {
                $allowsend = true;
            }
        }
        elseif(empty($sitekey) || empty($secretkey)) {
            $allowsend = true;
        }
        
        if($allowsend == true) {
            $to = $cptch['email'];
            $subject = 'New message from ' . $form['name'] . ' | ' . $_SERVER['SERVER_NAME'];
            $content =
                '<p>You have received a new message from ' . $_SERVER['SERVER_NAME'] . '</p>';

            //Loop posted data and combine hidden labels with values
            $postItems = [];

            foreach($_POST as $index => $postItem) {
                if(strpos($index, 'label') === 0) {
                    $postItems[$index] = [
                        'label' => $postItem
                    ];
                }
                elseif(array_key_exists('label' . $index, $postItems)) {
                    $postItems['label' . $index]['value'] = $postItem;
                }
            }

            if(count($postItems) > 0) {
                $content .= 
                    '<ul>';

                foreach($postItems as $postItem) {
                    $content .=
                        '<li><strong>' . $postItem['label'] . '</strong> ' . $postItem['value'] . '</li>';
                }

                $content .=
                    '</ul>';
            }
            
            $content .=
                '<p><strong>Recaptcha Score</strong> ' . $response->score . '</p>';
            
            if(!sendemail($to, $subject, $content)) {
                $status = 'danger';
                $message = 'Your message failed to send please try again later';
            }
            else {
                $status = 'success';
                $message = 'Your message has been sent successfully';
            }
        }
        else {
            $status = 'warning';
            $message = 'Captcha failed, please try again';
        }
    }

    if(isset($status) && isset($message)) {
        $_SESSIOn['status' . $_POST['formid']] = $status;
        $_SESSIOn['message' . $_POST['formid']] = $message;
    }

    header('Location: ' . $_POST['returnurl']);
    
?>