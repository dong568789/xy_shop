<?php
$wechat_config = wechat_config();
$code = $_GET['code'];
$state = $_GET['state'];
//$_SESSION['pro_id'] = $_g_pro_id;
if(!empty($code)){
	$tokeninfo = wechat_user_access_token($code);
	if(!empty($tokeninfo['access_token']) && !empty($tokeninfo['openid']) && !empty($_s_user_id)){
		$userinfo = wechat_userinfo($tokeninfo);
		$info = $db->pe_select('user', " and `user_id` = ".$_s_user_id."");
		if ($db->pe_num('user', array('user_wx_openid'=>pe_dbhold($userinfo['openid'])))) 
		{
			$bind_result = 'bindother';
			$seo = pe_seo($menutitle='绑定微信');
			include(pe_tpl('do_wx_bind.html'));
			exit();
		}	
			
			$sqlset['user_nickname'] = $info['user_nickname'] ? $info['user_nickname'] : $userinfo['nickname'];
			$sqlset['user_logo'] = $info['user_logo'] ? $info['user_logo'] : $userinfo['headimgurl'];
			$sqlset['user_wx_openid'] = $userinfo['openid'];
			if ($db->pe_update('user', array('user_id'=>$_s_user_id), $sqlset)) {
				$_SESSION['user_wx_openid'] = $userinfo['openid'];
				$_SESSION['user_nickname'] = $userinfo['nickname'];
				$bind_result = 'ok';
				//pe_jsonshow(array('result'=>true, 'show'=>'绑定成功'));
			}
		$seo = pe_seo($menutitle='绑定微信');
		include(pe_tpl('do_wx_bind.html'));
	}
	else {
		pe_jsonshow(array('result'=>false, 'show'=>'access_token is null'));
	}
	}
	else {
	pe_jsonshow(array('result'=>false, 'show'=>'code is null'));
	}
		
	
?>
