<?php

function isAllNotEmpty(array $inputData){
    $result=true;
    foreach($inputData as $k => $v){
        if(empty($k)|| empty($v)){
            $result= false;
        }
    }
    return $result;
}

function isAllKeyNotEmpty(array $inputData){
    $result=true;
    foreach($inputData as $k => $v){
        if(empty($k)){
            $result= false;
        }
    }
    return $result;
}

function inputDataParse(array $inputData){
    $fields=[];
    $conditions=[];

    foreach($inputData as $k => $v){
        if(!empty($k) && !empty($v)){
            array_push($conditions,[$k=>$v]);
        }
        elseif(!empty($k) && empty($v)){
            array_push($fields,$k);
        }
    }

    $result= [
        'fields'=>$fields,
        'conditions'=> $conditions
    ];

   

    return $result;
}

?>