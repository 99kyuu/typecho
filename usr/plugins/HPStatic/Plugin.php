<?php

/**
 * 静态化页面，提高服务器性能
 * 
 * @category widget
 * @package HPStatic
 * @author 雷鬼
 * @version 1.0
 * @link http://www.typechodev.com
 */

class HPStatic_Plugin implements Typecho_Plugin_Interface
{

    public static function activate(){
        //创建数据表
        $installDb = Typecho_Db::get();
        $prefix = $installDb->getPrefix();
        $cacheTable = $prefix. 'hpstatic_cache';
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `$cacheTable`(
            `hash`    char(32)  NOT NULL,
            `cache`   Blob      NOT NULL,
            `timestamp` timestamp  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY `hash` (`hash`) 
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 \n
        PARTITION BY KEY(hash) \n
        PARTITIONS 64";
            $installDb->query($sql);
        } catch (Typecho_Db_Exception $e) {
            throw new Typecho_Plugin_Exception('数据表建立失败，插件启用失败。错误信息：'.$sql);

        }

        Typecho_Plugin::factory('index.php')->begin = array('HPStatic_Plugin', 'getCache');
        Typecho_Plugin::factory('index.php')->end = array('HPStatic_Plugin', 'setCache');
    }

    public static function deactivate(){
        $installDb = Typecho_Db::get();
        $installDb->query("DROP TABLE IF EXISTS " . $installDb->getPrefix() .'hpstatic_cache');
    }
	
    public static function config(Typecho_Widget_Helper_Form $form){
        $cache_timeout = new Typecho_Widget_Helper_Form_Element_Text(
            'cache_timeout',NULL ,'3600',
            _t('设置缓存时间'),
            _t('设置缓存的有效期，单位是秒。如果需要手动情况缓存，请手动删除缓存目录下的所有文件')
        );
        $form->addInput($cache_timeout);
    }


    public static function personalConfig(Typecho_Widget_Helper_Form $form){

    }

    public static function getCache(){
        $req = Typecho_Request::getInstance();
        $url = $req->getRequestUri();


        if(!$req->isGet()){ #仅处理GET请求
            return;
        }
        if(strstr($url,'/action/') !== false ){
            #排除action接口,这个一般是特殊接口,所以不需要缓存
            return;
        }

        $db = Typecho_Db::get();

        $hash = md5($url);
        //die($hash);
        $query = $db->select('hash','cache','unix_timestamp(timestamp) as timestamp')
            ->from('table.hpstatic_cache')
            ->where('hash = ?',$hash)
            ->limit(1);
        $result = $db->fetchAll($query);

        @$settings = Helper::options()->plugin('HPStatic');
        if(!$settings) die('未开启Typecho插件，无法获取超时时间');


        $cache_timeout = intval($settings->cache_timeout);
        if($cache_timeout <= 0){
            $cache_timeout = 60; //1min
        }

        if(count($result)>0 && isset($result[0]['timestamp'])){
            $row = $result[0];

            $last_time = $row['timestamp'];

            if(intval(time()) - intval($last_time) <= $cache_timeout){
                $html = gzuncompress($row['cache']);
                var_dump($html);die();
                if($html){
                    echo $html;
                    die();
                }
            }
        }

    }

    public static function setCache(){
        die('sss');
        $req = Typecho_Request::getInstance();
        $url = $req->getRequestUri();
        
        if(!$req->isGet()){ #仅处理GET请求
            return;
        }
        if(strstr($url,'/action/') >=0 ){
            #排除action接口,这个一般是特殊接口,所以不需要缓存
            return;
        }
        

        $db = Typecho_Db::get();
        $html = ob_get_contents();

        $html_gz = addslashes(gzcompress($html));
        if(count($html_gz) >= 64 * 1024){
            #大于64K的内容,也不能插入数据库中,存放不下。
            return;
        }
        $hash = md5($url);
        $prefix = $db->getPrefix();

        $sql = "replace into {$prefix}hpstatic_cache (`hash`,`cache`) values('$hash','$html_gz')";
        $db->query($sql); #仅插入数据库,不判断成功或者失败

    }

      
}
