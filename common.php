<?php

error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('Asia/Tashkent');
header('Content-Type: text/html; charset=utf-8');
ini_set("session.cookie_httponly", 1);

//#################=====关闭register_globals=====#################//
if (@ini_get('register_globals')) {
	foreach ($_REQUEST as $name => $value) unset($$name);
}

//#################=====引入基本类库=====##############s###//
include(dirname(__FILE__).'/config.php');
include(dirname(__FILE__).'/hook/ini.hook.php');
include(dirname(__FILE__).'/public/class/db.class.php');
include(dirname(__FILE__).'/public/class/cache.class.php');
include(dirname(__FILE__).'/public/class/page.class.php');
include(dirname(__FILE__).'/public/function/global.func.php');
//include(dirname(__FILE__).'/public/function/license.func.php');
include(dirname(__FILE__).'/hook/notice.hook.php');

//#################=====基础设置=====#################//
$pe["host_root"] = pe_root("host");
$pe["path_root"] = pe_root("path");
$module = $mod = $act = "index";
$mod = $_POST["mod"] ? $_POST["mod"] : ($_GET["mod"] ? $_GET["mod"] : $mod);
$act = $_POST["act"] ? $_POST["act"] : ($_GET["act"] ? $_GET["act"] : $act);
$id = $_POST["id"] ? $_POST["id"] : ($_GET["id"] ? $_GET["id"] : $id);

if (trim($_SERVER["PATH_INFO"], "/")) {
    $module = "index";
    $_pathinfo = explode("/", str_ireplace(".html", "", trim($_SERVER["PATH_INFO"], "/")));

    $mod = $_pathinfo[0] ? $_pathinfo[0] : $mod;
    $act = $_pathinfo[1] ? $_pathinfo[1] : $act;
    if ($_pathinfo[1]) {
        $querystr = explode("-", $_pathinfo[1]);
        $querystr[0] && ($act = $querystr[0]);
        $querystr[1] && ($id = $querystr[1]);
    }
} else {
    $module = basename($_SERVER["SCRIPT_NAME"], ".php");
}

//#################=====检测手机模式=====#################//
$pe['mobile'] = pe_mobile();
//if ($pe['mobile'] && in_array($module, array('index', 'user'))) $module = "mobile_{$module}";
$module = "mobile_{$module}";
//#################=====定义模板路径=====#################//
$cache_setting = cache::get('setting');
$module_tpl = is_dir("{$pe['path_root']}template/{$module}/") ? $cache_setting['web_tpl'] : 'default';
$pe['host_tpl'] = "{$pe['host_root']}template/{$module}/";
$pe['path_tpl'] = "{$pe['path_root']}template/{$module}/";
//后台模板路径
$pe['host_admintpl'] = "{$pe['host_root']}admin/template/";
$pe['path_admintpl'] = "{$pe['path_root']}admin/template/";

//#################=====定义GPC变量=====#################//
if (get_magic_quotes_gpc()) {
	!empty($_GET) && extract(pe_trim(pe_stripslashes($_GET)), EXTR_PREFIX_ALL, '_g');
	!empty($_POST) && extract(pe_trim(pe_stripslashes($_POST)), EXTR_PREFIX_ALL, '_p');
}
else {
	!empty($_GET) && extract(pe_trim($_GET),EXTR_PREFIX_ALL,'_g');
	!empty($_POST) && extract(pe_trim($_POST),EXTR_PREFIX_ALL,'_p');
}
session_start();
//pe_setcookie(session_name(), session_id(), 86400);
!empty($_SESSION) && extract(pe_trim($_SESSION),EXTR_PREFIX_ALL,'_s');
!empty($_COOKIE) && extract(pe_trim(pe_stripslashes($_COOKIE)),EXTR_PREFIX_ALL,'_c');
$pe_token = $_s_pe_token;
//分享记录推广用户id
if ($_g_u) pe_setcookie('tguser_id', intval($_g_u));

//#################=====连接数据库开始吧=====#################//
if (stripos($_SERVER['SCRIPT_NAME'], 'install/index.php') === false) {
	$db = new db($pe['db_host'], $pe['db_user'], $pe['db_pw'], $pe['db_name'], $pe['db_coding']);
}
?>