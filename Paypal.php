<?php

class Paypal {

    public $pem = '-----BEGIN CERTIFICATE-----
MIIDoTCCAwqgAwIBAgIBADANBgkqhkiG9w0BAQUFADCBmDELMAkGA1UEBhMCVVMx
EzARBgNVBAgTCkNhbGlmb3JuaWExETAPBgNVBAcTCFNhbiBKb3NlMRUwEwYDVQQK
EwxQYXlQYWwsIEluYy4xFjAUBgNVBAsUDXNhbmRib3hfY2VydHMxFDASBgNVBAMU
C3NhbmRib3hfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMB4XDTA0
MDQxOTA3MDI1NFoXDTM1MDQxOTA3MDI1NFowgZgxCzAJBgNVBAYTAlVTMRMwEQYD
VQQIEwpDYWxpZm9ybmlhMREwDwYDVQQHEwhTYW4gSm9zZTEVMBMGA1UEChMMUGF5
UGFsLCBJbmMuMRYwFAYDVQQLFA1zYW5kYm94X2NlcnRzMRQwEgYDVQQDFAtzYW5k
Ym94X2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTCBnzANBgkqhkiG
9w0BAQEFAAOBjQAwgYkCgYEAt5bjv/0N0qN3TiBL+1+L/EjpO1jeqPaJC1fDi+cC
6t6tTbQ55Od4poT8xjSzNH5S48iHdZh0C7EqfE1MPCc2coJqCSpDqxmOrO+9QXsj
HWAnx6sb6foHHpsPm7WgQyUmDsNwTWT3OGR398ERmBzzcoL5owf3zBSpRP0NlTWo
nPMCAwEAAaOB+DCB9TAdBgNVHQ4EFgQUgy4i2asqiC1rp5Ms81Dx8nfVqdIwgcUG
A1UdIwSBvTCBuoAUgy4i2asqiC1rp5Ms81Dx8nfVqdKhgZ6kgZswgZgxCzAJBgNV
BAYTAlVTMRMwEQYDVQQIEwpDYWxpZm9ybmlhMREwDwYDVQQHEwhTYW4gSm9zZTEV
MBMGA1UEChMMUGF5UGFsLCBJbmMuMRYwFAYDVQQLFA1zYW5kYm94X2NlcnRzMRQw
EgYDVQQDFAtzYW5kYm94X2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNv
bYIBADAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4GBAFc288DYGX+GX2+W
P/dwdXwficf+rlG+0V9GBPJZYKZJQ069W/ZRkUuWFQ+Opd2yhPpneGezmw3aU222
CGrdKhOrBJRRcpoO3FjHHmXWkqgbQqDWdG7S+/l8n1QfDPp+jpULOrcnGEUY41Im
jZJTylbJQ1b5PBBjGiP0PpK48cdF
-----END CERTIFICATE-----';
    public $return_url = 'http://local.test.com/index/api/return_url';
    public $notify_url = 'http://local.test.com/index/api/notify_url';
    public $cancel_return = 'http://local.test.com/index/api/cancel_return';
    public $account = 's180400131-facilitator@sina.com';
    public $tocken = 'W4sOABPdZm5nkYivmQ-k-XfRXPx7IYgelOuY_gMEIJ9EQkGkKpsgf_jVo90';

    public function test($amount, $item_name, $order_sn = '', $array = array()) {
        if (!isset($array['env'])) {
            $array['env'] = $env = "1";
        }
        if (!isset($array['return'])) {
            $array['return'] = $this->return_url;
        }
        if (!isset($array['notify_url'])) {
            $array['notify_url'] = $this->notify_url;
        }
        if (!isset($array['cancel_return'])) {
            $array['cancel_return'] = $this->cancel_return;
        }
        if (!isset($array['account'])) {
            $array['account'] = $this->account;
        }
        $array['item_name'] = $item_name;

        foreach ($array as $key => $item) {
            if ($key != 'env') {
                if ($item == '') {
                    //  return json_encode(['data' => array(), 'code' => 404, 'message' => '缺少' . $key . '参数']);
                    echo '缺少' . $key . '参数';
                }
            }
        }

        if ($order_sn == '') {
            $order_sn = trim(input('post.order_sn', '', '\Safe::safeHtml'));
        }

        $this->buildForm($order_sn, $amount, $array);
    }

    //提交表单

