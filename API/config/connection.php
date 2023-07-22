<?php

function startConnection(string $host,string $user_name, string $password,string $dbName=null){
    try{
    //connection bdd
    if($dbName!=null){
        $connection = new PDO("mysql:host=".$host.";dbname=".$dbName.";charset=utf8", "".$user_name."", "".$password."");
    }
    else{
        $connection = new PDO("mysql:host=".$host.";charset=utf8", "".$user_name."", "".$password."");
    }
    
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    return $connection;
    }
    catch(Exception $e)
    {
        echo $e->getMessage();
    }
}

function closeConnection($connection){
    $connection= null;
}

?>