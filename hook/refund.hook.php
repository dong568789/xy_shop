<?php
//增加退款单
/*function refund_add($info, $post = array()) {
	global $db;
	$sql_set['refund_id'] = $refund_id = pe_guid('refund|refund_id');
	$sql_set['refund_type'] = $post['refund_type'];
	$sql_set['refund_product_money'] = $post['refund_product_money'];
	$sql_set['refund_wl_money'] = $post['refund_wl_money'] ;
	$sql_set['refund_money'] = $post['refund_product_money'] + $post['refund_wl_money'];
	$sql_set['refund_text'] = $post['refund_text'];
	$sql_set['refund_state'] = $post['refund_state'];
	$sql_set['refund_atime'] = time();
	$sql_set['order_id'] = $info['order_id'];
	$sql_set['orderdata_id'] = $info['orderdata_id'];
	$sql_set['product_id'] = $info['product_id'];
	$sql_set['product_guid'] = $info['product_guid'];
	$sql_set['product_name'] = $info['product_name'];
	$sql_set['product_rule'] = $info['product_rule'];
	$sql_set['product_logo'] = $info['product_logo'];
	$sql_set['product_money'] = $info['product_money'];	
	$sql_set['product_jjmoney'] = $info['product_jjmoney'];
	$sql_set['product_allmoney'] = $info['product_allmoney'];	
	$sql_set['product_num'] = $info['product_num'];
	$sql_set['user_id'] = $info['user_id'];
	$sql_set['user_name'] = $info['user_name'];	
	if ($db->pe_insert('refund', pe_dbhold($sql_set, array('product_rule')))) {
		$db->pe_update('orderdata', array('orderdata_id'=>$info['orderdata_id']), array('refund_id'=>$refund_id, 'refund_state'=>$post['refund_state']));
		return $refund_id;
	}
	else {
		return false;
	}
}*/

//修改退款单
/*function refund_edit($info, $post = array()) {
	global $db;
	$sql_set['refund_product_money'] = $post['refund_product_money'];
	$sql_set['refund_wl_money'] = $post['refund_wl_money'];
	$sql_set['refund_money'] = $post['refund_product_money'] + $post['refund_wl_money'];
	$sql_set['refund_text'] = $post['refund_text'];
	$sql_set['refund_state'] = $post['refund_state'];
	if ($db->pe_update('refund', array('refund_id'=>$info['refund_id']), pe_dbhold($sql_set))) {
		$db->pe_update('orderdata', array('orderdata_id'=>$info['orderdata_id']), array('refund_id'=>$info['refund_id'], 'refund_state'=>$post['refund_state']));
		return true;
	}
	else {
		return false;
	}
}*/

//退款通过操作
function refund_success($refund_id) {
	global $db;
	$info = $db->pe_select('refund', array('refund_id'=>$refund_id));
	$orderinfo = $db->pe_select('order', array('order_id'=>$info['order_id']),'order_payment');
	
	if ($db->pe_update('refund', array('refund_id'=>$refund_id), array('refund_state'=>'success'))) {
		//更新对应子订单状态				
		$db->pe_update('orderdata', array('orderdata_id'=>$info['orderdata_id']), array('refund_id'=>$refund_id, 'refund_state'=>'success'));
		//如果子订单已全部退款，主订单关闭
		if (!$db->pe_num('orderdata', " and `order_id` = '{$info['order_id']}' and `refund_state` != 'success'")) {
			order_callback_close($info['order_id'], "订单退款关闭", false);
		}
		
		add_refundlog($refund_id, 'success');
		//如果金额不为0，需要进行退款操作。
		if($info['refund_money'] > 0){
			if ($orderinfo['order_payment'] == 'wechat'){
				wechat_refund($refund_id);
			}else if($orderinfo['order_payment'] == 'alipay'){
				alipay_refund($info['order_id'],$info['refund_money'],$refund_id);
			}
		}
		return true;
	}
	else {
		return false;
	}
}

//退款关闭操作
function refund_close($id, $type='one') {
	global $db;
	$id = pe_dbhold($id);
	if ($type == 'all') {
		$info_list = $db->pe_selectall('refund', array('order_id'=>$id, 'refund_state'=>array('wcheck', 'wsend', 'wget', 'refuse')));
	}
	else {
		$info_list = $db->pe_selectall('refund', array('refund_id'=>$id));
	}
	foreach ($info_list as $v) {
		$db->pe_update('refund', array('refund_id'=>$v['refund_id']), array('refund_state'=>'close'));
		$db->pe_update('orderdata', array('refund_id'=>$v['refund_id']), array('refund_id'=>0, 'refund_state'=>''));
		if ($type == 'all') {
			add_refundlog($v['refund_id'], 'admin_close');			
		}
		else {
			add_refundlog($v['refund_id'], 'close');		
		}
	}
	return true;
}

