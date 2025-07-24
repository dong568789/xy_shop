<?php
/**
 * @copyright   2015-2019 逍遥商城 <http://www.yunziyuan.com.cn>
 * @creatdate   2012-0501 myllop <myllop@gmail.com>
 */
$menumark = 'moneylog';
switch ($act) {
	//####################//  //####################//
	default:
		$_g_user_name && $sql_where .= " and `user_name` like '%{$_g_user_name}%'";
		$_g_type && $sql_where .= " and `moneylog_type` = '{$_g_type}'";
		$sql_where .= ' order by moneylog_id desc';

		$info_list = $db->pe_selectall('moneylog', $sql_where, '*', array(50, $_g_page));
		$tongji['all'] = $db->pe_num('moneylog');
		$seo = pe_seo($menutitle='Fond tafsilotlari');
		include(pe_tpl('moneylog_list.html','admin'));
	break;
}
?>