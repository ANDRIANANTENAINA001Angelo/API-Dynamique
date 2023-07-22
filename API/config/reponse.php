<?php

function resultat(string $status,string $message, array $data)
{
    if(count($data) > 0)
    {
        $response= [
            'status'=>$status,
            'message'=>$message,
            'data'=>$data
        ];    
    }
    else{
        $response= [
            'status'=>$status,
            'message'=>$message
        ];
    }

    return $response;
}

function toJson(array $resultat){
    array_shift($resultat);
    if(array_key_exists("data",$resultat) && empty($resultat["data"])){
        array_pop($resultat);
    }

    return json_encode($resultat);
}

?>