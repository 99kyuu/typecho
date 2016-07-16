<?php
if (php_sapi_name() != 'cli'){
    die('');
}
$DB_USERNAME = "root";
$DB_PASSWORD = 'sunnyzhou123';
$DB_NAME = 'ic';
$DB_TABLE_CONTENTS = "te_contents";
$DB_TABLE_RELATIONSHIPS = "te_relationships";


$con_src = mysql_connect('127.0.0.1',$DB_USERNAME,$DB_PASSWORD);
$con_dst = mysql_connect('127.0.0.1',$DB_USERNAME,$DB_PASSWORD);
if (!$con_src OR !$con_dst) {
    die('Could not connect mysql : ' . mysql_error());
}

mysql_select_db($DB_NAME,$con_src);
mysql_select_db($DB_NAME,$con_dst);

//检查是否存在ext_categories列
$sql = "SELECT column_name FROM information_schema.columns WHERE table_name like '%contents%' AND column_name = 'ext_categories'";
$result = mysql_query($sql,$con_src);
$tmp=mysql_fetch_array($result,MYSQL_ASSOC);
if(empty($tmp)){
    die('ext_categories is not exists in contents table');
}

$sql = "select * from {$DB_TABLE_RELATIONSHIPS} order by cid desc";
$p_relationships = mysql_query($sql,$con_src);

function process_current_array(&$tmp){
	global $con_dst;
	global $DB_TABLE_CONTENTS;
	foreach($tmp as $cid => $arr_mid){
		echo 'Process '.$cid." ...\n";
		if(!isset($arr_mid) || empty($arr_mid)){
			echo "Found empty mids \n";
			continue;
		}else{
			sort($arr_mid);//sort一下，将小的分类放在前面，避免分类太多将ext_categories截断
			$values = join(" ",$arr_mid);
			$sql = "update {$DB_TABLE_CONTENTS} set `ext_categories` = ('$values') where cid = {$cid}";
			$ret  = mysql_query($sql,$con_dst);
			if(!$ret){
				echo "Error on {$cid}:".mysql_error($con_dst);
			}
		}
	}
}

$tmp = array();
$max_len = 10000;
$last_cid = '';
while($arr_relationships = mysql_fetch_assoc($p_relationships)){
    $cid = $arr_relationships['cid'];
    $mid = sprintf("%03d",intval($arr_relationships['mid']));
    if(!isset($tmp[$cid])){
        $tmp[$cid] = array($mid);
    }else{
        array_push($tmp[$cid],$mid);
    }
	
	if($last_cid != $cid && count($tmp) >= $max_len){
		process_current_array($tmp);
		unset($tmp);
		$tmp=array();
	}
	$last_cid = $cid;
}
process_current_array($tmp);

