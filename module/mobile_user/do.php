<?php
pe_lead('hook/wechat.hook.php');
switch($act) {
	//####################// 用户登录 //####################//
	case 'login':
		if (isset($_p_pesubmit)) {
			$user_name = pe_dbhold($_p_user_name);
			$user_pw = md5($_p_user_pw);
			if ($info = $db->pe_select('user', " and (user_name = '{$user_name}' or user_email = '{$user_name}' or user_phone = '{$user_name}') and user_pw = '{$user_pw}'")) {
				user_login_callback('login', $info);
				pe_jsonshow(array('result'=>true, 'show'=>'Kirish muvaffaqiyatli'));
			}
			else {
				pe_jsonshow(array('result'=>false, 'show'=>"Noto'g'ri hisob yoki parol"));
			}
		}
		$seo = pe_seo($menutitle='Foydalanuvchi login');
 		include(pe_tpl('do_login.html'));
	break;
	//####################// 用户退出 //####################//
	case 'logout':
		unset($_SESSION['user_idtoken'], $_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_ltime'], $_SESSION['user_nickname'], $_SESSION['user_wx_openid'], $_SESSION['pe_token']);
		pe_jsonshow(array('result'=>true, 'show'=>'Muvaffaqiyatli chiqish'));
	break;
	//####################// 重置密码 //####################//
	case 'getpw':
		if (isset($_p_pesubmit)) {
			$user_name = pe_dbhold($_p_user_name);
			if (!$user_name) pe_jsonshow(array('result'=>false, 'show'=>'Iltimos, hisob raqamini to ldiring'));
			if (stripos($user_name, '@') === false) {
				$info = $db->pe_select('user', array('user_phone'=>$user_name), 'user_id');
			}
			else {
				$info = $db->pe_select('user', array('user_email'=>$user_name), 'user_id');
			}
			if (!$info['user_id']) pe_jsonshow(array('result'=>false, 'show'=>'Hisob mavjud emas'));
			pe_lead('hook/yzmlog.hook.php');
// 			if (!check_yzm($user_name, $_p_yzm)) pe_jsonshow(array('result'=>false, 'show'=>'验证码错误'));
			if (strlen($_p_user_pw) < 6 or strlen($_p_user_pw) > 20) pe_jsonshow(array('result'=>false, 'show'=>"Yangi parol 6-20 belgidan iborat bo'lishi kerak"));
			if ($_p_user_pw != $_p_user_pw1) pe_jsonshow(array('result'=>false, 'show'=>'Ikkita parol bir-biriga mos kelmaydi'));
			if ($db->pe_update('user', array('user_id'=>$info['user_id']), array('user_pw'=>md5($_p_user_pw)))) {
				pe_jsonshow(array('result'=>true, 'show'=>'Parolni tiklash muvaffaqiyatli'));
			}
			else {
				pe_jsonshow(array('result'=>false, 'show'=>'Parolni tiklash amalga oshmadi'));
			}
		}
		$seo = pe_seo($menutitle='parolni tiklash');
 		include(pe_tpl('do_getpw.html'));
	break;
	//####################// 用户注册 //####################//
	case 'register':
		if (isset($_p_pesubmit)) {
			unset($_SESSION['user_idtoken'], $_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_ltime'], $_SESSION['user_nickname'], $_SESSION['user_wx_openid'], $_SESSION['pe_token']);
			if (mb_strlen($_p_user_name, 'utf8') < 5 or mb_strlen($_p_user_name, 'utf8') > 15) pe_jsonshow(array('result'=>false, 'show'=>'Foydalanuvchi nomi (5-15 belgilar)'));
			if ($db->pe_num('user', array('user_name'=>pe_dbhold($_p_user_name)))) pe_jsonshow(array('result'=>false, 'show'=>'Foydalanuvchi nomi allaqachon mavjud'));
			if (strlen($_p_user_pw) < 6 or strlen($_p_user_pw) > 20) pe_jsonshow(array('result'=>false, 'show'=>'Parol 6-20 belgidan iborat bo lishi kerak'));
			if ($_p_user_pw1 && $_p_user_pw != $_p_user_pw1) pe_jsonshow(array('result'=>false, 'show'=>'Ikkita parol bir-biriga mos kelmaydi'));
			
			if ($_p_reg_type == 'phone') {
				if (!pe_formcheck('phone', $_p_user_phone)) pe_jsonshow(array('result'=>false, 'show'=>'请填写正确的手机号'));
				if ($db->pe_num('user', array('user_phone'=>pe_dbhold($_p_user_phone)))) pe_jsonshow(array('result'=>false, 'show'=>'手机号已存在'));			
				pe_lead('hook/yzmlog.hook.php');
				if ($cache_setting['web_checkphone'] && !check_yzm($_p_user_phone, $_p_phone_yzm)) pe_jsonshow(array('result'=>false, 'show'=>'短信验证码错误'));			
			}
			else {
			    $_p_user_email = $_p_user_name."@xxx.com";
				// if (!pe_formcheck('email', $_p_user_email)) pe_jsonshow(array('result'=>false, 'show'=>'请填写正确的邮箱'));
				if ($db->pe_num('user', array('user_email'=>pe_dbhold($_p_user_email)))) pe_jsonshow(array('result'=>false, 'show'=>'Hisob allaqachon mavjud'));			
				pe_lead('hook/yzmlog.hook.php');
				// if ($cache_setting['web_checkemail'] && !check_yzm($_p_user_email, $_p_email_yzm)) pe_jsonshow(array('result'=>false, 'show'=>'邮箱验证码错误'));
			}
			$sql_set['user_name'] = $_p_user_name;
			$sql_set['user_pw'] = md5($_p_user_pw);
			$sql_set['user_phone'] = $_p_user_phone;
			$sql_set['user_email'] = $_p_user_email;
			$sql_set['user_ip'] = pe_ip();
			$sql_set['user_atime'] = $sql_set['user_ltime'] = time();
			if ($_s_user_wx_openid) $sql_set['user_wx_openid'] = $_s_user_wx_openid;
			if ($user_id = $db->pe_insert('user', pe_dbhold($sql_set))) {
				user_login_callback('reg', $user_id);
				if ($_p_reg_type == 'email') {
					update_yzm($_p_user_email, $_p_email_yzm);
				}
				else {
					update_yzm($_p_user_phone, $_p_phone_yzm);				
				}
				add_moneylog($user_id, 'add', $cache_setting['web_phone'], 'muvaffaqiyatli');
				pe_jsonshow(array('result'=>true, 'show'=>'Roʻyxatdan oʻtish muvaffaqiyatli'));
			}
			else {
				pe_jsonshow(array('result'=>false, 'show'=>'Ro‘yxatdan o‘tish amalga oshmadi'));
			}
		}
		$seo = pe_seo($menutitle='Foydalanuvchini ro yxatdan o tkazish');
 		include(pe_tpl('do_register.html'));
	break;
	

	
}
?>