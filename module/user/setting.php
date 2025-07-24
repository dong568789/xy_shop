<?php
switch($act) {
	//####################// 修改头像  //####################//
	case 'logo':
		$menumark = 'setting_logo';
		$info = $db->pe_select('user', array('user_id'=>$_s_user_id), 'user_logo');
		if (isset($_p_pesubmit)) {
			pe_token_match();
			if ($db->pe_update('user', array('user_id'=>$_s_user_id), array('user_logo'=>pe_dbhold($_p_user_logo)))) {
				pe_jsonshow(array('result'=>true, 'show'=>'Telefon raqami allaqachon mavjud'));
			}
			else {
				pe_jsonshow(array('result'=>false, 'show'=>'Yuborilmadi'));
			}
		}
		$seo = pe_seo($menutitle="Profil rasmini o'zgartirish");
		include(pe_tpl('setting_logo.html'));
	break;
	//####################// 登录密码修改  //####################//
	case 'pw':
		$menumark = 'setting_pw';
		if (isset($_p_pesubmit)) {
			pe_token_match();
			if (!$db->pe_num('user', array('user_id'=>$_s_user_id, 'user_pw'=>md5($_p_user_oldpw)))) pe_jsonshow(array('result'=>false, 'show'=>"Joriy parol noto'g'ri"));
			if (strlen($_p_user_pw) < 6 or strlen($_p_user_pw) > 20) pe_jsonshow(array('result'=>false, 'show'=>"Yangi parol 6-20 belgidan iborat bo'lishi kerak"));
			if ($_p_user_pw != $_p_user_pw1) pe_jsonshow(array('result'=>false, 'show'=>'Yangi parol va tasdiqlangan parol mos kelmaydi'));
			if ($db->pe_update('user', array('user_id'=>$_s_user_id), array('user_pw'=>md5($_p_user_pw)))) {
				pe_jsonshow(array('result'=>true, 'show'=>'Oʻzgartirish muvaffaqiyatli'));
			}
			else {
				pe_jsonshow(array('result'=>false, 'show'=>'O‘zgartirish amalga oshmadi'));
			}
		}
		$seo = pe_seo($menutitle="Kirish parolini o'zgartiring");
		include(pe_tpl('setting_pw.html'));
	break;
	//####################// 支付密码修改  //####################//
	case 'paypw':
		$menumark = 'setting_pw';
		if (isset($_p_pesubmit)) {
			pe_token_match();
			if ($user['user_paypw'] && !$db->pe_num('user', array('user_id'=>$_s_user_id, 'user_paypw'=>md5($_p_user_oldpw)))) pe_jsonshow(array('result'=>false, 'show'=>'当前密码错误'));
			if (strlen($_p_user_pw) < 6 or strlen($_p_user_pw) > 20) pe_jsonshow(array('result'=>false, 'show'=>"Yangi parol 6-20 belgidan iborat bo'lishi kerak"));
			if ($_p_user_pw != $_p_user_pw1) pe_jsonshow(array('result'=>false, 'show'=>'Yangi parol va tasdiqlangan parol mos kelmaydi'));
			if ($db->pe_update('user', array('user_id'=>$_s_user_id), array('user_paypw'=>md5($_p_user_pw)))) {
				pe_jsonshow(array('result'=>true, 'show'=>'Oʻzgartirish muvaffaqiyatli'));
			}
			else {
				pe_jsonshow(array('result'=>false, 'show'=>'O‘zgartirish amalga oshmadi'));
			}
		}
		$seo = pe_seo($menutitle="To'lov parolini o'zgartiring");
		include(pe_tpl('setting_paypw.html'));
	break;
	//####################// 个人信息 //####################//
	default:
		$menumark = 'setting_base';
		if (isset($_p_pesubmit)) {
			pe_token_match();
			if ($_p_user_phone) {
				if (!pe_formcheck('phone', $_p_user_phone)) pe_jsonshow(array('result'=>false, 'show'=>"Iltimos, mobil telefon raqamini to'g'ri kiriting"));
				if ($db->pe_num('user', " and `user_phone` = '".pe_dbhold($_p_user_phone)."' and `user_id` != '{$_s_user_id}'")) pe_jsonshow(array('result'=>false, 'show'=>'Telefon raqami allaqachon mavjud'));
			}
			if ($_p_user_email) {
				if (!pe_formcheck('email', $_p_user_email)) pe_jsonshow(array('result'=>false, 'show'=>"Iltimos, to'g'ri elektron pochta manzilini to'ldiring"));
				if ($db->pe_num('user', " and `user_email` = '".pe_dbhold($_p_user_email)."' and `user_id` != '{$_s_user_id}'")) pe_jsonshow(array('result'=>false, 'show'=>'Email allaqachon mavjud'));
			}
			$sql_set['user_tname'] = $_p_user_tname;
			$sql_set['user_phone'] = $_p_user_phone;
			$sql_set['user_email'] = $_p_user_email;
			if ($db->pe_update('user', array('user_id'=>$_s_user_id), pe_dbhold($sql_set))) {
				pe_jsonshow(array('result'=>true, 'show'=>'Oʻzgartirish muvaffaqiyatli'));
			}
			else {
				pe_jsonshow(array('result'=>false, 'show'=>'O‘zgartirish amalga oshmadi'));
			}
		}
		$info = $db->pe_select('user', array('user_id'=>$_s_user_id));				
		$seo = pe_seo($menutitle="Shaxsiy ma'lumot");
		include(pe_tpl('setting_base.html'));
	break;
}
?>