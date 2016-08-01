<?php
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id: index.php 1153 2009-07-02 10:53:22Z magike.net $
 */

/** HPTypecho自定义配置 */

define('DISABLE_CONTENT_INDEX',false); //true时关闭content表的索引,主要是插入索引。默认是false
define('DISABLE_POST_BY_DATE',true); //关闭按时间归档这个功能,此功能会全表查询,会造成大量的性能浪费

define('OPTIMIZE_ORDER_BY',true); //优化文章查询时的order_by,按时间排序改成按cid倒序
define('OPTIMIZE_PAGE_NAV',true); //优化分页的查询逻辑,避免offset太大时造成性能浪费


//HPTypecho配置结束
//define('__TYPECHO_DEBUG__',TRUE );
/** 载入配置支持 */
if (!defined('__TYPECHO_ROOT_DIR__') && !@include_once 'config.inc.php') {
    file_exists('./install.php') ? header('Location: install.php') : print('Missing Config File');
    exit;
}

/** 初始化组件 */
Typecho_Widget::widget('Widget_Init');

/** 注册一个初始化插件 */
Typecho_Plugin::factory('index.php')->begin();

/** 开始路由分发 */
Typecho_Router::dispatch();

/** 注册一个结束插件 */
Typecho_Plugin::factory('index.php')->end();
