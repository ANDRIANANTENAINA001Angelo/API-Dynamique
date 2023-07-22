<?php
require_once("./config/database.php");



function getConfig(){
    $config=[];

    $header= apache_request_headers();

    // header
    // if(!isset($header['db_host']) || (isset($header['db_host']) && $header['db_host']=="localhost")){
    //     $config['db_host']=getIp();
    // }
    // else{
    //     $config['db_host']=$header['db_host'];
    // }
    $config['db_host']=getIp();

    // $config["db_host"]=getIp();
    //port
    // if(isset($header['port'])){
    //     $config['port']=$header['port'];
    // }
    // else{
    //     $config['port']="80";
    // }
    if(isset($header['user_name']) && !empty($header["user_name"] && isset($header["password"]))){
        $config['user_name']= $header['user_name'];
        $config['password']= $header['password'];
        return $config;        
    }
    else{
        return false;
    }

}


?>