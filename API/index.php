<?php
require_once("./config/url.php");
require_once("./config/header.php");
require_once("./config/execution.php");
require_once("./config/database.php");
require_once("./config/inputData.php");
require_once("./config/config.php");
require_once("./config/secure.php");

header("Access-Control-Allow-Origin:*");
header('Content-Type: application/json; charset= UTF-8');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-With');
header('Access-Control-Allow-Methods: GET,HEAD,POST,PUT,DELETE,OPTIONS');
header('Access-Control-Allow-Credentials: true');

$resultat=[];

$config= getConfig();

$config= toSecure($config);

$url= urlParse();

$url = toSecure($url);

if($config==false)
{
    $resultat=[
        'status'=>400,
        'message'=>"Requette non valide, le champ 'user_name' ou 'password' dans le header du requête est obligatoire.",
        'data'=>[]
    ];
}
elseif($url['exist'])
{

$methode= getMethode();

$inputData= json_decode(file_get_contents("php://input"),true);
if($inputData!=null){
    $inputData= toSecure($inputData);
}

$connection = startConnection($config['db_host'],$config['user_name'],$config['password']);

$allDb= getAllDb($connection);
$allDb= $allDb['data'];

if($url['db']!=null){
    $dbExist= isDbExist($url['db'],$allDb);
}else{
    $dbExist= false;
}

if($dbExist){
    $connection = startConnection($config['db_host'],$config['user_name'],$config['password'],$url['db']);    
}

if($dbExist && $url["table"]!=null){
    $allTable= getAllTablesInDb($connection,$url['db']);
    if(isset($allTable["data"])){
        $allTable= $allTable['data'];
    }
    else{
        $allTable=[];
    }
    
    $tableExist= isTableExist($url['table'],$allTable);
}
else{
    $tableExist=false;
}

switch ($methode) {
        case 'GET':
            if($dbExist && $tableExist){
                //champs voulue
                if(isExistFields()){
                    $fields= getFields();
                }
                else{
                    $fields=[];
                }
        
                //condition requette
                if(isExistCondition()){
                    $condition= getCondition();
                }
                else{
                    $condition=[];
                }
        
                if($url['id']!=null){
                    $condition[getIdKey($connection,$url['table'])]=$url['id'];
        
                    $resultat = readIn($connection,$url['table'],$fields,$condition,isExistDistinct());
                }
                else{
                    $resultat = readIn($connection,$url['table'],$fields,$condition,isExistDistinct());
                }
    
            }
            elseif($dbExist && $url['table']==null){
                $resultat= getAllTablesInDb($connection,$url['db']);
            }
            elseif($url['db']==null){
                $resultat= getAllDb($connection);
            }
            elseif(!$dbExist){
                $resultat["status"]=404;
                $resultat["message"]="La base de données ".$url['db']." n'existe pas dans votre base de données.";               
            }
            elseif($dbExist && !$tableExist){
                $resultat["status"]=404;
                $resultat["message"]="La table ".$url['table']." n'existe pas dans la base de données ".$url['db'].".";               
            }
    
            break;
            
        case 'POST':
            if($url['table']==null || $url["db"]==null){
                // methode ou url non valide
                $resultat=[
                    'status'=>400,
                    'message'=>"La methode ou l'url n'est pas valide. Base de données ou table non spécifié.",
                    'data'=>[]
                ];    
            }
            elseif(!$dbExist){
                $resultat["status"]=404;
                $resultat["message"]="La base de données ".$url['db']." n'existe pas dans votre base de données.";               
            }
            elseif($dbExist && !$tableExist){
                $resultat["status"]=404;
                $resultat["message"]="La table ".$url['table']." n'existe pas dans la base de données ".$url['db'].".";               
            }
            else{
                if(empty($inputData)){
                    $resultat["status"]=400;
                    $resultat["message"]="Erreur requette: Aucun donné reçu pour l'ajout d'une nouvelle element dans ". $url['table'];
                }
                else{
                    if(isAllNotEmpty($inputData)){
                        $resultat= createIn($connection,$url['table'],$inputData);
                    }
                    else{
                        $resultat["status"]=400;
                        $resultat["message"]="Erreur requette: Une des données est vide";                
                    }
                }
            }
            break;
        
        case 'PUT':
            if($url['table']==null || $url["db"]==null){
                // methode ou url non valide
                $resultat=[
                    'status'=>400,
                    'message'=>"La methode ou l'url n'est pas valide. Base de données ou table non spécifié.",
                    'data'=>[]
                ];    
            }
            elseif(!$dbExist){
                $resultat["status"]=404;
                $resultat["message"]="La base de données ".$url['db']." n'existe pas dans votre base de données.";               
            }
            elseif($dbExist && !$tableExist){
                $resultat["status"]=404;
                $resultat["message"]="La table ".$url['table']." n'existe pas dans la base de données ".$url['db'].".";               
            }
            else{
                //condition requette
                if(isExistCondition()){
                    $condition= getCondition();
                }
                else{
                    $condition=[];
                }
                
                if(!idExist()){
                    $resultat["status"]=400;
                    $resultat["message"]="Erreur requette: L'identifiant pour la modification d'une element dans ". $url['table'] ." n'est pas spécifié.";
                }
                elseif(empty($inputData))
                {
                    $resultat["status"]=400;
                    $resultat["message"]="Erreur requette: Aucun donné reçu pour la modification d'une element dans ". $url['table'];
                }
                else{
                    if(isAllNotEmpty($inputData)){
                        $condition[getIdKey($connection,$url['table'])]=getIdValue();
                        $resultat= updateIn($connection,$url['table'],$inputData,$condition);
                    }
                    else{
                        $resultat["status"]=400;
                        $resultat["message"]="Erreur requette: Une des données est vide";                
                    }
                }
            }
            break;
        
        case 'PATCH':
            if($url['table']==null || $url["db"]==null){
                // methode ou url non valide
                $resultat=[
                    'status'=>400,
                    'message'=>"La methode ou l'url n'est pas valide. Base de données ou table non spécifié.",
                    'data'=>[]
                ];    
            }
            elseif(!$dbExist){
                $resultat["status"]=404;
                $resultat["message"]="La base de données ".$url['db']." n'existe pas dans votre base de données.";               
            }
            elseif($dbExist && !$tableExist){
                $resultat["status"]=404;
                $resultat["message"]="La table ".$url['table']." n'existe pas dans la base de données ".$url['db'].".";               
            }
            else{
                //condition requette
                if(isExistCondition()){
                     $condition= getCondition();
                }
                else{
                    $condition=[];
                }
                if(!idExist()){
                    $resultat["status"]=400;
                    $resultat["message"]="Erreur requette: L'identifiant pour la modification d'une element dans ". $url['table'] ." n'est pas spécifié.";
                }
                elseif(empty($inputData))
                {
                    $resultat["status"]=400;
                    $resultat["message"]="Erreur requette: Aucun donné reçu pour la modification d'une element dans ". $url['table'];
                }
                else{
                    if(isAllNotEmpty($inputData)){
                        $condition[getIdKey($connection,$url['table'])]=getIdValue();
                        $resultat= updateIn($connection,$url['table'],$inputData,$condition);
                    }
                    else{
                        $resultat["status"]=400;
                        $resultat["message"]="Erreur requette: Une des données est vide";                
                    }
                }
            }
            break;
        
        case 'DELETE':
            if($url['table']==null || $url["db"]==null){
                // methode ou url non valide
                $resultat=[
                    'status'=>400,
                    'message'=>"La methode ou l'url n'est pas valide. Base de données ou table non spécifié.",
                    'data'=>[]
                ];    
            }
            elseif(!$dbExist){
                $resultat["status"]=404;
                $resultat["message"]="La base de données ".$url['db']." n'existe pas dans votre base de données.";               
            }
            elseif($dbExist && !$tableExist){
                $resultat["status"]=404;
                $resultat["message"]="La table ".$url['table']." n'existe pas dans la base de données ".$url['db'].".";               
            }
            elseif($url['db']!=null && !$dbExist){
                $resultat["status"]=404;
                $resultat["message"]="La base de données ".$url['db']." n'existe pas dans votre base de données.";               
            }
            else{
                //condition requette
                if(isExistCondition()){
                    $condition= getCondition();
                 }
                else{
                    $condition=[];
                }
                if(!idExist()){
                    $resultat["status"]=400;
                    $resultat["message"]="Erreur requette: L'identifiant pour la suppression d'une element dans ". $url['table'] ." n'est pas spécifié.";
                }
                else{
                    $condition[getIdKey($connection,$url['table'])]=getIdValue();
                    $resultat= deleteIn($connection,$url['table'],$condition);
                }
            }
            break;
    
        case 'HEAD':
            $resultat=[
                'status'=>200,
                'message'=>"La requette peut se poursuivre.",
                'data'=>[]
            ];
            break;
        
        case 'OPTIONS':
            $resultat=[
                'status'=>200,
                'message'=>"La requette peut se poursuivre.",
                'data'=>[]
            ];
            break;
    
        default:
            $resultat=[
                'status'=>404,
                'message'=>"Le requette demandé ou le protocole n'est pas prise en charge non prise en charge.",
                'data'=>[]
            ];
            break;
        }
        closeConnection($connection);

}
else{
    $resultat=[
        'status'=>400,
        'message'=>"L'url demandé n'est pas valide.",
        'data'=>[]
    ];
}


setHeaderResponse($resultat['status']);
echo toJson($resultat);

?>