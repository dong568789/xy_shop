<?php
/**
 * @copyright   2015-2019 逍遥商城 <http://www.yunziyuan.com.cn>
 * @creatdate   2012-0501 myllop <myllop@gmail.com>
 */
include('../common.php');
$adminmenu[1] = array(
	'headnav' => '商品中心',
	'subnav' => array(
		array('name' => '商品列表', 'menumark' => 'product', 'url' => 'webadmin.php?mod=product'),
		array('name' => '商品分类', 'menumark' => 'category', 'url' => 'webadmin.php?mod=category'),
		array('name' => '品牌管理', 'menumark' => 'brand', 'url' => 'webadmin.php?mod=brand'),
		array('name' => '规格管理', 'menumark' => 'rule', 'url' => 'webadmin.php?mod=rule'),
		array('name' => '评价管理', 'menumark' => 'comment', 'url' => 'webadmin.php?mod=comment')
	)
);
$adminmenu[3] = array(
	'headnav' => '交易中心',
	'subnav' => array(
	    array('name' => '回购订单', 'menumark' => 'recycle', 'url' => 'webadmin.php?mod=recycle'),
		array('name' => '订单列表', 'menumark' => 'order', 'url' => 'webadmin.php?mod=order'),
		array('name' => '退款/退货', 'menumark' => 'refund', 'url' => 'webadmin.php?mod=refund'),
		array('name' => '资金明细', 'menumark' => 'moneylog', 'url' => 'webadmin.php?mod=moneylog'),
		array('name' => '积分明细', 'menumark' => 'pointlog', 'url' => 'webadmin.php?mod=pointlog'),
		array('name' => '充值记录', 'menumark' => 'order_pay', 'url' => 'webadmin.php?mod=order_pay'),
		array('name' => '提现管理', 'menumark' => 'cashout', 'url' => 'webadmin.php?mod=cashout')
	)
);
$adminmenu[5] = array(
	'headnav' => '营销中心',
	'subnav' => array(
		array('name' => '优惠券', 'menumark' => 'quan', 'url' => 'webadmin.php?mod=quan'),
		array('name' => '限时折扣', 'menumark' => 'zhekou', 'url' => 'webadmin.php?mod=zhekou'),
		array('name' => '限时拼团', 'menumark' => 'pintuan', 'url' => 'webadmin.php?mod=pintuan')
	)
);
$adminmenu[4] = array(
	'headnav' => '用户中心',
	'subnav' => array(
		array('name' => '会员列表', 'menumark' => 'user', 'url' => 'webadmin.php?mod=user'),
		array('name' => '会员等级', 'menumark' => 'userlevel', 'url' => 'webadmin.php?mod=userlevel'),
		array('name' => '管理帐号', 'menumark' => 'admin', 'url' => 'webadmin.php?mod=admin'),
		array('name' => '管理权限', 'menumark' => 'adminlevel', 'url' => 'webadmin.php?mod=adminlevel')
	)
);
$adminmenu[2] = array(
	'headnav' => '文章中心',
	'subnav' => array(
		array('name' => '文章分类', 'menumark' => 'class', 'url' => 'webadmin.php?mod=class'),
		array('name' => '文章列表', 'menumark' => 'article', 'url' => 'webadmin.php?mod=article')
	)
);
$adminmenu[6] = array(
	'headnav' => '控制面板',
	'subnav' => array(
		array('name' => '网站设置', 'menumark' => 'setting', 'url' => 'webadmin.php?mod=setting&act=base'),
		array('name' => '微信设置', 'menumark' => 'wechat', 'url' => 'webadmin.php?mod=wechat&act=base'),
		array('name' => '支付设置', 'menumark' => 'payment', 'url' => 'webadmin.php?mod=payment'),
		array('name' => '导航管理', 'menumark' => 'menu', 'url' => 'webadmin.php?mod=menu'),
		array('name' => '广告管理', 'menumark' => 'ad', 'url' => 'webadmin.php?mod=ad'),
		array('name' => '友情链接', 'menumark' => 'link', 'url' => 'webadmin.php?mod=link')
	)
);

$admin = pe_login('admin');
if ($admin['admin_id'] && $mod == 'do' && $act != 'logout') pe_goto('webadmin.php');
if (!$admin['admin_id'] && $mod != 'do') pe_goto('webadmin.php?mod=do&act=login');

//检测管理权限
$cache_adminlevel = cache::get('adminlevel');
if (!in_array($mod, array('index', 'do')) && $admin['adminlevel_id'] != 1) {
	$admin_result = false;
	$adminlevel_modact = unserialize($cache_adminlevel[$admin['adminlevel_id']]['adminlevel_modact']);
	$adminlevel_modact = $adminlevel_modact ? $adminlevel_modact : array();
	foreach ($adminlevel_modact as $v) {
		foreach(explode('|', $v) as $vv) {
			if ("{$mod}-{$act}" == $vv or $mod == $vv) $admin_result = true;
		}
	}
	!$admin_result && pe_error('权限不足...');
}
//检测菜单隐藏
$adminlevel_menumark = $cache_adminlevel[$admin['adminlevel_id']]['adminlevel_menumark'];
$adminlevel_menumark = $adminlevel_menumark ? unserialize($adminlevel_menumark) : array();
foreach ($adminmenu as $k=>$v) {
	foreach ($v['subnav'] as $kk=>$vv) {
		if ($admin['adminlevel_id'] == 1 or in_array($vv['menumark'], $adminlevel_menumark)) {
			$adminmenu[$k]['show'] = true;
			$adminmenu[$k]['subnav'][$kk]['show'] = true;
		}
	}
}


if (in_array("{$mod}.php", pe_dirlist("{$pe['path_root']}admin/module/*.php"))) {
	include("{$pe['path_root']}admin/module/{$mod}.php");
}
pe_result();
?>