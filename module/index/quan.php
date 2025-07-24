<?php
/**
 * @copyright   2015-2019 逍遥商城 <http://www.izhu.com>
 * @creatdate   2012-0501 myllop <myllop@gmail.com>
 */
switch ($act) {
	//####################// 优惠券列表 //####################//
	case 'list':
		$info_list = $db->pe_selectall('quan', array('quan_type'=>'online', 'order by'=>'`quan_atime` desc'), '*', array(25, $_g_page));
		//把个人领取的券都放在数组里
		foreach ($info_list as $k=>$v) {
			$myquan[$k][$v['quan_id']] = $db->pe_select('quanlog', array('quan_id'=>$v['quan_id'],'user_id'=>$_s_user_id));
		}
		
		$nowpath = " > <a href='".pe_url("article-{$class_type}")."'>{$ini['class_type'][$class_type]}</a> > <a href='".pe_url("article-list-{$class_id}")."'> {$cache_class[$class_id]['class_name']}</a>";
		$menutitle = $cache_class[$class_id]['class_name'];
		$seo = pe_seo($menutitle);
		include(pe_tpl('quan_list.html'));
	break;

	
	//####################// 优惠券详情（领取） //####################//
	default:
		$quan_id = intval($act);
		//处理领取操作
		if (isset($_p_pesubmit)) {
			$info = $db->pe_select('quan', array('quan_id'=>$quan_id));
			$user = $db->pe_select('user', array('user_id'=>$_s_user_id));
			if(!$_s_user_id){
				echo json_encode(array('result'=>false,'show'=>'Iltimos, avval tizimga kiring'));
				exit();
			}
			//判断是否已经领取
			$loginfo = $db->pe_select('quanlog', array('quan_id'=>$quan_id,'user_id'=>$_s_user_id));
			if($loginfo['quanlog_id']){
				echo json_encode(array('result'=>false,'show'=>'您已领取'));
				exit();
			}
			$_p_info['quan_id'] = $quan_id;
			$_p_info['quan_name'] = $info['quan_name'];
			$_p_info['quan_money'] = $info['quan_money'];
			$_p_info['quan_limit'] = $info['quan_limit'];
			$_p_info['quan_sdate'] = $info['quan_sdate'];
			$_p_info['quan_edate'] = $info['quan_edate'];
			$_p_info['product_id'] = $info['product_id'];
			$_p_info['user_id'] = $_s_user_id;
			$_p_info['user_name'] = $user['user_name'];
			$_p_info['quanlog_atime'] = $_p_info['quanlog_atime'] ? strtotime($_p_info['quanlog_atime']) : time();
			if ($db->pe_insert('quanlog', pe_dbhold($_p_info))) {
				//统计优惠券数
				$sql1 = " and `quan_id` = ".$quan_id." and `user_id` >0";
				$quan_num_get = $db->pe_num('quanlog', $sql1);
				$sql2 = $sql1 . " and `quanlog_utime`>0";
				$quan_num_use = $db->pe_num('quanlog', $sql2);
				$db->pe_update('quan',array('quan_id'=>$quan_id),array( 'quan_num_get'=>$quan_num_get , 'quan_num_use'=>$quan_num_use));
				echo json_encode(array('result'=>true,'show'=>'领取成功'));
			}
			else {
				echo json_encode(array('result'=>false,'show'=>'领取失败'));
			}
			exit();
		}
		
		$info_list = array();
		//$db->pe_update('article', array('article_id'=>$article_id), '`article_clicknum`=`article_clicknum`+'.rand(2,5));
		$info = $db->pe_select('quan', array('quan_id'=>$quan_id));
		if($info['product_id']){
			$sqlwhere = " and product_id in (".$info['product_id'].")";
			$info_list = $db->pe_selectall('product', $sqlwhere, '*', array(20, $_g_page));
		}
		$quanlog_num = $db->pe_num('quanlog',  array('quan_id'=>$quan_id,'user_id'=>$_s_user_id));
		
		$seo = pe_seo($info['quan_name']);
		include(pe_tpl('quan_view.html'));
	break;
}



function quan_limitshow($quaninfo){
	if($quaninfo){
		//满150元，指定商品可用
		if($quaninfo['quan_limit']>0 ){
			return "满".$quaninfo['quan_limit']."元，指定商品可用";
		}
		//无关联商品，无门槛
		if($quaninfo['product_id']=='' ){
			return "无门槛";
		}
	}
	
}
?>