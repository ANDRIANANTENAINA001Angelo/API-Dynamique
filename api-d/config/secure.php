<?php

function toSecure(array $data){
    if($data!= null){
        foreach($data as $k=>$v){
            $k= htmlspecialchars($k);
            $v= htmlspecialchars($v);
        }
    }
    return $data;
}

?>