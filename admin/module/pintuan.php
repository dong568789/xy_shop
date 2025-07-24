<?php
/**
 * @copyright   2015-2019 逍遥商城 <http://www.yunziyuan.com.cn>
 * @creatdate   2012-0501 myllop <myllop@gmail.com>
 */
$menumark = 'pintuan';
pe_lead('hook/category.hook.php');
pe_lead('hook/product.hook.php');
$category_treelist = category_treelist();
$cache_brand = cache::get('brand');
switch ($act) {
	//####################// 添加拼团 //####################//
	case 'add':
		if (isset($_p_pesubmit)) {
			pe_token_match();

			$_p_info['huodong_atime'] = $_p_huodong_atime ? strtotime($_p_huodong_atime) : time();
			$_p_info['huodong_stime'] = $_p_huodong_stime ? strtotime($_p_huodong_stime) : time();
			$_p_info['huodong_etime'] = $_p_huodong_etime ? strtotime($_p_huodong_etime) : time();
			$_p_info['huodong_type'] = $_p_huodong_type ? $_p_huodong_type : 'pintuan';
			$_p_info['huodong_tag'] = $_p_huodong_tag;

			$huodong_id = $db->pe_insert('huodong', pe_dbhold($_p_info));
			if($huodong_id){
				$info = $db->pe_select('product', array('product_id'=>$_p_product_id));
				$sqlset['huodong_id'] = $huodong_id;
				$sqlset['huodong_tag'] = $_p_info['huodong_tag'];
				$sqlset['huodong_type'] = $_p_info['huodong_type'];
				$sqlset['huodong_stime'] = $_p_info['huodong_stime'];
				$sqlset['huodong_etime'] = $_p_info['huodong_etime'];
				$sqlset['product_id'] = $_p_product_id;
				$sqlset['product_name'] = $info['product_name'];
				$sqlset['product_logo'] = $info['product_logo'];
				$sqlset['product_smoney'] = $info['product_smoney'];
				$sqlset['product_money'] = $_p_product_money;
				$sqlset['product_ptnum'] = $_p_product_ptnum;
				$db->pe_insert('huodongdata', pe_dbhold($sqlset));
			}
			pe_success('添加成功!', 'webadmin.php?mod=pintuan');
		}
		$info_list = array();
		$info = array();
		$info['huodong_tag'] = '限时拼团';
		$info['huodong_stime'] = time();
		$info['huodong_etime'] = time()+604800;
		$seo = pe_seo($menutitle='限时拼团', '', '', 'admin');
		include(pe_tpl('pintuan_add.html','admin'));
	break;
	//####################// 拼团修改 //####################//
	case 'edit':
		$huodong_id = intval($_g_id);
		if (isset($_p_pesubmit)) {
			pe_token_match();
			$_p_info['huodong_atime'] = $_p_huodong_atime ? strtotime($_p_huodong_atime) : time();
			$_p_info['huodong_stime'] = $_p_huodong_stime ? strtotime($_p_huodong_stime) : time();
			$_p_info['huodong_etime'] = $_p_huodong_etime ? strtotime($_p_huodong_etime) : time();
			$_p_info['huodong_type'] = $_p_huodong_type ? $_p_huodong_type : 'pintuan';
			$_p_info['huodong_tag'] = $_p_huodong_tag;

			$db->pe_update('huodong',array('huodong_id'=>$huodong_id), pe_dbhold($_p_info));
			//删除现有的折扣数据
			$db->pe_delete('huodongdata',array('huodong_id'=>$huodong_id));
			$info = $db->pe_select('product', array('product_id'=>$_p_product_id));
				$sqlset['huodong_id'] = $huodong_id;
				$sqlset['huodong_tag'] = $_p_info['huodong_tag'];
				$sqlset['huodong_type'] = $_p_info['huodong_type'];
				$sqlset['huodong_stime'] = $_p_info['huodong_stime'];
				$sqlset['huodong_etime'] = $_p_info['huodong_etime'];
				$sqlset['product_id'] = $_p_product_id;
				$sqlset['product_name'] = $info['product_name'];
				$sqlset['product_logo'] = $info['product_logo'];
				$sqlset['product_smoney'] = $info['product_smoney'];
				$sqlset['product_money'] = $_p_product_money;
				$sqlset['product_ptnum'] = $_p_product_ptnum;
				$db->pe_insert('huodongdata', pe_dbhold($sqlset));
			pe_success('修改成功!', 'webadmin.php?mod=pintuan');
		}
		$info = $db->pe_select('huodong', array('huodong_id'=>$huodong_id));
		//读取关联的限定商品
		//$info_list = $db->pe_selectall('huodongdata', array('huodong_id'=>$huodong_id),'*');
		//$info_list = "{\"info_list\":".json_encode($info_list)."}";
		$seo = pe_seo($menutitle='限时拼团', '', '', 'admin');
		include(pe_tpl('pintuan_add.html','admin'));
	break;
	//####################// 拼团删除 //####################//
	case 'del':
		pe_token_match();
		if ($db->pe_delete('huodong', array('huodong_id'=>is_array($_p_huodong_id) ? $_p_huodong_id : $_g_id))) {
			$db->pe_delete('huodongdata',array('huodong_id'=>is_array($_p_huodong_id) ? $_p_huodong_id : $_g_id));
			pe_success('删除成功!');
		}
		else {
			pe_error('删除失败...');
		}
	break;
	
	//####################// 向父窗口添加product //####################//
	case 'product_info':
		$product_id = intval($_g_id);
		if($product_id){
			$info = $db->pe_select('huodongdata', array('huodong_id'=>$product_id));
			$info_list=json_encode($info);
			echo "{\"info_list\":[".$info_list."]}";
		}
	break;

	

	//####################// 向父窗口添加product //####################//
	case 'product_add':
		$product_id = intval($_g_id);
		if($product_id){
			$info = $db->pe_select('product', array('product_id'=>$product_id));
			$info_list=json_encode($info);
			echo "{\"info_list\":[".$info_list."]}";
		}else{
			echo json_encode(array('info_list'=>'null'));
		}
	break;
	
	
	//####################// 商品列表 //####################//
	case 'product_list':
		$cache_category = cache::get('category');
		$_g_category_id && $sqlwhere .= is_array($category_cidarr = category_cidarr($_g_category_id)) ? " and `category_id` in('".implode("','", $category_cidarr)."')" : " and `category_id` = '{$_g_category_id}'";
		$_g_brand_id && $sqlwhere .= " and `brand_id` = '{$_g_brand_id}'";
		$_g_name && $sqlwhere .= " and `product_name` like '%{$_g_name}%'";
		$sqlwhere .= " and `product_state` = 1";
		$sqlwhere .= " order by `product_order` asc, `product_id` desc";
		$info_list = $db->pe_selectall('product', $sqlwhere, '*', array(30, $_g_page));
		$seo = pe_seo($menutitle='选择商品', '', '', 'admin');
		include(pe_tpl('pintuan_product_list.html','admin'));
	break;
	
	//####################// 列表 //####################//
	default :
		$_g_name && $sqlwhere .= " and b.`huodong_name` like '%{$_g_name}%'";
		$sqlwhere .= " order by b.`huodong_id` desc";
		$sql = "select * from ".dbpre."huodongdata a left join ".dbpre."huodong b on a.huodong_id = b.huodong_id where b.`huodong_type`='pintuan'".$sqlwhere;

		$info_list = $db->sql_selectall($sql,  array(25, $_g_page));
		
		$sqlwhere1 = " and `huodong_type`='pintuan'";
		$_g_name && $sqlwhere1 .= " and `huodong_name` like '%{$_g_name}%'";
		$tongji['all'] = $db->pe_num('huodong', $sqlwhere1);
		
		$seo = pe_seo($menutitle='限时拼团', '', '', 'admin');
		include(pe_tpl('pintuan_list.html','admin'));
	break;
}
?>