<?php
/**
 * @copyright   2015-2019 逍遥商城 <http://www.yunziyuan.com.cn>
 * @creatdate   2012-0501 myllop <myllop@gmail.com>
 */
$menumark = 'product';
pe_lead('hook/cache.hook.php');
switch ($act) {
	//####################// 添加 //####################//
	case 'add':
		if (isset($_p_pesubmit)) {
			pe_token_match();
			$info['product_id'] = intval($_g_id);
			$prokey_user = $_p_prokey_user;
			$prokey_pw = $_p_prokey_pw;
			$prokey_edate = $_p_prokey_edate;
			$info['prokey_type'] = 'more';
			$info['prokey_atime'] = time();
			$info['prokey_state'] = 'new';
			if (is_array($prokey_user)){
				foreach ($prokey_user as $k=>$v){
					if($prokey_user[$k]!='' && $prokey_pw[$k]!='' && $prokey_edate[$k]!=''){
						$info['prokey_user'] = $prokey_user[$k];
						$info['prokey_pw'] = $prokey_pw[$k];
						$info['prokey_edate'] = $prokey_edate[$k];
						$db->pe_insert('prokey', pe_dbhold($info));
					}
				}
			echo json_encode(array('result'=>true));
			}
			exit();
		}
		$product_id = intval($_g_id);
		$seo = pe_seo($menutitle='手动添加卡密', '', '', 'admin');
		include(pe_tpl('prokey_add.html','admin'));
	break;
	//####################// 修改 //####################//
	case 'edit':
		$prokey_id = intval($_g_id);
		if (isset($_p_pesubmit)) {
			pe_token_match();

			if ($db->pe_update('prokey', array('prokey_id'=>$prokey_id), pe_dbhold($_p_info))) {
				echo json_encode(array('result'=>true));
				exit();
			}
			else {
				pe_error('修改失败...');
			}
		}
		$info = $db->pe_select('prokey', array('prokey_id'=>$prokey_id));		
		$seo = pe_seo($menutitle='修改卡密', '', '', 'admin');
		include(pe_tpl('prokey_edit.html','admin'));
	break;

	//####################// 删除 //####################//
	case 'del':
		pe_token_match();
		$prokey_id = is_array($_p_prokey_id) ? $_p_prokey_id : intval($_g_id);
		if ($db->pe_delete('prokey', array('prokey_id'=>$prokey_id))) {
			pe_success('删除成功!');
		}
		else {
			pe_error('删除失败...');
		}
	break;

	
	//####################// 批量导入 //####################//
	case 'import':
		$info['product_id'] = intval($_g_id);
		if (isset($_p_pesubmit)) {
			pe_token_match();
			$info['prokey_type'] = 'more';
			$info['prokey_atime'] = time();
			if ($_FILES['data']['size']) {
                pe_lead('public/class/upload.class.php');
				$upload = new upload($_FILES['data'], 'attachment/prokey_import/', array('filename'=>time()));
				header("Content-Type:text/html;charset=utf-8");
				pe_lead('public/class/excel/reader.php');
				$data = new Spreadsheet_Excel_Reader();
				$data->setOutputEncoding('UTF-8');
				$data->read($pe["path_root"].$upload->filehost);
				$rows = intval($data->sheets[0]['numRows'])-1;
				for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
					$info['prokey_user'] = $data->sheets[0]['cells'][$i][1];
					$info['prokey_pw'] = $data->sheets[0]['cells'][$i][2];
					$info['prokey_edate'] = $data->sheets[0]['cells'][$i][3];
					$db->pe_insert('prokey', pe_dbhold($info));
				}
				pe_success('成功导入'.$rows.'条数据!','webadmin.php?mod=prokey&act=list&id='.$info['product_id'],'dialog');
			}
			exit();
		}
		include(pe_tpl('prokey_import.html','admin'));
	break;	

	//####################// 虚拟商品卡密管理 //####################//
	default :
		$product_id = intval($_g_id);
		if (isset($_p_pesubmit)) {
			pe_token_match();
			
	
			if ($db->pe_update('prokey', array('product_id'=>$product_id), pe_dbhold($_p_info))) {
				pe_success('修改成功!', $_g_fromto);
			}
			else {
				pe_error('修改失败...');
			}
		}
		$add['prokey_type'] = 'one';
		$add['prokey_atime'] = time();
		$add['product_id'] = $product_id;

		$tongji['prokey_num'] = $db->pe_num('prokey', array('product_id'=>$product_id));
		if($tongji['prokey_num']<1){
			$db->pe_insert('prokey', pe_dbhold($add));
			$sql = "select * from `".dbpre."prokey` a left join `".dbpre."product` b on a.`product_id` = b.`product_id` where a.product_id=".$product_id;
			$info = $db->sql_select($sql);
			$info_list = array();
		}else{
			$sql = "select a.*,b.product_name from `".dbpre."prokey` a left join `".dbpre."product` b on a.`product_id` = b.`product_id` where a.product_id=".$product_id;
			$info = $db->sql_select($sql);
			$info_list = $db->pe_selectall('prokey',array('product_id'=>$product_id,'prokey_type'=>'more'), '*', array(20, $_g_page));
		}
		//$tongji = $db->index('ad_type')->pe_selectall('prokey', array('group by'=>'ad_type'), 'count(1) as num, ad_type');
		$seo = pe_seo($menutitle='卡密管理', '', '', 'admin');
		include(pe_tpl('prokey_list.html','admin'));
	break;
}




?>