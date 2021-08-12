<?php
// 8-22
namespace App\Services\Order;

use App\Services\BaseServices;

class ExpressServices extends BaseServices
{
    public function getExpressName($code) {
        return [
            'ZTO' => '中通快递',
            'YTO' =>'圆通速递',
            'YD' => '韵达速递',
            'YZPY' => '邮政快递包裹',
            'EMS' => 'EMS',
            'DBL' => '德邦快递',
            'FAST' => '快捷快递',
            'ZJS' => '宅急送',
            'TNT' => 'TNT快递',
            'UPS' => 'UPS',
            'DHL' => 'DHL',
            'FEDEX' => 'FEDEX联邦(国内件)',
            'FEDEX_GJ' => 'FEDEX联邦(国际件)'
        ][$code] ?? '';
    }

    const APP_ID = 1638577;
    const APP_KEY = '588a2e96-d957-xxxx-8937-13547c4a5656';
    const APP_URL = 'http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx';

    public function getOrderTraces($com, $code) {// 
        $requestData = "{OrderCode: '', ShipperCode: '$com', LogisticCode: '$code',   }";
        $datas = array(
            'EBusinessID' => self::APP_ID,
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData),
            'DataType' => '2'
        );
        $datas['DataSign'] = $this->encrypt($requestData, self::APP_KEY);
        $result = $this->sendPost(self::APP_URL, $datas);
        $result = json_decode($result, true);

        // 缺点 直接将物流接口的返回数据返回给前端 以后如果换了供应商 前端也需要相应更改
        // 最好是做一个统一的返回结构 适配不同物流提供商返回的数据 这样才能更好的做好可扩展
        return $result; 
    }    

    private function sendPost($url, $datas) {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if (empty($url_info['port'])) {
         $url_info['port'] = 80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader .= "Host:" . $url_info['host'] . "\r\n";
        $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader .= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader .= "Connection:close\r\n\r\n";
        $httpheader .= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
        if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
            break;
        }
        }
        while (!feof($fd)) {
            $gets .= fread($fd, 128);
        }
        fclose($fd);
        return $gets;
    }
    
    // 电商Sign签名生成
    private function encrypt($data, $appkey)
    {
        return urlencode(base64_encode(md5($data . $appkey)));
    }
}
