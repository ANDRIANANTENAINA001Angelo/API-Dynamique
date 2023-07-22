<?php
function getMethode(){
    return $_SERVER['REQUEST_METHOD'];
}

function urlParse(){
    $url= array();

    //existence et validité
    if(isset($_GET['url']) && !empty($_GET['url'])){
        $url['exist']=true;
    }
    else{
        $url['exist']=false;
    }

    //DB name et TABLE name
    $arrayUrl= explode("/",$_GET['url']);
    //database
    if(isset($arrayUrl[1]) && !empty($arrayUrl[1])){
        $url['db']=$arrayUrl[1];
    }
    else{
        $url['db']=null;
    }
    
    
    //table
    if(isset($arrayUrl[2]) && !empty($arrayUrl[2])){
        $url['table']=$arrayUrl[2];
    }
    else{
        $url['table']=null;
    }

    //id
    if(isset($arrayUrl[3]) && !empty($arrayUrl[3])){
        $url['id']=$arrayUrl[3];
    }
    else{
        $url['id']=null;
    }

    return $url;
}

function isValideUrl(){
    if(!empty($_GET['url']))
    {     
        $arrayUrl= explode("/",$_GET['url']);
        if(count($arrayUrl)>=1)
        {
            return true;            
        }
        else
        {
            return false;
        }
    }
    else
    {    
        return false;   
    }
}

function dbNameExist(){
    if(!empty($_GET['url'])){     
        $arrayUrl= explode("/",$_GET['url']);
        if(count($arrayUrl)>=2){
            return true;            
        }
        else{
            return false;
        }
    }
    else{    
        return false;   
    }
}

function tableNameExist(){
    if(!empty($_GET['url'])){     
        $arrayUrl= explode("/",$_GET['url']);
        if(count($arrayUrl)>=3){
            return true;            
        }
        else{
            return false;
        }
    }
    else{    
        return false;   
    }
}

function getDbName(){
    $urlArray= explode("/",$_GET['url']);
    $table=$urlArray[1];
    return $table;
}

function getTable(){
    $urlArray= explode("/",$_GET['url']);
    $table=$urlArray[2];
    return $table;
}

function getIdValue(){
    $urlArray= explode("/",$_GET['url']);
    $id=$urlArray[3];
    return $id;
}

function idExist(){
    $arrayUrl= explode("/",$_GET['url']);
    if(count($arrayUrl)==4 && !empty($arrayUrl[3]) && isset($arrayUrl[3])){
        return true;            }
    else{
        return false;
    }
}

function isExistCondition(){
    if(isset($_GET['cond']) && !empty($_GET['cond']) && count(explode(",",$_GET['cond']))>=1){
        return true;
    }
    else{
        return false;
    }
}

function getCondition(){
    
    if(empty($_GET['cond']) && !isset($_GET['cond'])){
        return [];
    }
    else{
      
        $arrayCond= explode(",",$_GET['cond']);

        $fields= array();

        foreach($arrayCond as $index=>$json){
            foreach(json_decode($json) as $k=>$v){
                $fields[$k]=$v;
            }
        }
        return $fields;
    }
}

function isExistFields(){
    if(isset($_GET['fields']) && !empty($_GET['fields']) && count(explode(",",$_GET['fields']))>=1){
        return true;
    }
    else{
        return false;
    }
}

function getFields(){
    
    if(empty($_GET['fields']) && !isset($_GET['fields'])){
        return [];
    }
    else{
      
        $arrayCond= explode(",",$_GET['fields']);
  
        $fields_index=array();
           
        for($i=0;$i<count($arrayCond);$i++){
            $fields_index[$i]= $arrayCond[$i];
        }

        return $fields_index;
    }
}

function isExistDistinct(){
    if(isset($_GET['d']) && !empty($_GET['d']) && $_GET['d']==1){
        return true;
    }
    else{
        return false;
    }
}

?>