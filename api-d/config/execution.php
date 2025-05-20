<?php
require_once('connection.php');
require_once('reponse.php');

function readIn($connection,string $table,array $fields=null,array $condition=null,bool $distinct,string $orderBy=null){
    //$status,$message,$data pour la reponse
    $status='';
    $message="";
    $data = [];
    
    //preparation requette
    $reqSelect="SELECT ";
    $reqField="";
    $reqCondition="WHERE ";
    $reqOrder="";


    if($fields==null || count($fields)==0){$reqField=" * ";}
    else{
        
        foreach ($fields as $v) { 
            $reqField.=$v;
            $reqField.=",";
        }
        $reqField=substr($reqField,0,-1);

    }

    if($condition==null || count($condition)==0){
        $reqCondition.="1";
    }
    else{
        foreach($condition as $k=>$v)
        {
            $reqCondition.=
            $k."='".
            $v."' AND ";
        }
        $reqCondition=substr($reqCondition,0,-5);
    }
    if($distinct){
        $reqSelect.= "DISTINCT ";
    }

    if($orderBy!=''){
        $reqOrder="ORDER BY ". $orderBy ." ASC";
        $sql=$reqSelect.$reqField." FROM ".$table." ".$reqCondition." ". $reqOrder .";";    
    }
    else{
        $sql=$reqSelect.$reqField." FROM ".$table." ".$reqCondition.";";
    }
    try {
// echo $sql;

        //requette affiche
        $req = $connection->prepare($sql);
        $req->execute();
        
        while($ligne= $req->fetch(PDO::FETCH_ASSOC)){
            array_push($data,$ligne);
        }

        if(count($data)==0){
            $status= 404;
            $message= "Aucun resultat trouve.";
        }
        else{
            $status=200;
            $message= "Affichage des ". count($data) ." donnees dans ". $table. " reussie.";
        }

    } catch (Exception $e) {
        $message= "Erreur affichage des donnees dans ". $table .": ". $e->getMessage();
        $status = 500;
    }

    return resultat($status,$message,$data);
}

function updateIn($connection,string $table, array $fields=null, array $condition=null)
{
    //$status,$message,$data pour la reponse
    $status='';
    $message="";
    $data = [];

    //preparation requette
    $reqField="";
    $reqCondition="WHERE ";
    if($fields==null || count($fields)==0)
    {
        $message= "Erreur de requette: champs à modifier vide.";
        $status = 400;
    }
    else{
        foreach($fields as $k=>$v)
        {
            $reqField.=$k."='".$v."',";
        }
            $reqField = substr($reqField,0,-1);

        if($condition==null || count($condition)==0){
            $message= "Erreur de requette: condition non specifie vide.";
            $status = 400;
        }
        else{
            foreach($condition as $k=>$v)
            {
                $reqCondition.=$k."='".$v."' AND ";
            }
            $reqCondition=substr($reqCondition,0,-5);
        }

        $sql= "UPDATE ".$table." SET ". $reqField ." ". $reqCondition.";";
       
        try {
            $connection->exec($sql);
            $message="Modification reussit.";
            $status=200;
        
        } catch (Exception $e) {
            $message= "Echec Modification element dans ". $table .": ". $e->getMessage();
            $status= 500;
        }

        return resultat($status,$message,$data);
    }
}

function deleteIn($connection,string $table,array $condition){
   //$status,$message,$data pour la reponse
   $status='';
   $message="";
   $data = [];

   //preparation de la requette
    if(count($condition)==0){
        $message= "Erreur de la requette: la condition de suppresion n'est pas specifie.";
        $status= 400;
    }
    else{
        $reqCondition="WHERE ";
        foreach($condition as $k=>$v)
        {
            $reqCondition.=$k."='".$v."' AND ";
        }
        $reqCondition=substr($reqCondition,0,-5);
    
        $sql= "DELETE FROM ".$table." ".$reqCondition.";";
    
        try {
            $connection->exec($sql);
            $message="Suppression element dans ". $table ." reussit.";
            $status= 200;
        } catch (Exception $e) {
            $message= "Echec suppression element dans ". $table .": ".$e->getMessage();
            $status= 500;
        }
    
        return resultat($status,$message,$data);
    }
}

function createIn($connection,string $table, array $values=null)
{
    //$status,$message,$data pour la reponse
   $status='';
   $message="";
   $data = [];

    $reqKey="(";
    $reqValue="(";

    foreach($values as $k=>$v){
        $reqKey.=$k.",";
        $reqValue.="'". $v ."',";
    }
    $reqKey= substr($reqKey,0,-1);
    $reqKey.=")";
    $reqValue = substr($reqValue,0,-1);
    $reqValue.=")";

    $sql= "INSERT INTO ".$table." ".$reqKey." VALUES ". $reqValue .";";

    try {
        $connection->exec($sql);
        $message="Insertion nouvelle element dans ". $table ." reussit.";
        $status= 200;

        $id_last=$connection->lastInsertId();
        $data['id']=$id_last;

    } catch (Exception $e) {
        $message= "Echec insertion du nouvelle element dans ". $table .": ".$e->getMessage();
        $status= 500;
    }

    return resultat($status,$message,$data);
}

?>