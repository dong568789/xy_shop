<?php
/**
 * @copyright   2015-2019 逍遥商城 <http://www.yunziyuan.com.cn>
 * @creatdate   2016-0101 myllop <myllop@gmail.com>
 */
$menumark = 'userbank';
switch ($act) {
	//####################// 账户增加 //####################//
	case 'add':
		//$user = $db->pe_select('user', array('user_id'=>$_s_user_id));
		if (isset($_p_pesubmit)) {
			pe_token_match();
			if (!in_array($_p_userbank_type, array('alipay', 'wechat')) && !$_p_userbank_address) pe_jsonshow(array('result'=>false, 'show'=>"Iltimos, bank ma'lumotlarini to'ldiring"));
			if (!$_p_userbank_num) pe_jsonshow(array('result'=>false, 'show'=>"Iltimos, to'lov hisob raqamini to'ldiring"));
			//if ($db->pe_num('userbank', array('userbank_num'=>pe_dbhold($_p_userbank_num)))) pe_jsonshow(array('result'=>false, 'show'=>'收款帐号已存在'));
			if (!$_p_userbank_tname) pe_jsonshow(array('result'=>false, 'show'=>"Iltimos, oluvchini to'ldiring"));
			$sql_set['userbank_type'] = $_p_userbank_type;
			$sql_set['userbank_name'] = $ini['userbank_type'][$_p_userbank_type];
			$sql_set['userbank_address'] = $_p_userbank_address;
			$sql_set['userbank_tname'] = $_p_userbank_tname;
			$sql_set['userbank_num'] = $_p_userbank_num;
			$sql_set['userbank_atime'] = time();
			$sql_set['user_id'] = $_s_user_id;
			$sql_set['user_name'] = $_s_user_name;
			if ($db->pe_insert('userbank', pe_dbhold($sql_set))) {
				pe_jsonshow(array('result'=>true, 'show'=>"Muvaffaqiyatli qo'shildi"));
			}
			else {
				pe_jsonshow(array('result'=>false, 'show'=>"Qo‘shish amalga oshmadi"));
			}
		}
		$seo = pe_seo($menutitle="Yangi hisob qo'shing");
		include(pe_tpl('userbank_add.html'));
	break;
	//####################// 账户删除 //####################//
	case 'del':
		pe_token_match();
		$userbank_id = intval($_g_id);
		if ($db->pe_delete('userbank', array('userbank_id'=>$userbank_id, 'user_id'=>$_s_user_id))) {
			pe_jsonshow(array('result'=>true, 'show'=>"Muvaffaqiyatli o'chirish"));
		}
		else {
			pe_jsonshow(array('result'=>false, 'show'=>"Oʻchirib boʻlmadi"));
		}
	break;
	//####################// 账户列表 //####################//
	default:
		$info_list = $db->pe_selectall('userbank', array('user_id'=>$_s_user_id, 'order by'=>'userbank_id desc'), '*', array(30, $_g_page));

		$tongji['all'] = $db->pe_num('userbank', array('user_id'=>$_s_user_id));
		$seo = pe_seo($menutitle="Yig'ish hisobi");
		include(pe_tpl('userbank_list.html'));
	break;
}
?>