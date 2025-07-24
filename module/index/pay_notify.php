<?php

use ptpaysdk\core\PtSdk;
pe_lead('hook/order.hook.php');
$cache_payment = cache::get('payment');

require_once dirname(__FILE__) . '/../../public/ptpaysdk/core/PtSdk.php';
$pay = new PtSdk();
//验证签名
if($pay->isSign())
{
    //再次判断订单状态,如过服务器有这个订单就处理业务
    if($pay->isCheckOrder() || $pay->checkOrderState())
    {
        //签名验证成功,订单验证成功
        //---------开始业务逻辑----------------
        $request_data =get_notify_parameter();
        $type =$request_data['type'];

        if(order_callback_pay($request_data['?payId'],null,$type==1 ?'wechat':'alipay'))
        {
            exit('success');
        }
        //----------业务逻辑结束---------------
        //告诉服务器已经收到通知
    }
    else
    {
        exit('fail');
    }
}
else
{
    exit('fail');
}


