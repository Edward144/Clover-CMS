<?php

    if(isset($_POST['recaptchaverify'])) {        
        $valid = true;
        $message = '';
        $status = 'success';
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
                $valid = false;
                $status = 'danger';
                
                switch($result['error-codes'][0]) {
                    case 'missing-input-response':
                        $message = 'Recaptcha has not been completed';
                        break;
                    case 'invalid-input-response':
                        $message = 'Recaptcha is invalid';
                        break;
                    case 'bad-request':
                        $message = 'Request is invalid';
                        break;
                    case 'timeout-or-duplicate':
                        $message = 'Recaptcha has expired';
                        break;
                    case 'missing-input-secret':
                    case 'invalid-input-secret':
                    default:
                        $message = 'A server side error has occurred';
                        break;
                }
                
                break;
            }
            
            curl_close($ch);
        }
        
        echo json_encode(['valid' => $valid, 'message' => $message, 'status' => $status]);
        
        exit();
    }

?>