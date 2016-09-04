<?php

class HPImport_Gen extends Widget_Abstract_Contents implements Widget_Interface_Do {
    public function __construct($request, $response, $params = NULL) {
        parent::__construct($request, $response, $params);
    }

    public function die_with_json($code,$msg){
        $array = array(
            'ret'=>$code,
            'msg'=>$msg
        );
        die(json_encode($array));
    }

    /**
     * 绑定动作
     *
     * @access public
     * @return void
     */
    public function action() {
        define('MAX_LEN_PER_PROCESS', 1000);
        $request = Typecho_Request::getInstance();

        //SITEMAP_FULL_DIR
        //__TYPECHO_ROOT_DIR__

        @$settings = Helper::options()->plugin('HPSitemap');
        if(!$settings) $this->die_with_json(1001, '未开启Typecho插件');

        $auth_key = $settings->sitemap_auth_key;
        $key = $request->get('_auth','xxx');
        if(empty($auth_key) || empty($key) || $auth_key !== $key){
            $this->die_with_json(false, 'Invalid auth key');
        }

        $sitemap_dir = $settings->sitemap_dir;
        if(empty($sitemap_dir)) $this->die_with_json(1002, '未设置sitemap目录');

        //检查目录环境
        define('SITEMAP_FULL_DIR',__TYPECHO_ROOT_DIR__.'/usr/'.$sitemap_dir);
        if(!is_dir(SITEMAP_FULL_DIR)){
            if(!mkdir(SITEMAP_FULL_DIR)){
                $this->die_with_json(1003,'Mkdir sitemap dir failed: '.SITEMAP_FULL_DIR);
            }
        }


        $db = Typecho_Db::get();

        $content_query= $db->select('cid, modified as last_modified')
            ->from('table.content_source')
            ->where('status = ?', 'publish')
            ->where('type =?', 'post');
        $category_query = $db->select('cid, unix_timestamp() as last_modified')
            ->from('table.metas')
            ->where('type =?', 'category');


        $simtemap_file_index = 1;
        $array_sitemap_index = array();

        //生成post的sitemap
        while(true){
            echo 'Generating for file '.$simtemap_file_index."\n";
            $list = $this->fetch_next($db, $content_query, MAX_LEN_PER_PROCESS);
            if(empty($list)) break;
            $sitemap_info = $this->build_sitemap_file_for_sql_items($list,$simtemap_file_index);

            if(empty($sitemap_info)) break;
            $array_sitemap_index[] = $sitemap_info;
            $simtemap_file_index += 1;
        }

        //生成category的sitem
        while(true){
            echo 'Generating for file '.$simtemap_file_index."\n";
            $list = $this->fetch_next($db, $category_query, MAX_LEN_PER_PROCESS);
            if(empty($list)) break;
            $sitemap_info = $this->build_sitemap_file_for_sql_items($list,$simtemap_file_index);

            if(empty($sitemap_info)) break;
            $array_sitemap_index[] = $sitemap_info;
            $simtemap_file_index += 1;
        }

        //生成最后的sitemap_index文件
        $sitemap_index_filename = SITEMAP_FULL_DIR.'sitemap.xml';
        $ret = file_put_contents($sitemap_index_filename,$this->build_site_map_index($array_sitemap_index));
        if(!$ret){
            $this->die_with_json(1006,'Error on generate sitemap index file: '.$sitemap_index_filename);
        }
    }


    function build_post_url($post_id){
        return Typecho_Router::url('post',$post_id);
    }
    function build_category_url($cat_id){
        return Typecho_Router::url('category',$cat_id);
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
                'loc'=>isset($item['cid'])?$this->build_post_url($item['cid']):$this->build_category_url($item['slug']),
                'lastmod'=>gmdate('Y-m-d\TH:i:s+08:00',$item['last_modified'])
            );
        },$list);
        return $result;
    }

    function fetch_next($db,$query,$max_len){
        $list = array();
        while($item = $db->fetchRow($query)){
            $list[] = $item;
            if(count($list) >= $max_len){
                break;
            }
        }
        return $list;

    }

    function build_sitemap_file_for_sql_items($list,$file_index){
        //如果非空，则继续。
        $list = $this->change_sql_list_to_sitemap_format($list);
        $str_xml = $this->build_site_map_xml_content($list);

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
}
?>




