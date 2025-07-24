<?php
/**
 * @copyright   2015-2019 逍遥商城 <http://www.yunziyuan.com.cn>
 * @creatdate   2012-0501 myllop <myllop@gmail.com>
 */
switch ($act) {
	//####################// 折扣列表 //####################//
	case 'zhekou':
		$sqlfield = " `product_id`,`product_name`,`product_logo`,`product_money`,`product_smoney`,`huodong_etime`";
		$sql = "select ".$sqlfield." from (select * from ".dbpre."huodongdata order by product_money asc) as bb where `huodong_etime`>".time()." and `huodong_type`='zhekou'  group by product_id order by huodong_stime DESC";

		$info_list = $db->sql_selectall($sql,  array(25, $_g_page));
		
		$nowpath = " > ";
		$menutitle = "限时折扣";
		$seo = pe_seo($menutitle);
		include(pe_tpl('huodong_zhekou.html'));
	break;

	//####################// 拼团列表 //####################//
	case 'pintuan':
		$sqlfield = " `product_id`,`product_name`,`product_logo`,`product_money`,`product_smoney`,`huodong_etime`,product_ptnum";
		$sql = "select ".$sqlfield." from (select * from ".dbpre."huodongdata order by product_money asc) as bb where `huodong_etime`>".time()." and `huodong_type`='pintuan'  group by product_id order by huodong_stime DESC";

		$info_list = $db->sql_selectall($sql,  array(25, $_g_page));
		
		$nowpath = " > ";
		$menutitle = "限时拼团";
		$seo = pe_seo($menutitle);
		include(pe_tpl('huodong_pintuan.html'));
	break;
	
	//####################// 折扣（领取） //####################//
	default:
		
}




?>