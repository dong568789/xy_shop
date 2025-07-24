<?php
/**
 * @copyright   2015-2019 逍遥商城 <http://www.yunziyuan.com.cn>
 * @creatdate   2012-0501 myllop <myllop@gmail.com>
 */
$menumark = 'quan';
switch ($act) {
	
		//####################// 兑换券码 //####################//
	case 'duihuan':
		if (isset($_p_pesubmit)) {
			$quan_key = pe_dbhold($_p_quan_key);
			//判断该券是否存在
			if($info = $db->pe_select('quanlog',array('quan_key'=>$quan_key ,'user_id'=>0))){
				//进行领取操作
				$db->pe_update('quanlog',array('quan_key'=>$quan_key),array('user_id'=>$_s_user_id , 'user_name'=>$_s_user_name ));
				//统计优惠券数
				$sql1 = " and `quan_id` = ".$info['quan_id']." and `user_id` >0";
				$quan_num_get = $db->pe_num('quanlog', $sql1);
				$sql2 = $sql1 . " and `quanlog_utime`>0";
				$quan_num_use = $db->pe_num('quanlog', $sql2);
				$db->pe_update('quan',array('quan_id'=>$info['quan_id']),array( 'quan_num_get'=>$quan_num_get , 'quan_num_use'=>$quan_num_use));
				
				echo json_encode(array('result'=>true,'show'=>'兑换成功'));
			}else{
				echo json_encode(array('result'=>false,'show'=>'券码无效'));
			}
		}else{
			echo json_encode(array('result'=>false,'show'=>'兑换失败，请刷新页面后重试'));
		}
	break;
	
	//####################// 优惠券列表（领取） //####################//
	default:
		$info_list = $db->pe_selectall('quanlog', array('user_id'=>$_s_user_id, 'order by'=>'quanlog_atime desc'), '*', array(20, $_g_page));
		$tongji['all'] = $db->pe_num('quanlog',array('user_id'=>$_s_user_id));
		$menutitle = '我的优惠券';
		$seo = pe_seo($menutitle);
		include(pe_tpl('quan_list.html'));
	break;
}


?>