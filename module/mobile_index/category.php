<?php
switch ($act) {
	
	//####################// abc //####################//
	case 'abc':
		
	break;
	//####################// 商品内容 //####################//
	default:
		//echo var_dump($cache_category_arr[0]);
		$seo = pe_seo('商品分类');
		include(pe_tpl('category.html'));
	break;
}
?>