<?php
if (php_sapi_name() != 'cli'){
    die('');
}

if($argc==2 && $argv[1] == 'update_all') {
    define('IS_UPDATE_ALL',TRUE);
}

$DB_USERNAME = "root";
$DB_PASSWORD = 'your_password';
$DB_NAME = 'your_database_name';
$DB_TABLE_CONTENTS = "typecho_contents";
$DB_TABLE_METAS = 'typecho_metas';

define('SITE_URL','http://your_site_name/');
define('SITE_ROOT',dirname(__FILE__).'/../');
define('SITEMAP_DIR','sitemap/');//直接生成到根目录
define('MAX_LEN_PER_PROCESS',5000);

function build_post_url($post_id){
    return SITE_URL."archives/{$post_id}.html";
}
function build_category_url($cat_id){
    return SITE_URL."category/{$cat_id}/";
}

function build_site_map_xml_content($list){
    $str='<?xml version="1.0" encoding="utf-8"?>';
    $str .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
    foreach($list as $item){
        $str .= "<url>";
        $str .= "<loc>{$item['loc']}</loc>";
        $str .= "<lastmod>{$item['lastmod']}</lastmod>";
        $str .= "<changefreq>daily</changefreq>";
        $str .= "<priority>0.5</priority>";
        $str .= "</url>";
    }
    $str .= "</urlset>";
    return $str;
}

function build_site_map_index($list){
    $str='<?xml version="1.0" encoding="utf-8"?>';
    $str .="<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
    foreach($list as $item){
        $str .="<sitemap>";
        $str.="<loc>{$item['loc']}</loc>";
        $str.="<lastmod>{$item['lastmod']}</lastmod>";
        $str .="</sitemap>";
    }

    $str .="</sitemapindex>";
    return $str;
}

function change_sql_list_to_sitemap_format($list){
    $result = array_map(function($item){
        return array(
            'loc'=>isset($item['cid'])?build_post_url($item['cid']):build_category_url($item['slug']),
            'lastmod'=>gmdate('Y-m-d\TH:i:s+00:00',defined('IS_UPDATE_ALL') && IS_UPDATE_ALL == true?time():$item['last_modified'])
        );
    },$list);
    return $result;
}

function fetch_next($query_res,$max_len){
    $list = array();
    while($item = mysql_fetch_assoc($query_res)){
        $list[] = $item;
        if(count($list) >= $max_len){
            break;
        }
    }
    return $list;

}

function build_sitemap_file_for_sql_items($list,$file_index){
    //如果非空，则继续。
    $list = change_sql_list_to_sitemap_format($list);
    $str_xml = build_site_map_xml_content($list);

    $sitemap_filename = 'sitemap_'.$file_index.'.xml';
    $sitemap_full_filename = SITEMAP_FULL_DIR.$sitemap_filename;
    $ret = file_put_contents($sitemap_full_filename,$str_xml);

    if(!$ret){
        die('Error on generate sitemap file: '.$sitemap_full_filename);
    }else{
        $tmp = array(
            'loc'=>SITE_URL.SITEMAP_DIR.$sitemap_filename,
            'lastmod'=>gmdate('Y-m-d\TH:i:s+00:00',time())
        );
        return $tmp;
    }
}

function build_site_map_files_for_sql($sql,$conn,&$simtemap_file_index){
    $array_sitemap_index = array();
    $res = mysql_query($sql,$conn);
    if(!$res) die('Can not query from db with sql: '.$sql);
    while(true){
        echo 'Generating for file '.$simtemap_file_index."\n";
        $list = fetch_next($res,MAX_LEN_PER_PROCESS);
        if(empty($list)) break;
        $sitemap_info = build_sitemap_file_for_sql_items($list,$simtemap_file_index);

        if(empty($sitemap_info)) break;
        $array_sitemap_index[] = $sitemap_info;
        $simtemap_file_index += 1;
    }
    return $array_sitemap_index;
}



//开始处理
define('SITEMAP_FULL_DIR',SITE_ROOT.SITEMAP_DIR);
if(!is_dir(SITEMAP_FULL_DIR)){
    if(!mkdir(SITEMAP_FULL_DIR)){
        die('Mkdir sitemap dir failed: '.SITEMAP_FULL_DIR);
    }
}

$conn = mysql_connect('127.0.0.1',$DB_USERNAME,$DB_PASSWORD);
if (!$conn) {
    die('Could not connect mysql : ' . mysql_error());
}

if(!mysql_select_db($DB_NAME,$conn)){
    die('Can not select db:'.$DB_NAME);
}


$simtemap_file_index = 1;
$array_sitemap_index = array();
//生成post的索引
$sql = "select cid, modified as last_modified from {$DB_TABLE_CONTENTS} where status = 'publish' and type = 'post'";
$index_list = build_site_map_files_for_sql($sql,$conn,$simtemap_file_index);
$array_sitemap_index = array_merge($array_sitemap_index,$index_list);
//生成category的索引
$sql = "select mid,unix_timestamp() as last_modified,slug from {$DB_TABLE_METAS} where type = 'category'";
$index_list = build_site_map_files_for_sql($sql,$conn,$simtemap_file_index);
$array_sitemap_index = array_merge($array_sitemap_index,$index_list);

//生成最后的sitemap_index文件
$sitemap_index_filename = SITEMAP_FULL_DIR.'sitemap.xml';
$ret = file_put_contents($sitemap_index_filename,build_site_map_index($array_sitemap_index));
if(!$ret){
    die('Error on generate sitemap index file: '.$sitemap_index_filename);
}
echo 'Finished';

