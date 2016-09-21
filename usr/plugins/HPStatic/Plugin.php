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
            $installDb->query("CREATE TABLE `$cacheTable.`(
            `hash`    char(32)  NOT NULL,
            `cache`   Blob      NOT NULL,
            `timestamp` timestamp  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY `hash` (`hash`) 
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 \n
        PARTITION BY KEY(hash) \n
        PARTITIONS 64");
        } catch (Typecho_Db_Exception $e) {
            $code = $e->getCode();
            throw new Typecho_Plugin_Exception('数据表建立失败，插件启用失败。错误号：'.$code);
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

    }

    public static function setCache(){

    }

      
}