    private function buildForm($order_sn, $amount, $array) {

        $paypal_email = $array['account'];    //卖家账号
        $custom = '';                        //自定义额外参数
        $currency_code = 'USD';                //货币类型
        $charset = 'utf-8';                    //编码
        $item_name = $array['item_name'];
        if ($array['env'] == "2") {
            $formurl = 'https://www.paypal.com/cgi-bin/webscr'; //正试提交地址  
        } else {
            $formurl = 'https://www.sandbox.paypal.com/cgi-bin/webscr'; //测试提交地址  
        }

        /*
         * 这里inkdiy要用https
         */
        $notifyurl = $array['notify_url'];            //IPN地址
        $cancel_return = $array['cancel_return'];                           //用户取消支付后返回的地址
        $return = $array['return'];              //支付成功后返回的地址
        //更多表单变量，请访问paypal官网
        //http://www.ebay.cn/public/paypal/integrationcenter/list__resource_7.html

        $html = '';
        $html .= '<form id="paypalsubmit" action="' . $formurl . '" method="post">';
        $html .= '<input type="hidden" name="cmd" value="_xclick"><input type="hidden" name="charset" value="' . $charset . '"><input type="hidden" name="business" value="' . $paypal_email . '"><input type="hidden" name="receiver_email" value="' . $paypal_email . '"><input type="hidden" name="item_name" value="' . $item_name . '"><input type="hidden" name="item_number" value="' . $order_sn . '"><input type="hidden" name="currency_code" value="' . $currency_code . '"><input type="hidden" name="custom" value="' . $custom . '"><input type="hidden" name="amount" value="' . $amount . '"><input type="hidden" name="notify_url" value="' . $notifyurl . '" /><input type="hidden" name="cancel_return" value="' . $cancel_return . '" /><input type="hidden" name="return" value="' . $return . '" />';
        //  $html .= '<input type="hidden" name="cmd" value="_xclick"><input type="hidden" name="charset" value="' . $charset . '"><input type="hidden" name="business" value="' . $paypal_email . '"><input type="hidden" name="receiver_email" value="' . $paypal_email . '"><input type="hidden" name="item_name" value="' . $item_name . '"><input type="hidden" name="item_number" value="' . $order_sn . '"><input type="hidden" name="currency_code" value="' . $currency_code . '"><input type="hidden" name="custom" value="' . $custom . '"><input type="hidden" name="amount" value="' . $amount . '"><input type="hidden" name="currency" value="EUR"><input type="hidden" name="notify_url" value="' . $notifyurl . '" /><input type="hidden" name="cancel_return" value="' . $cancel_return . '" /><input type="hidden" name="return" value="' . $return . '" />';
        $html .= '</form><script>document.forms["paypalsubmit"].submit();</script>';
        //   $html .= '</form>';
        echo $html;
    }

    public function returnUrl($array = array()) {
        $request = input('request.');
        $amt = input('request.amt', '');
        $item_number = input('request.item_number', '');
        $st = input('request.st', '');
        $tx = input('request.tx', '');
        $custom = input('request.cm', '');
        $cm = input('request.cm', '');
 
        if ($st == "Completed") {
            if (isset($array['env'])) {
                $env = $array['env'];
            } else {
                $env = 1;
            }
            if ($env == "2") {
                $url = 'https://www.paypal.com/cgi-bin/webscr';
                $pp_hostname = "www.paypal.com";
            } else {
                $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
                $pp_hostname = "www.sandbox.paypal.com";
            }
            if (isset($array['tocken'])) {
                $pdt_tocken = $array['tocken'];
            }else{
                 $pdt_tocken = $this->tocken;
            }
            $req = "cmd=_notify-synch&tx=$tx&at=" . $pdt_tocken;

            if (isset($array['pem'])) {
               $pem = $array['pem'];
            } else {
                 $pem = $this->pem;
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSLVERSION, 6);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: $pp_hostname"));
            curl_setopt($ch, CURLOPT_CAINFO, $pem);
            $res = curl_exec($ch);
            curl_close($ch);
            // var_dump($res);
            if (!$res) {
                return false;
            } else {
                // parse the data
                $lines = explode("\n", trim($res));
                $keyarray = array();
                if (strcmp($lines[0], "SUCCESS") == 0) {
                    for ($i = 1; $i < count($lines); $i++) {
                        $temp = explode("=", $lines[$i], 2);
                        $keyarray[urldecode($temp[0])] = urldecode($temp[1]);
                    }
                    return $keyarray;
                } else if (strcmp($lines[0], "FAIL") == 0) {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public function notifyUrl($array = array()) {
        if (isset($array['env'])) {
            $env = $array['env'];
        } else {
            $env = 1;
        }
        $post = input('post.');
        $out_trade_no = $post['item_number'];

        $req = 'cmd=_notify-validate';
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }

        foreach ($post as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req.= "&$key=$value";
        }
        if (isset($array['pem'])) {
            $pem = $array['pem'];
        } else {
            $pem = $this->pem;
        }

        // $pem = $this->encryption->authCode($Paypal['pem'], "DECODE", 'wuhen');
        if ($env == "2") {
            $url = 'https://www.paypal.com/cgi-bin/webscr'; //正试提交地址     
        } else {
            $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        curl_setopt($ch, CURLOPT_CAINFO, $pem);
        $resultver = curl_exec($ch);

        if ($resultver === false) {
            //  var_dump(curl_error($ch));
         //   echo 'ERROR';
             return false;
            exit();
        }
        curl_close($ch);

        if ($resultver == "VERIFIED") {
            //签名验证成功的处理
            //  \file::write_file(ROOT_PATH . 'public' . DS . "log", json_encode($post));
          //  echo 'VERIFIED';
            return true;
            exit();
        } else if ($resultver == "INVALID") {

          //  echo 'INVALID';
                 return false;
            exit();
        }
    }

}
