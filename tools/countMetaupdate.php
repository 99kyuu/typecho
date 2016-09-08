<?php

//原始数据的DB
$sourcedata['IP']="127.0.0.1";
$sourcedata['DB']="your_db_name";
$sourcedata['username']="root";
$sourcedata['passwd']="your_db_password";
$relationtable="typecho_relationships";
$metatablename="typecho_metas";


function execsqlcmd($con,$DB,$sqlcmd){
	mysql_select_db($DB, $con);
	$result = mysql_query($sqlcmd);
	return $result;
}


//打开DB
$con = mysql_connect($sourcedata['IP'],$sourcedata['username'],$sourcedata['passwd']);
if (!$con)
{
	die('Could not connect $destdata : ' . mysql_error());
}


//先读取所有meta的数据
$metasqlcmd="SELECT mid from $metatablename;";
$result=execsqlcmd($con,$sourcedata['DB'],$metasqlcmd);
while($row = mysql_fetch_array($result,MYSQL_ASSOC))
{
	echo $row['mid']."-->";

	$countsqlcmd="select count(*) from $relationtable where mid=".$row['mid'];
	$countresult=execsqlcmd($con,$sourcedata['DB'],$countsqlcmd);
	$itemcount=mysql_fetch_array($countresult,MYSQL_ASSOC);
	echo $itemcount['count(*)']."  --> ";

	$updatesqlcmd="update $metatablename set count= ".$itemcount['count(*)']." where mid= ".$row['mid'];
	$updateresult=execsqlcmd($con,$sourcedata['DB'],$updatesqlcmd);
	echo  mysql_affected_rows()."\n";
}


?>