<?php

$register_starttime=0;
$register_endtime=0;


$rstr= register();

$serverName = "127.0.0.1"; //数据库服务器地址
$uid        = "gabf"; //数据库用户名
$pwd        = "Ucdoskeyms18600"; //数据库密码
$collect_url = 'http://scywc.chinapopin.com:8161/ywc/wwcj';
$connectionInfo = array("UID" => $uid, "PWD" => $pwd, "Database" => "Ticket");


while (true) {
    if(time()>=$register_endtime)
    {
        $rstr = register();

    }

    $start_time = isset($end_time)? $end_time: date('Y-m-d H:i:s', time());
    sleep(180);
    $end_time = date('Y-m-d H:i:s', time());

    $conn           = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn == false) {
        echo "connect false！";
        die(print_r(sqlsrv_errors(), true));
    }
    //$start_time='2019-04-12 17:33:27';
    //$end_time='2019-04-13 00:00';

    //$query = sqlsrv_query($conn, "SELECT cIdentityCardNumber,cEndSiteName,cStationName,cSellDateTime,dDepartureDate,dDepartureTime,cPassengerName,idcardtype FROM Ticket.dbo.T_SP_TicketRealName where idcardtype in('户口薄','身份证','护照') and cSellDateTime>'{$start_time}' and cSellDateTime<'{$end_time}'");
    $string = iconv('utf-8', 'GB2312//IGNORE', "'户口薄','身份证','护照'");

    $query = sqlsrv_query($conn, "SELECT cIdentityCardNumber,cEndSiteName,cStationName, CONVERT(varchar(100), cSellDateTime, 20) as cSellDateTime,dDepartureDate,dDepartureTime,cPassengerName,idcardtype FROM Ticket.dbo.T_SP_TicketRealName where idcardtype in({$string}) and  cSellDateTime>'{$start_time}' and cSellDateTime<'{$end_time}'");
    if( $query === false ) {
        die( print_r( sqlsrv_errors(), true));
    }


    $post_data = [
        'servicecode'   => '002',
        'usercode'      => 'msza_gaj_20190313001',
        'passcode'      => $rstr,
        'hycode'        => '0004',
        'hytable'       => 't_gpxxcj',
        'serviceparams' => []
    ];


    while ($row = sqlsrv_fetch_array($query)) {
        $params = [
            'zjlx'=>($row['idcardtype']=='护照')?'03':'01',
            'zjhm'=>$row['cIdentityCardNumber'],
            'czmc'=>$row['cStationName'],
            'gpsj'=>$row['cSellDateTime'],
            'cpdzd'=>$row['cEndSiteName'],
            'fcsj'=>$row['dDepartureDate'].' '.$row['dDepartureTime'].':00',
            'gprxm'=>$row['cPassengerName'],
            'sbzh'=>'5114032019041100123213',

        ];

        foreach ($params as &$param) {
            $encode    = mb_detect_encoding($param, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
            $param = mb_convert_encoding($param, 'UTF-8', $encode);
        }
        $post_data['serviceparams'] = $params;


          $ret = mycurl($collect_url, json_encode($post_data,JSON_UNESCAPED_UNICODE), 1, 0);



          echo  mb_convert_encoding($ret, 'GB2312', mb_detect_encoding($ret, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5')));;

    }

    sqlsrv_free_stmt($query);
    sqlsrv_close($conn);

}





function register()
{

    $GLOBALS['register_starttime']=time();echo date('Y-m-d H:i:s',$GLOBALS['register_starttime'])."\n\t";
    $GLOBALS['register_endtime']=$GLOBALS['register_starttime']+900;echo date('Y-m-d H:i:s',$GLOBALS['register_endtime'])."\n\t";
    $url = 'http://scywc.chinapopin.com:8161/ywc/fwregister';


    $param = [
                             'servicecode' => '001',
                             'usercode'    => 'msza_gaj_20190313001',
                             'passcode'    => '123456'

                         ];

    $ret = mycurl($url, json_encode($param), 1, 0);
    return json_decode($ret, true)['rstr'];

  /*  $param['passcode']=$rstr;
    $param['servicecode']='002';

    $params = [
      'qymc'=>'彭山汽车客运中心站',
      'qyfl'=>'29903',
      'qyxz'=>'09',
      'qydzsf'=>'510000',
      'qydzcs'=>'511400',
      'qydzqx'=>'511403',
      'qydzbc'=>'迎宾大道中段159号',
      'qysspcs'=>'511403420000',
      'busername'=>'abc_12345678',
      'phone'=>'13890303573',
      'buserpwd'=>'12345678'


    ];

    foreach ($params as &$pa) {
        $encode    = mb_detect_encoding($pa, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
        $pa = mb_convert_encoding($pa, 'UTF-8', $encode);
    }
    $param['serviceparams'] = $params;echo json_encode($param,true);
    $ret=mycurl($url, json_encode($param,true), 1, 0);

    $rstr1=json_decode($ret, true)['rstr'];

return [$rstr,$rstr1];*/





}


function mycurl($url, $params = false, $ispost = 0, $https = 0)
{
    $httpInfo = array();
    $ch       = curl_init();
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($https) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
    }
    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
        if (json_decode($params, true)) {


            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length:'.strlen($params)
            ));

        }

    } else {
        if ($params) {
            if (is_array($params)) {
                $params = http_build_query($params);
            }
            curl_setopt($ch, CURLOPT_URL, $url.'?'.$params);// 此处就是参数的列表,给你加了个?

        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }
    $response = curl_exec($ch);

    if ($response === false) {
        echo "cURL Error: ".curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
    curl_close($ch);
    return $response;
}



?>