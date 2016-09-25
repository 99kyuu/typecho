<?php

/**
 * 数据导入工具插件。<a href='https://www.typechodev.com/index.php/archives/701/'>需要帮助?<a/>
 * 
 * @category widget
 * @package HPImport
 * @author 雷鬼
 * @version 1.0
 * @link http://www.typechodev.com
 */

class HPImport_Plugin implements Typecho_Plugin_Interface
{
    public static function activate(){
        Helper::addAction('import_one', 'HPImport_Post');
        Helper::addAction('import_excel', 'HPImport_Excel');

    }

    public static function deactivate(){
	    Helper::removeAction('import_one');
        Helper::removeAction('import_excel');

    }
	
    public static function config(Typecho_Widget_Helper_Form $form){

        $import_user_info = new Typecho_Widget_Helper_Form_Element_Text(
            'import_user_info',NULL ,'username/password',
            _t('导入内容所使用的账号信息'),
            _t('设定导入theme/plugin所使用的账号名称和密码，如user/pass,注意用斜杠分割')
        );
        $form->addInput($import_user_info);

        $import_user_auth = new Typecho_Widget_Helper_Form_Element_Text(
            'import_user_auth',NULL ,'',
            _t('设置调用接口时的认证信息'),
            _t('导入post时,请带上?_auth=xxxxx参数,校验通过后才允许导入')
        );
        $form->addInput($import_user_auth);
    }


    public static function personalConfig(Typecho_Widget_Helper_Form $form){

    }

      
}
