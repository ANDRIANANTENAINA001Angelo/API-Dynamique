<?php

function toSecure(array $data){
    foreach($data as $k=>$v){
        $k= htmlspecialchars($k);
        $v= htmlspecialchars($v);

    }
    return $data;
}

?>