//计算最大退款金额
function refund_maxmoney($order_id, $orderdata_id) {
	global $db;
	$order = $db->pe_select('order', array('order_id'=>$order_id), 'order_pstate, order_money, order_wl_money, order_product_money, order_quan_money, order_point_money');	
	if (!$order['order_pstate']) return array('product'=>'0.0', 'wl'=>'0.0');
	$orderdata = $db->pe_select('orderdata', array('orderdata_id'=>$orderdata_id));
	//计算最大退款商品金额
	if ($order['order_product_money'] > 0) {
		$zhekou = ($orderdata['product_allmoney'] / $order['order_product_money']) * ($order['order_quan_money'] + $order['order_point_money']);
		$max['product'] = pe_num($orderdata['product_allmoney'] - $zhekou, 'round', 1);	
	}
	else {
		$max['product'] = 0;
	}
	//计算最大退款运费金额
	$tongji = $db->pe_select('refund', " and `order_id` = '{$order_id}' and `orderdata_id` != '{$orderdata_id}' and `refund_state` != 'close'", 'sum(refund_wl_money) as `money`');
	$refund_wl_money = pe_num($tongji['money'], 'round', 1);
	$max['wl'] = pe_num($order['order_wl_money'] - $refund_wl_money, 'round', 1);
	//优惠券金额大于商品金额会出现负数
	if ($max['product'] < 0) $max['product'] = 0;
	if ($max['wl'] < 0) $max['wl'] = 0;
	return $max;
}

//支付宝退款操作
function alipay_refund($out_trade_no,$refund_amount,$refund_order){
	pe_lead("public/plugin/payment/alipay/lib/AopClient.php");
	pe_lead("public/plugin/payment/alipay/lib/AlipayTradeRefundRequest.php");
	$aop = new AopClient ();
	$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
	$aop->appId = '2016012601122018';
	$aop->rsaPrivateKey = 'MIIEpQIBAAKCAQEAnzRdAOBQJaQoKlpZ9ONY3w+GTh+/jiPzGi5w0vWX58NSXL8YJ6ctm9B0CMEYShdaTxIZ6n1Uw71N6NZAe2YMBq7lLzS5TNV/wjvGDwOkJyHNKUMR1uGrba0XuodNEpZe+yhjq0z+6o9Lg68y9yBJ1HCY6D2js6unSmVbBwn81WRHyrwqrOX9qjyvrhzazZnASvNay6/3pt7NBNInE5RViYkRIXG3VurECC87hPJqQwSiS8ppChNuCBBnXcyS6R8PjckwoBP+kN6BfTR2/EvWEaIatV1cFeeGUVQyiZe/18rvx7+lgK6znvTLnkc92OX/ZtWV+Y43ppGNW5XJkdtr5wIDAQABAoIBAHjmgg5NSnAbZqkrLg9Lq0zR9FNVxtmBsXbbd7Cs8yv854opUIWkbn1aYxPW18REUb+kZmvtu7ZeB1Ffi15qk41ruf4MUv/nTntJe1IhmMJi5/OCzIRbohzLCy8K2tvXt8+Rz4biFOA568XYAaMsMQUUcPpE/RFPtN5JdEQ3MHs31q+doUEir838b/ozmlFWQ73BGWHj15bt/6nCgqeWq+QT8jZ14c8Nj0UdW9RX3oxGGSHU6Cy9smryt1weqxypX7NpTM3DcWfgULveWmQ+71DzxZrlQO4aI/y8ngDYJgBI32Od01WEVUNKPtX/7tUzWmEW02iYeYTp/p2+9xZsejECgYEAyz9mSRcsUc2EleeNkEBZ8uzcKmUYWFJemAJ6avLHhESnVm28fm0gWDEYwqdxmh54zJzO+l++vfx+KiMpALi3EHOYmaZyDhPRTkCw8JFaluE7ly5PD39jL4bml5RKDHczE8FowXanV3abGxgaCrqRaM70YS137pHZXcAFqhbnyukCgYEAyIaPeLB1LWJD9GXSt9jFqcMmo+YdhbsR0E5HenufMJbmYLWxYjkgULgC1WHtAJ3qSEwX1LrP5TvJx6tOzPXFHLigxi3bHyvw5BAHsVgyYnM88bQcXkaJRCvCrzuJIz8vYtsRp+1pGlmpN3WQOnFIb4ofzaRaIZHWwf2AnYlYnk8CgYEAqcHQWufh+okfXl1CXeOBvWacFUTO6qYRoAEh4kXY4t68rXGGaEoZI9AOEDbuqX0eBDhMn85scB59+g/X5WRicnk6Ucs1pBBlV2is4KFm+nIw5XqmShlp7H5OBviySGse3DdEmaHS7zh/IEgnKCLf5JxGfQwPCdIDrUv+7yumelECgYEAjDDW+S+UnGgIzkKpsPKSLn9K8mcc2LPSXml7u3vV7WhyI8ai/uKlXwlmobvWsAm3E1hviMAaPE8yYK+5Zb2lUtEZRbQlb4OO5EL4vdnFT+L1Bp4+/wIdUVWuxEfBNq0j+d7iVx65zjxE+4rrjJBKbgCI/4IZqUKGfBU45+pv1C0CgYEAndIqsLJHSutZgDRq5LBdtfyAjTQCRH0KtwF9xGXBnAkSyhlS15qoG6ukRJQNs+PIuc9Qqsv9HaSH59kqGFqf2y5fUIpOjf0Iv9gjMguh2Klya3zMze3luqaTbiG/GRl7lCvzvnm09jfr3G+o+a9cww+UeaOTZNv7biZ8A/tvxyY=';
	$aop->alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAoJrdxe7XnMYYmg+xqJbEcFWCDEOA6zeKuHRs3KJ6qjO+iZQP78/shBUAL/hHnSSjOakGf0yIQEn888KMG2AWoD4tv8hmEmoewL2CACbEwwxfQiSqvJhWcbFxDizY1gTFzRclGBw5FzBdeaHG66rmZuLcUSIewKwRgCo++/jmzsI8s9nOhOpXhu4APbadb8EhWzxaQA2fcFxAf/ExnF0gabBJ/Z/urtQhIcvhK1wTEgsM/u70bNSOFd6MP6uH2t6kqTfkFC939t/KScnCOh1VAmw69RlMpAiCRsT5HzFL6GdSeSw1xJcPa/0GeKzSodd/eM9Np3kEnlwqsWfW90NXJwIDAQAB';
	$aop->apiVersion = '1.0';
	$aop->signType = 'RSA2';
	$aop->postCharset='UTF-8';
	$aop->format='json';
	$request = new AlipayTradeRefundRequest ();
	$request->setBizContent("{" .
		//            "\"trade_no\":\"2017112821001004030523090753\"," .
					"\"out_trade_no\":\"$out_trade_no\"," .
					"\"refund_amount\":$refund_amount," .
					"\"refund_reason\":\"交易退款\"," .
					"\"out_request_no\":\"$refund_order\"," .
					"\"operator_id\":\"\"," .
					"\"store_id\":\"\"," .
					"\"terminal_id\":\"\"" .
					"  }");
	$result = $aop->execute ( $request); 
	$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
	$resultCode = $result->$responseNode->code;
	if(!empty($resultCode)&&$resultCode == 10000){
		return true;
	} else {
		return false;
	}
}

