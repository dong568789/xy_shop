<?php
/**
 * @copyright   2015-2019 逍遥商城 <http://www.yunziyuan.com.cn>
 * @creatdate   2012-0501 myllop <myllop@gmail.com>
 */
$menumark = 'wechat';
pe_lead('hook/cache.hook.php');
switch ($act) {
	
	//####################// 微信设置 //####################//
	case 'base':
		if (isset($_p_pesubmit)) {
			pe_token_match();
			
			$sql = "update `".dbpre."setting` set `setting_value` = case `setting_key` {$sqlset}
				when 'wechat_appid' then '".pe_dbhold($_p_info['wechat_appid'])."'
				when 'wechat_appsecret' then '".pe_dbhold($_p_info['wechat_appsecret'], 'all')."' else `setting_value` end";
			if ($db->sql_update($sql)) {
				cache_write('setting');
				pe_success('设置成功!');
			}
			else {
				pe_error('设置失败...');
			}
		}
		$info = $db->index('setting_key')->pe_selectall('setting');
		$seo = pe_seo($menutitle='微信设置', '', '', 'admin');
		include(pe_tpl('wechat_base.html','admin'));
	break;
	//####################// 网站设置 //####################//
	default:
		echo "你说啥";
	break;
}
?>