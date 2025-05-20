<?php

function setHeaderResponse($status){
    
    $message="";
    switch ($status) {
        case 200:
            $message="OK";
            break;
        
        case 400:
            $message="ERROR REQUEST";
            break;    
        
        case 500:
            $message="ERROR FROM SERVER";
            break;
        case 404:
            $message= "NOT FOUND";
            break;
        
        default:
            $message="OK";
            break;
    }

    $header="HTTP/1.0 ".$status." ".$message;
    header($header);
}



?>