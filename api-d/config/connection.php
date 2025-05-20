<?php

function startConnection(string $host,string $user_name, string $password,string $dbName=null){
    $port = '3306';
    try{
        //connection bdd
        if($dbName!=null){
            // echo "mysql:host=".$host.";dbname=".$dbName.";charset=utf8", "".$user_name."", "".$password."";
            $connection = new PDO("mysql:host=".$host.";port=". $port .";dbname=".$dbName.";charset=utf8", "".$user_name."", "".$password."");
        }
        else{
            // echo "mysql:host=".$host.";charset=utf8,".$user_name.",".$password."\n";
            $connection = new PDO("mysql:host=".$host.";port=". $port .";charset=utf8", "".$user_name."", "".$password."");

        }

        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $connection;
    }
    catch(Exception $e)
    {
        echo $e->getMessage();
        exit(-200);
    }
}

function closeConnection($connection){
    $connection= null;
}

?>