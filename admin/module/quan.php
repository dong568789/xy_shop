<?php
/**
 * @copyright   2015-2019 逍遥商城 <http://www.yunziyuan.com.cn>
 * @creatdate   2012-0501 myllop <myllop@gmail.com>
 */
$menumark = 'quan';
pe_lead('hook/category.hook.php');
pe_lead('hook/product.hook.php');
$category_treelist = category_treelist();
$cache_brand = cache::get('brand');
switch ($act) {
	//####################// 添加优惠券 //####################//
	case 'add':
		if (isset($_p_pesubmit)) {
			pe_token_match();
			$_p_info['quan_atime'] = $_p_info['quan_atime'] ? strtotime($_p_info['quan_atime']) : time();
			$_p_info['quan_num'] = $_p_info['quan_num'] ? $_p_info['quan_num'] : 1;
			$_p_info['product_id'] = _p_product_id ? implode(',',$_p_product_id) : '';

			if ($quan_id = $db->pe_insert('quan', pe_dbhold($_p_info))) {
				if($_p_info['quan_type']=='offline'){
					
					$nums = intval($_p_info['quan_num']);
					$code_length = 10;
					$exist_array = '';
					
					$sql = "insert into `".dbpre."quanlog` (quanlog_atime,quan_id,quan_key,quan_name,quan_money,quan_limit,quan_sdate,quan_edate,product_id) values";
					//生成券码开始
					$characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz";
					$promotion_codes = array();
					for($j = 0 ; $j < $nums; $j++) {
						$code = '';
						for ($i = 0; $i < $code_length; $i++) {
							$code .= $characters[mt_rand(0, strlen($characters)-1)];
						}
						if( !in_array($code,$promotion_codes) ) {
							if( is_array($exist_array) ) {
								if( !in_array($code,$exist_array) ) {//排除已经使用的优惠码
									$sql .= "(".$_p_info['quan_atime'].",".$quan_id.",'".$code."','".$_p_info['quan_name']."',".$_p_info['quan_money'].",".$_p_info['quan_limit'].",'".$_p_info['quan_sdate']."','".$_p_info['quan_edate']."','".$_p_info['product_id']."'),";
								} else {
									$j--;
								}
							} else {
								$sql .= "(".$_p_info['quan_atime'].",".$quan_id.",'".$code."','".$_p_info['quan_name']."',".$_p_info['quan_money'].",".$_p_info['quan_limit'].",'".$_p_info['quan_sdate']."','".$_p_info['quan_edate']."','".$_p_info['product_id']."'),";
							}
						} else {
							$j--;
						}
					}
					//生成过程结束
					$sql = rtrim($sql,",");
					
					$db->query($sql);
					

				}
				pe_success('添加成功!', 'webadmin.php?mod=quan');
			}
			else {
				pe_error('添加失败...');
			}
		}
		$product_list = array();
		$seo = pe_seo($menutitle='添加优惠券', '', '', 'admin');
		include(pe_tpl('quan_add.html','admin'));
	break;
	//####################// 优惠券修改 //####################//
	case 'edit':
		$quan_id = intval($_g_id);
		if (isset($_p_pesubmit)) {
			pe_token_match();
			
			$_p_info['product_id'] = $_p_product_id ? implode(',',$_p_product_id) : '';
			if ($db->pe_update('quan',array('quan_id'=>$quan_id), pe_dbhold($_p_info))) {

				//更新quanlog中未领取券码的数据
				$db->pe_update('quanlog',array('quan_id'=>$quan_id,'user_id'=>0), pe_dbhold($_p_info));
				//统计优惠券数
				$sql1 = " and `quan_id` = ".$quan_id." and `user_id` >0";
				$quan_num_get = $db->pe_num('quanlog', $sql1);
				$sql2 = $sql1 . " and `quanlog_utime`>0";
				$quan_num_use = $db->pe_num('quanlog', $sql2);
				$db->pe_update('quan',array('quan_id'=>$quan_id), array( 'quan_num_get'=>$quan_num_get , 'quan_num_use'=>$quan_num_use));

				pe_success('修改成功!', 'webadmin.php?mod=quan');
			}
			else {
				pe_error('修改失败...');
			}
		}
		//读取关联的限定商品
		$info = $db->pe_select('quan', array('quan_id'=>$quan_id));
		$product_list = array();
		if($info['product_id']){
			$sqlwhere = " and product_id in (".$info['product_id'].")";
			$product_list = $db->pe_selectall('product', $sqlwhere, '*', array(20, $_g_page));
		}
		$seo = pe_seo($menutitle='优惠券文章', '', '', 'admin');
		include(pe_tpl('quan_add.html','admin'));
	break;
	//####################// 优惠券删除 //####################//
	case 'del':
		pe_token_match();
		if ($db->pe_delete('quan', array('quan_id'=>is_array($_p_quan_id) ? $_p_quan_id : $_g_id))) {
			$db->pe_delete('quanlog', array('quan_id'=>is_array($_p_quan_id) ? $_p_quan_id : $_g_id));
			pe_success('删除成功!');
		}
		else {
			pe_error('删除失败...');
		}
	break;
	
	//####################// 追加优惠券 //####################//
	case 'fafang':
		$quan_id = intval($_g_id);
		
		if (isset($_p_pesubmit)) {
			pe_token_match();
			$info = $db->pe_select('quan', array('quan_id'=>$quan_id));
			$info['num'] = $_p_num ? $_p_num : 1;
			
			$info['product_id'] = $info['product_id'] ? $info['product_id'] : '';
			if ($info && intval($_p_num)>0) {
					//生成券码
					// $sql = "insert into `".dbpre."quanlog` (quanlog_atime,quan_id,quan_key,quan_name,quan_money,quan_limit,quan_sdate,quan_edate,product_id) values";
					// for($i=0;$i<intval($info['num']);$i++){
					// 	$randomstr = substr(pe_guid().rand(0,9).rand(0,9),-10)+$i;
					// 	$sql .= "(".$info['quan_atime'].",".$quan_id.",'".$randomstr."','".$info['quan_name']."',".$info['quan_money'].",".$info['quan_limit'].",'".$info['quan_sdate']."','".$info['num']."','".$_p_info['product_id']."'),";
					// 	$randomstr = "";
					// }
					// $sql = substr($sql, 0 ,strlen($sql)-1);


					//生成券码
					$nums = intval($info['num']);
					$code_length = 10;
					$exist_array = '';
					
					$sql = "insert into `".dbpre."quanlog` (quanlog_atime,quan_id,quan_key,quan_name,quan_money,quan_limit,quan_sdate,quan_edate,product_id) values";
					//生成券码开始
					$characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz";
					$promotion_codes = array();
					for($j = 0 ; $j < $nums; $j++) {
						$code = '';
						for ($i = 0; $i < $code_length; $i++) {
							$code .= $characters[mt_rand(0, strlen($characters)-1)];
						}
						if( !in_array($code,$promotion_codes) ) {
							if( is_array($exist_array) ) {
								if( !in_array($code,$exist_array) ) {//排除已经使用的优惠码
									$sql .= "(".$info['quan_atime'].",".$quan_id.",'".$code."','".$info['quan_name']."',".$info['quan_money'].",".$info['quan_limit'].",'".$info['quan_sdate']."','".$info['quan_edate']."','".$info['product_id']."'),";
								} else {
									$j--;
								}
							} else {
								$sql .= "(".$info['quan_atime'].",".$quan_id.",'".$code."','".$info['quan_name']."',".$info['quan_money'].",".$info['quan_limit'].",'".$info['quan_sdate']."','".$info['quan_edate']."','".$info['product_id']."'),";
							}
						} else {
							$j--;
						}
					}
					//生成过程结束
					$sql = rtrim($sql,",");

					if ($db->query($sql)){
						//统计优惠券数
						$quan_num = $db->pe_num('quanlog', array('quan_id'=>$quan_id));
						$sql1 = " and `quan_id` = ".$quan_id." and `user_id` >0";
						$quan_num_get = $db->pe_num('quanlog', $sql1);
						$sql2 = $sql1 . " and `quanlog_utime`>0";
						$quan_num_use = $db->pe_num('quanlog', $sql2);
						$db->pe_update('quan',array('quan_id'=>$quan_id),array('quan_num'=>$quan_num , 'quan_num_get'=>$quan_num_get , 'quan_num_use'=>$quan_num_use));

						pe_success('追加成功！','','dialog');
					}
				
			}
			else {
				pe_error('追加失败...');
			}
		}
		
		$sqlwhere = " and user_id >0 ";
		$sqlwhere .= " and quan_id =".$quan_id;
		$sqlwhere .= " order by `quanlog_id` desc";
		$info = $db->pe_select('quan', array('quan_id'=>$quan_id));
		$info['quan_num_get'] = $db->pe_num('quanlog', $sqlwhere);
		
		$seo = pe_seo($menutitle='追加优惠券', '', '', 'admin');
		include(pe_tpl('quan_fafang.html','admin'));
	break;
	
	//####################// 券码导出 //####################//
	case 'excel_out':
		$quan_id = intval($_g_id);
		if ($_g_excel == '1') {
			$info = $db->pe_select('quan', array('quan_id'=>$quan_id));
			$info_list = $db->pe_selectall('quanlog', array('quan_id'=>$quan_id));
			pe_lead('public/class/excel_out.class.php');	 	 	 	 	 	 	 	 	
			$xls_data[] = array('优惠券名称', '面值', '使用条件', '券码', '领取用户', '使用日期', '状态');
			$info['quan_limit'] = "满".$info['quan_limit']."元";
			foreach($info_list as $k=>$v) {
				$quanlog_state = $v['user_id'] ? '已使用' : '未使用';
				$xls_data[] = array($info['quan_name'],$info['quan_money'], $info['quan_limit'], $v['quan_key'] , $v['user_name'] , $v['quanlog_utime'], $quanlog_state);
			}
			$xls = new excel('UTF-8', false, $info['quan_name']);  
			$xls->addArray($xls_data);  
			$xls->generateXML($info['quan_name']);
		}

	break;	
	
	//####################// 领取日志 //####################//
	case 'mylog':
		$quan_id = intval($_g_id);
		$sqlwhere = " and quan_id =".$quan_id;
		$sqlwhere .= " order by `quanlog_id` desc";
		$info = $db->pe_select('quan', array('quan_id'=>$quan_id));
		$info_list = $db->pe_selectall('quanlog', $sqlwhere, '*', array(30, $_g_page));
		$seo = pe_seo($menutitle='领取日志', '', '', 'admin');
		include(pe_tpl('quan_mylog.html','admin'));
	break;
	

	//####################// 向父窗口添加product //####################//
	case 'product_add':
		$product_id = intval($_g_id);
		if($product_id){
			$info = $db->pe_select('product', array('product_id'=>$product_id));
			$html = "<tr class=\"js_product\" id=\"".$info['product_id']."\"><td>".$info['product_id']."<input type=\"hidden\" name=\"product_id[]\" value=\"".$info['product_id']."\" \/></td><td><img src=\"".pe_thumb($info['product_logo'], 100, 100)."\" width=\"40\" height=\"40\" class=\"imgbg\" \/></td><td class=\"aleft\"><a href=\"".pe_url('product-'.$info['product_id'])."\" target=\"_blank\" class=\"cblue\">".$info['product_name']."</a></td><td><span class=\"corg\">".$info['product_money']."</span></td><td><a href=\"javascript:;\" class=\"admin_btn\">删除</a></td></tr>";
			echo json_encode(array('html'=>$html));
		}else{
			echo json_encode(array('html'=>'null'));
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
		include(pe_tpl('quan_product_list.html','admin'));
	break;
	
	//####################// 列表 //####################//
	default :
		$_g_name && $sqlwhere .= " and `quan_name` like '%{$_g_name}%'";
		$sqlwhere .= " order by `quan_id` desc";
		$info_list = $db->pe_selectall('quan', $sqlwhere, '*', array(20, $_g_page));
		$tongji['all'] = $db->pe_num('quan', $sqlwhere);
		$seo = pe_seo($menutitle='优惠券列表', '', '', 'admin');
		include(pe_tpl('quan_list.html','admin'));
	break;
}
?>