//增加退款操作记录
function add_refundlog($refund_id, $type, $text = '') {
	global $db, $ini;
	$info = $db->pe_select('refund', array('refund_id'=>$refund_id));
	switch ($type) {
		case 'add':
			$sql_set['refundlog_text'] = "发起了《{$ini['refund_type'][$info['refund_type']]}》申请，退款金额：{$info['refund_money']}元，说明：{$info['refund_text']}";
		break;
		case 'edit':
			$sql_set['refundlog_text'] = "修改了《{$ini['refund_type'][$info['refund_type']]}》申请，退款金额：{$info['refund_money']}元，说明：{$info['refund_text']}";
		break;
		case 'send':
			$sql_set['refundlog_text'] = "已寄回商品，快递公司：{$info['refund_wl_name']}，快递单号：{$info['refund_wl_id']}";
		break;
		case 'close':
		case 'admin_close':
			$sql_set['refundlog_text'] = "取消本次申请，退款关闭";
		break;
		case 'agree':
			$sql_set['refundlog_text'] = "同意本次申请，退款金额：{$info['refund_money']}元，退货地址：{$info['refund_tname']}，{$info['refund_phone']}，{$info['refund_address']}";
		break;	
		case 'refuse':
			$sql_set['refundlog_text'] = "拒绝本次申请，说明：{$text}";
		break;
		case 'success':
			$sql_set['refundlog_text'] = $text ? $text : "同意本次申请，已退款金额：{$info['refund_money']}元";
		break;
	}
	$sql_set['refundlog_atime'] = time();
	$sql_set['refund_id'] = $refund_id;
	if (in_array($type, array('add', 'edit', 'send', 'close'))) {
		$sql_set['user_id'] = $info['user_id'];
		$sql_set['user_name'] = $info['user_name'];
	}
	$db->pe_insert('refundlog', pe_dbhold($sql_set));
}
?>