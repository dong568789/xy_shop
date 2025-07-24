<?php
include('common.php');
$cache_category = cache::get('category');
$cache_category_arr = cache::get('category_arr');
$cache_class = cache::get('class');
$cache_class_arr = cache::get('class_arr');
$cache_userlevel = cache::get('userlevel');
$cache_menu = cache::get('menu');
pe_lead('hook/user.hook.php');
pe_lead('hook/wechat.hook.php');

$user = pe_login('user');
if (preg_match("/^http(s)?:\\/\\/.+/",$user['user_logo'])){
	$user_logo = $user['user_logo'];
}else{
	$user_logo = pe_thumb($user['user_logo'], '_120', '_120', 'avatar');
}

if ($user['user_id'] && $mod == 'do' && $act != 'logout') pe_goto('user.php');
if (!$user['user_id'] && $mod != 'do') pe_goto('user.php?mod=do&act=login');

if (in_array("{$mod}.php", pe_dirlist("{$pe['path_root']}module/{$module}/*.php"))) {
	include("{$pe['path_root']}module/{$module}/{$mod}.php");
}
pe_result();
?>