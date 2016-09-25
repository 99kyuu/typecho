<?php

/**
 * 静态化页面，提高服务器性能。<a href='https://www.typechodev.com/index.php/archives/703/'>需要帮助?</a>
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
        Typecho_Plugin::factory('index.php')->begin = array('HPStatic_Plugin', 'getCache');
        Typecho_Plugin::factory('index.php')->end = array('HPStatic_Plugin', 'setCache');
    }

    public static function deactivate(){
    }
	
    public static function config(Typecho_Widget_Helper_Form $form){
        $cache_dir = new Typecho_Widget_Helper_Form_Element_Text(
            'cache_dir',NULL ,'cache',
            _t('设置缓存目录'),
            _t('/开头表示绝对路径,否则表示相对于usr开始的路径。注意,请自行确保目录可写,并且所在分区inode数量足够。')
        );
        $form->addInput($cache_dir);

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
        if(strstr($url,'/action/') !== false || strstr($url,'/admin/') !== false){
            #排除action接口,这个一般是特殊接口,所以不需要缓存
            return;
        }

        $hash = md5($url);

        @$settings = Helper::options()->plugin('HPStatic');
        if(!$settings) return;


        $cache_timeout = intval($settings->cache_timeout);

        $cache_root = $settings->cache_dir;
        if(strstr($cache_root, '/') !== 0 ){
            $cache_root = __TYPECHO_ROOT_DIR__.'/usr/'.$cache_root;
        }

        if($cache_timeout <= 0){
            $cache_timeout = 60; //1min
        }

        $file = self::hash2dir($hash,$cache_root).$hash.".gz";
        if(file_exists($file)){
            $filetime = filemtime($file);
            if($filetime !== false && time() - $filetime < $cache_timeout){
                $fh = fopen($file, "rb");
                $content = fread($fh,filesize ($file));
                fclose($fh);
                $html = gzuncompress($content);
                echo $html;
                exit(0);
            }
        }else{
            //continue...
        }
    }

    public static function setCache(){
        $req = Typecho_Request::getInstance();
        $url = $req->getRequestUri();
        
        if(!$req->isGet()){ #仅处理GET请求
            return;
        }
        if(strstr($url,'/action/') !== false || strstr($url,'/admin/') !== false){
            #排除action接口,这个一般是特殊接口,所以不需要缓存
            return;
        }

        @$settings = Helper::options()->plugin('HPStatic');
        if(!$settings) return;
        $cache_root = $settings->cache_dir;
        if(strstr($cache_root, '/') !== 0 ){
            $cache_root = __TYPECHO_ROOT_DIR__.'/usr/'.$cache_root;
        }

        $hash = md5($url);
        $file = self::hash2dir($hash,$cache_root).$hash.".gz";
        $dir = dirname($file);

        if(!file_exists($dir)){
            $ret = mkdir($dir,0777,true);
            if(!$ret){
                return;
            }
        }

        $html = ob_get_contents();
        $html_gz = gzcompress($html);
        $fp = fopen($file, 'w');
        fwrite($fp, $html_gz);
        fclose($fp);
    }

    private static function hash2dir($hash,$base_dir=""){
        $dir="";
        for($i = 0; $i < strlen($hash) ; $i+=2){
            $dir = $dir."/".substr($hash, $i, 2);
        }
        return rtrim($base_dir,'/').$dir.'/';
    }
      
}
