<?php

require_once("./config/url.php");
require_once("./config/header.php");
require_once("./config/execution.php");
require_once("./config/database.php");
require_once("./config/inputData.php");
require_once("./config/config.php");
require_once("./config/secure.php");

header("Access-Control-Allow-Origin:*");
header('Content-Type: application/json; charser= UTF-8');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Request-With');
header('Access-Control-Allow-Methods: GET,HEAD,POST,PUT,DELETE,OPTIONS');
header('Access-Control-Allow-Credentials: true');

$resultat=[];

$config= getConfig();

// echo json_encode(apache_request_headers());
echo json_encode(getallheaders());

// echo("config après getConfig\n");
// var_dump($config);
// echo("\n");
if(isset($_GET['url'])){
    $url= urlParse();
    $url = toSecure($url);
}

if(!isset($_GET['url'])){
    $resultat=[
        'status'=>400,
        'message'=>"Requette non valide; Url non specifie ou non valide",
        'data'=>[]
    ];
}
elseif($config==false)
{
    $resultat=[
        'status'=>400,
        'message'=>"Requette non valide, le champ 'Username' et 'Password' dans le header est obligatoire.",
        'data'=>[]
    ];
}

if($url['exist'])
{
    $config= toSecure($config);

    $methode= getMethode();

    $inputData= json_decode(file_get_contents("php://input"),true);
    if($inputData!=null){
        $inputData= toSecure($inputData);
    }

    $connection = startConnection($config['Dbhost'],$config['Username'],$config['Password']);

    $allDb= getAllDb($connection);
    $allDb= $allDb['data'];

    if($url['db']!=null){
        $dbExist= isDbExist($url['db'],$allDb);
    }else{
        $dbExist= false;
    }

    if($dbExist){
        $connection = startConnection($config['Dbhost'],$config['Username'],$config['Password'],$url['db']);    
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
                $resultat["message"]="La base de donnees ".$url['db']." n'existe pas dans votre base de donnees.";               
            }
            elseif($dbExist && !$tableExist){
                $resultat["status"]=404;
                $resultat["message"]="La table ".$url['table']." n'existe pas dans la base de donnees ".$url['db'].".";               
            }

            setHeaderResponse($resultat['status']);
            echo toJson($resultat);
            break;
                
        case 'POST':
            if($url['table']==null || $url["db"]==null){
                // methode ou url non valide
                $resultat=[
                    'status'=>400,
                    'message'=>"La methode ou l'url n'est pas valide. Base de donnees ou table non specifie.",
                    'data'=>[]
                ];    
            }
            elseif(!$dbExist){
                $resultat["status"]=404;
                $resultat["message"]="La base de donnees ".$url['db']." n'existe pas dans votre base de donnees.";               
            }
            elseif($dbExist && !$tableExist){
                $resultat["status"]=404;
                $resultat["message"]="La table ".$url['table']." n'existe pas dans la base de donnees ".$url['db'].".";               
            }
            else{
                if(empty($inputData)){
                    $resultat["status"]=400;
                    $resultat["message"]="Erreur requette: Aucun donne reçu pour l'ajout d'une nouvelle element dans ". $url['table'];
                }
                else{
                    if(isAllNotEmpty($inputData)){
                        $resultat= createIn($connection,$url['table'],$inputData);
                    }
                    else{
                        $resultat["status"]=400;
                        $resultat["message"]="Erreur requette: Une des donnees est vide";                
                    }
                }
            }
            
            setHeaderResponse($resultat['status']);
            echo toJson($resultat);
            break;
        
        case 'PUT':
            if($url['table']==null || $url["db"]==null){
                // methode ou url non valide
                $resultat=[
                    'status'=>400,
                    'message'=>"La methode ou l'url n'est pas valide. Base de donnees ou table non specifie.",
                    'data'=>[]
                ];    
            }
            elseif(!$dbExist){
                $resultat["status"]=404;
                $resultat["message"]="La base de donnees ".$url['db']." n'existe pas dans votre base de donnees.";               
            }
            elseif($dbExist && !$tableExist){
                $resultat["status"]=404;
                $resultat["message"]="La table ".$url['table']." n'existe pas dans la base de donnees ".$url['db'].".";               
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
                    $resultat["message"]="Erreur requette: L'identifiant pour la modification d'une element dans ". $url['table'] ." n'est pas specifie.";
                }
                elseif(empty($inputData))
                {
                    $resultat["status"]=400;
                    $resultat["message"]="Erreur requette: Aucun donne reçu pour la modification d'une element dans ". $url['table'];
                }
                else{
                    if(isAllNotEmpty($inputData)){
                        $condition[getIdKey($connection,$url['table'])]=getIdValue();
                        $resultat= updateIn($connection,$url['table'],$inputData,$condition);
                    }
                    else{
                        $resultat["status"]=400;
                        $resultat["message"]="Erreur requette: Une des donnees est vide";                
                    }
                }
            }

            setHeaderResponse($resultat['status']);
            echo toJson($resultat);
            break;
        
        case 'PATCH':
            if($url['table']==null || $url["db"]==null){
                // methode ou url non valide
                $resultat=[
                    'status'=>400,
                    'message'=>"La methode ou l'url n'est pas valide. Base de donnees ou table non specifie.",
                    'data'=>[]
                ];    
            }
            elseif(!$dbExist){
                $resultat["status"]=404;
                $resultat["message"]="La base de donnees ".$url['db']." n'existe pas dans votre base de donnees.";               
            }
            elseif($dbExist && !$tableExist){
                $resultat["status"]=404;
                $resultat["message"]="La table ".$url['table']." n'existe pas dans la base de donnees ".$url['db'].".";               
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
                    $resultat["message"]="Erreur requette: L'identifiant pour la modification d'une element dans ". $url['table'] ." n'est pas specifie.";
                }
                elseif(empty($inputData))
                {
                    $resultat["status"]=400;
                    $resultat["message"]="Erreur requette: Aucun donne reçu pour la modification d'une element dans ". $url['table'];
                }
                else{
                    if(isAllNotEmpty($inputData)){
                        $condition[getIdKey($connection,$url['table'])]=getIdValue();
                        $resultat= updateIn($connection,$url['table'],$inputData,$condition);
                    }
                    else{
                        $resultat["status"]=400;
                        $resultat["message"]="Erreur requette: Une des donnees est vide";                
                    }
                }
            }

            setHeaderResponse($resultat['status']);
            echo toJson($resultat);
            break;
        
        case 'DELETE':
            if($url['table']==null || $url["db"]==null){
                // methode ou url non valide
                $resultat=[
                    'status'=>400,
                    'message'=>"La methode ou l'url n'est pas valide. Base de donnees ou table non specifie.",
                    'data'=>[]
                ];    
            }
            elseif(!$dbExist){
                $resultat["status"]=404;
                $resultat["message"]="La base de donnees ".$url['db']." n'existe pas dans votre base de donnees.";               
            }
            elseif($dbExist && !$tableExist){
                $resultat["status"]=404;
                $resultat["message"]="La table ".$url['table']." n'existe pas dans la base de donnees ".$url['db'].".";               
            }
            elseif($url['db']!=null && !$dbExist){
                $resultat["status"]=404;
                $resultat["message"]="La base de donnees ".$url['db']." n'existe pas dans votre base de donnees.";               
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
                    $resultat["message"]="Erreur requette: L'identifiant pour la suppression d'une element dans ". $url['table'] ." n'est pas specifie.";
                }
                else{
                    $condition[getIdKey($connection,$url['table'])]=getIdValue();
                    $resultat= deleteIn($connection,$url['table'],$condition);
                }
            }

            setHeaderResponse($resultat['status']);
            echo toJson($resultat);
            break;
    
        case 'HEAD':            
            $resultat=[
                'status'=>200,
                'message'=>"La requette peut se poursuivre.",
                'data'=>[]
            ];

            setHeaderResponse($resultat['status']);
            echo toJson($resultat);
            break;
        
        case 'OPTIONS':
            $resultat=[
                'status'=>200,
                'message'=>"La requette peut se poursuivre.",
                'data'=>[]
            ];
            
            setHeaderResponse($resultat['status']);
            echo toJson($resultat);
            break;

        default:
            $resultat=[
                'status'=>404,
                'message'=>"Le requette demande ou le protocole n'est pas prise en charge non prise en charge.",
                'data'=>[]
            ];
            setHeaderResponse($resultat['status']);
            echo toJson($resultat);
            break;
        }
        closeConnection($connection);
}
else{
    $resultat=[
        'status'=>400,
        'message'=>"L'url demande n'est pas valide.",
        'data'=>[]
    ];
    closeConnection($connection);

    setHeaderResponse($resultat['status']);
    echo toJson($resultat);
    
}

    // setHeaderResponse('HTTP/1.1 200 OK');
    // setHeaderResponse(200);
    // setHeaderResponse($resultat['status']);



?>