<?php
$menumark = 'moneylog';
switch($act) {
	//####################//  //####################//
	default:
		$info_list = $db->pe_selectall('moneylog', array('user_id'=>$_s_user_id, 'order by'=>'moneylog_id desc'), '*', array(30, $_g_page));

		$tongji['all'] = $db->pe_num('moneylog', array('user_id'=>$_s_user_id));
		$seo = pe_seo($menutitle='Fond tafsilotlari');
		include(pe_tpl('moneylog_list.html'));
	break;
}
?>