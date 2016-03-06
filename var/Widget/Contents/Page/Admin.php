<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 独立页面管理列表
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * 独立页面管理列表组件
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Widget_Contents_Page_Admin extends Widget_Contents_Post_Admin
{
    /**
     * 执行函数
     *
     * @access public
     * @return void
     */
    public function execute()
    {
        /** 过滤状态 */
        $select = $this->select()->where('table.contents_source.type = ? OR (table.contents_source.type = ? AND table.contents_source.parent = ?)', 'page', 'page_draft', 0);

        /** 过滤标题 */
        if (NULL != ($keywords = $this->request->keywords)) {
            $args = array();
            $keywordsList = explode(' ', $keywords);
            $args[] = implode(' OR ', array_fill(0, count($keywordsList), 'table.contents_source.title LIKE ?'));

            foreach ($keywordsList as $keyword) {
                $args[] = '%' . Typecho_Common::filterSearchQuery($keyword) . '%';
            }

            call_user_func_array(array($select, 'where'), $args);
        }

        /** 提交查询 */
        $select->order('table.contents_source.order', Typecho_Db::SORT_ASC);

        $this->db->fetchAll($select, array($this, 'push'));
    }


//    public function size(Typecho_Db_Query $condition)
//    {
//        return $this->db->fetchObject($condition
//            ->select(array('COUNT(DISTINCT table.contents_source.cid)' => 'num'))
//            ->from('table.contents_source')
//            ->cleanAttribute('group'))->num;
//    }


    /**
     * 获取查询对象。覆盖上文，提供一个简单的查询对象
     *
     * @access public
     * @return Typecho_Db_Query
     */
//    public function select()
//    {
//        return $this->db->select('table.contents_source.cid', 'table.contents_source.title', 'table.contents_source.slug', 'table.contents_source.created', 'table.contents_source.authorId',
//            'table.contents_source.modified', 'table.contents_source.type', 'table.contents_source.status', 'table.contents_source.commentsNum', 'table.contents_source.order',
//            'table.contents_source.template', 'table.contents_source.password', 'table.contents_source.allowComment', 'table.contents_source.allowPing', 'table.contents_source.allowFeed',
//            'table.contents_source.parent')->from('table.contents_source');
//    }
}
