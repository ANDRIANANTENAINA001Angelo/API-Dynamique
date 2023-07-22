<?php
require_once("./config/connection.php");
require_once('./config/reponse.php');

function getIdKey($connection,string $table){
    $idKey=0;
    
    $sql= "SHOW INDEX IN ". $table ." WHERE 1;";

    try {
    $result= $connection->prepare($sql);
    $result->execute();
    

    $ligne= $result->fetch(PDO::FETCH_BOTH);

    $idKey= $ligne["Column_name"];

    return $idKey;
    } catch (Exception $e) {
        echo "Erreur 500 ". $e->getMessage();

        return false;
    }
}

function getAllDb($connection){
    $message='';
    $status=0;
    $data=[];

    $sql= "SHOW DATABASES;";
    try {
        $req= $connection->prepare($sql);
        $req->execute();
        while($ligne= $req->fetch(PDO::FETCH_COLUMN)){

            //enlever les tables qu'il ne faut pas utilisé
            //les tables reservé: information_schema,mysql,performance_schema,phpmyadmin,sys            
            if($ligne!="information_schema" && $ligne!="mysql" && $ligne!="performance_schema" && $ligne!= "phpmyadmin" && $ligne!="sys"){
                array_push($data,$ligne);
            }
        }


    

        $status=200;
        $message="La liste de tous les bases de données a été bien retourné.";


    } catch (Exception $e) {
        $status= 500;
        $message= "Erreur lors de l'affichage de tous les bases de données.";
        $data=$e;
    }

    return resultat($status,$message,$data);
}

function getAllTablesInDb($connection, string $db){

    $status=0;
    $message= '';
    $data=[];

    $sql= "SHOW TABLES FROM ".$db.";";

    try {
        $req= $connection->prepare($sql);
        $req->execute();
        $count=0;
        while($ligne= $req->fetch(PDO::FETCH_COLUMN)){
            array_push($data,$ligne);
            $count++;
        }

        if($count==0){
            $message="Aucun table trouvé dans la base de données ".$db.".";
            $status=404;
        }else{
            $message="La liste de tous les tables dans ".$db." a été bien retourné.";
            $status=200;
        }


    
    } catch (Exception $e) {
        $status= 500;
        $message="Erreur lors de l'affichage de tous les tables dans la base de données ". $db.".";
        $data=$e;
    }
    
    return resultat($status,$message,$data);  
}

function getIp(){
    return $_SERVER['REMOTE_ADDR']; 
}

function isDbExist(string $db,array $allDB){
    
    foreach($allDB as $v){
        if($v==$db){
            return true;
        }
    }
    return false;
}

function isTableExist(string $table,array $allTable){
    foreach($allTable as $v){
        if($v==$table){
            return true;
        }
    }
    return false;
}

?>