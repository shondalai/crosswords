<?php
/**
 * @version		$Id: categories.php 01 2012-09-20 11:37:09Z maverick $
 * @package		CoreJoomla.crosswords
 * @subpackage	Components.site
 * @copyright	Copyright (C) 2009 - 2013 corejoomla.com, Inc. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die();

jimport('joomla.application.component.modellist');
jimport('joomla.application.categories');

class CrosswordsModelCategories extends JModelList {

	protected $_items;
	protected $_item;
	private $_parent = null;

	function __construct() {

		parent::__construct ();
	}

	public function get_categories($parent = 0, $recursive = false){

		if (empty($this->_items)) {
			
			$app = JFactory::getApplication();
			$menu = $app->getMenu();
			$active = $menu->getActive();
			$params = new JRegistry();

			if ($active) {
				
				$params->loadString($active->params);
			}

			$options = array();
			$options['countItems'] = $params->get('show_cat_num_crosswords_cat', 1) || !$params->get('show_empty_crosswords_cat', 0);
			$options['statefield'] = 'published';
			$categories = JCategories::getInstance('Crosswords', $options);
			$this->_parent = $categories->get($parent);

			if (is_object($this->_parent)) {
				
				$this->_items = $this->_parent->getChildren($recursive);
			}else {
				
				$this->_items = false;
			}
		}

		return $this->_items;
	}
	
	public function get_category($catid){
		
		if (!is_object($this->_item)) {
			
			$app = JFactory::getApplication();
			$menu = $app->getMenu();
			$active = $menu->getActive();
			$params = new JRegistry();

			if ($active) {
				
				$params->loadString($active->params);
			}

			$options = array();
			$options['countItems'] = $params->get('show_cat_num_articles_cat', 1) || !$params->get('show_empty_categories_cat', 0);

			$catid = $catid > 0 ? $catid : 'root';
			$categories = JCategories::getInstance('Crosswords', $options);
			$this->_item = $categories->get($catid);
			
			if (is_object($this->_item)) {
				
				$user	= JFactory::getUser();
				$userId	= $user->get('id');
				$asset	= 'com_content.category.'.$this->_item->id;
			
				if ($user->authorise('core.create', $asset)) {
					
					$this->_item->getParams()->set('access-create', true);
				}
			}
		}
		
		return $this->_item;
	}
	
	public function get_migrated_category($id){
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('id, title, alias, migrate_id')->from('#__crosswords_categories')->where('id = '.$id);
		$db->setQuery($query);
		$category = $db->loadObject();
		
		return $category;
	}
}
?>

