<?php
/**
 * @copyright   2015-2019 逍遥商城 <http://www.yunziyuan.com.cn>
 * @creatdate   2012-0501 myllop <myllop@gmail.com>
 */
$menumark = 'pointlog';
switch ($act) {
	//####################// 积分明细 //####################//
	default:
		$_g_user_name && $sql_where .= " and `user_name` like '%{$_g_user_name}%'";
		$_g_type && $sql_where .= " and `pointlog_type` = '{$_g_type}'";
		$sql_where .= ' order by pointlog_id desc';

		$info_list = $db->pe_selectall('pointlog', $sql_where, '*', array(50, $_g_page));
		$tongji['all'] = $db->pe_num('pointlog');
		$seo = pe_seo($menutitle='积分明细');
		include(pe_tpl('pointlog_list.html','admin'));
	break;
}
?>