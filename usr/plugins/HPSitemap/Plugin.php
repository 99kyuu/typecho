<?php

/**
 * 数据导入工具插件
 * 
 * @category widget
 * @package HPImport
 * @author lgl
 * @version 0.1
 * @link http://www.typechodev.com
 */

class HPSite_Plugin implements Typecho_Plugin_Interface
{
    public static function activate(){
        Helper::addAction('gen_sitemap', 'HPSitemap_Gen');

    }

    public static function deactivate(){
	    Helper::removeAction('gen_sitemap');

    }
	
    public static function config(Typecho_Widget_Helper_Form $form){

        $sitemap_dir = new Typecho_Widget_Helper_Form_Element_Text(
            'sitemap_dir',NULL ,'sitemap',
            _t('设置生成sitemap的目录,根目录是usr目录。'),
            _t('譬如设置sitemap,则会在usr/sitemap/下生成sitemap.xml,请保证此目录可访问')
        );
        $form->addInput($sitemap_dir);

        $import_user_auth = new Typecho_Widget_Helper_Form_Element_Text(
            'sitemap_user_auth',NULL ,'',
            _t('设置调用接口时的认证信息'),
            _t('调用接口时,请带上?_auth=xxxxx参数,校验通过后才允许导入')
        );
        $form->addInput($import_user_auth);

    }


    public static function personalConfig(Typecho_Widget_Helper_Form $form){

    }

      
}
