<?php
/**
 * @copyright   2015-2019 逍遥商城 <http://www.yunziyuan.com.cn>
 * @creatdate   2012-0501 myllop <myllop@gmail.com>
 */
$menumark = 'link';
pe_lead('hook/cache.hook.php');
switch ($act) {
	//####################// 链接添加 //####################//
	case 'add':
		if (isset($_p_pesubmit)) {
			pe_token_match();
			stripos($_p_info['link_url'], 'http://') === false && $_p_info['link_url'] = "http://{$_p_info['link_url']}";
			if ($db->pe_insert('link', pe_dbhold($_p_info))) {
				cache_write('link');
				pe_success('添加成功!', 'webadmin.php?mod=link');
			}
			else {
				pe_error('添加失败...');
			}
		}
		$seo = pe_seo($menutitle='添加链接', '', '', 'admin');
		include(pe_tpl('link_add.html','admin'));
	break;
	//####################// 链接修改 //####################//
	case 'edit':
		$link_id = intval($_g_id);
		if (isset($_p_pesubmit)) {
			pe_token_match();
			stripos($_p_info['link_url'], 'http://') === false && $_p_info['link_url'] = "http://{$_p_info['link_url']}";
			if ($db->pe_update('link', array('link_id'=>$link_id), pe_dbhold($_p_info))) {
				cache_write('link');
				pe_success('修改成功!', 'webadmin.php?mod=link');
			}
			else {
				pe_error('修改失败...' );
			}
		}
		$info = $db->pe_select('link', array('link_id'=>$link_id));
		$seo = pe_seo($menutitle='修改链接', '', '', 'admin');
		include(pe_tpl('link_add.html','admin'));
	break;
	//####################// 链接删除 //####################//
	case 'del':
		pe_token_match();
		$link_id = is_array($_p_link_id) ? $_p_link_id : intval($_g_id);
		if ($db->pe_delete('link', array('link_id'=>$link_id))) {
			cache_write('link');
			pe_success('删除成功!');
		}
		else {
			pe_error('删除失败...');
		}
	break;
	//####################// 链接排序 //####################//
	case 'order':
		pe_token_match();
		foreach ($_p_link_order as $k => $v) {
			$result = $db->pe_update('link', array('link_id'=>$k), array('link_order'=>$v));
		}
		if ($result) {
			cache_write('link');
			pe_success('排序成功!');
		}
		else {
			pe_error('排序失败...');
		}
	break;
	//####################// 链接列表 //####################//
	default:
		$info_list = $db->pe_selectall('link', array('order by'=>'`link_order` asc, `link_id` asc'), '*', array(10, $_g_page));
		$tongji['all'] = $db->pe_num('link');
		
		$seo = pe_seo($menutitle='友情链接', '', '', 'admin');
		include(pe_tpl('link_list.html','admin'));
	break;
}
?>