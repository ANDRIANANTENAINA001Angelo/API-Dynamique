<?php
require_once("./config/database.php");


function getConfig(){
    $config=[];
    $valide=true;

    $header= apache_request_headers();


    // header
        // db host
    if(isset($header['Dbhost'])){
        $config['Dbhost']=$header["Dbhost"];
    }
    else{
        $config['Dbhost']='localhost';
    }

        // user_name
    if((isset($header['Username']) && !empty($header['Username']))){
        $config['Username']=$header["Username"];
    }
    else{
        $config['Username']='root';
    }

        // password
    if(isset($header['Password']) ){
        $config['Password']=$header["Password"];
    }
    else{
        $config['Password']="";
    }


    
    //port
    if(isset($header['Port']) && !empty($header['Port'])){
        $config['Port']=$header['Port'];
    }
    else{
        $config['Port']="80";
    }

    if($valide){
        return $config;
    }
    else{
        return $valide;
    }

}


?>