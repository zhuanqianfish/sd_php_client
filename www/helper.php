<?php

function success($msg = 'success',$data=null){
    echo json_encode([
        'code' => 1,
        'message' => $msg,
        'data' => $data
        ]
    );
}

function error($msg = 'error',$data=null){
    echo json_encode([
        'code' => 0,
        'message' => $msg,
        'data' => $data
        ]
    );
}



//同步调用辅助方法
function doCurlPostRequest($url = '',Array $data = array(),$authpass=""){
    $data_string = json_encode($data,JSON_UNESCAPED_UNICODE);
    // halt($data_string);
    // $data_string = $data;
    $curl_con = curl_init();
    curl_setopt($curl_con, CURLOPT_URL, $url);
    curl_setopt($curl_con, CURLOPT_HEADER, false);
    curl_setopt($curl_con, CURLOPT_POST, true);
    curl_setopt($curl_con, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl_con, CURLOPT_CONNECTTIMEOUT, 120);        //最大时间
    curl_setopt($curl_con, CURLOPT_TIMEOUT, 120);
    curl_setopt($curl_con, CURLOPT_USERPWD, $authpass); //与服务器启动参数保持一致
    
    curl_setopt($curl_con, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
    );
    curl_setopt($curl_con, CURLOPT_POSTFIELDS, $data_string);
    $res = curl_exec($curl_con);
    $status = curl_getinfo($curl_con);
    curl_close($curl_con);
    if (isset($status['http_code']) && $status['http_code'] == 200) {
        return $res;
    } else {
        return FALSE;
    }
}


//同步调用辅助方法
function doCurlGetRequest($url = '',Array $data = array(),$authpass=""){
    $data_string = json_encode($data,JSON_UNESCAPED_UNICODE);
    // halt($data_string);
    // $data_string = $data;
    $curl_con = curl_init();
    curl_setopt($curl_con, CURLOPT_URL, $url);
    curl_setopt($curl_con, CURLOPT_HEADER, false);
    // curl_setopt($curl_con, CURLOPT_POST, false);
    curl_setopt($curl_con, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl_con, CURLOPT_USERPWD, $authpass); //与服务器启动参数保持一致
    curl_setopt($curl_con, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
    );
    
    $res = curl_exec($curl_con);
    $status = curl_getinfo($curl_con);
    curl_close($curl_con);
    if (isset($status['http_code']) && $status['http_code'] == 200) {
        return $res;
    } else {
        return FALSE;
    }
}
