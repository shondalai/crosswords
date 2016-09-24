<?php
/**
 * @version		$Id: crosswords.php 01 2011-08-13 11:37:09Z maverick $
 * @package		CoreJoomla.Crosswords
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2011 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class CrosswordsModelCrosswords extends JModelList {

	public function __construct($config = array()){
		
		if (empty($config['filter_fields'])) {
			
			$config['filter_fields'] = array(
					'id', 'a.id',
					'title', 'a.title',
					'alias', 'a.alias',
					'catid', 'a.catid', 'category_title',
					'state', 'a.published',
					'created', 'a.created',
					'created_by', 'a.created_by', 'user_name',
					'hits', 'a.hits',
					'questions', 'a.questions',
					'rows', 'a.rows',
					'columns', 'a.columns',
					'solved', 'a.solved'
			);
		}
		
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null) {
		
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
	
		if ($layout = $app->input->get('layout')) {
			
			$this->context .= '.'.$layout;
		}
	
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
	
		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', 1);
		$this->setState('filter.published', $published);
	
		$categoryId = $this->getUserStateFromRequest($this->context.'.filter.category_id', 'id');
		$this->setState('filter.category_id', $categoryId);
	
		$language = $this->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);
		
		$task = $app->input->getCmd('task');
		
		switch ($task){
			case 'popular':
				$ordering = 'a.hits';
				break;
			case 'solved':
				$ordering = 'a.solved';
				break;
			case 'mycrosswords':
				$this->setState('filter.author_id', JFactory::getUser()->id);
				$ordering = 'a.created';
				break;
			case 'myresponses':
				$this->setState('filter.responder_id', JFactory::getUser()->id);
				$ordering = '';
				break;
			default:
				$ordering = 'a.created';
				break;
		}
		
		// List state information.
		parent::populateState($ordering, $direction);
	}
	
	protected function getStoreId($id = ''){
		
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.category_id');
		$id	.= ':'.$this->getState('filter.author_id');
// 		$id	.= ':'.$this->getState('filter.language');
	
		return parent::getStoreId($id);
	}
	
	protected function _buildQuery(){
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(TRUE);
		
		$query->select('a.id, a.title, a.alias, a.catid, a.description, a.created_by, a.created, a.hits, a.questions, a.rows, a.columns, a.solved, a.published');
		$query->from('#__crosswords as a');
		 
		$query->select('c.title as category_title, c.alias as category_alias');
		$query->leftjoin('#__categories as c on a.catid = c.id');
		 
		$query->select('u.name as user_name, u.email');
		$query->leftJoin('#__users as u on a.created_by = u.id');
		
		return $query;
	}
	
	protected function _buildWhere(&$query) {
		
		$user = JFactory::getUser();
		$authorId = $this->getState('filter.author_id');
		if(is_numeric($authorId)){
			
			$query->where('a.created_by = ' . (int) $authorId);
		}
		
		$categoryId = $this->getState('filter.category_id');
		$authorized_categories = $user->getAuthorisedCategories(CW_APP_NAME, 'core.view');
		
		if(is_array($categoryId)){
		
			$categoryId = array_intersect($categoryId, $authorized_categories);
			
			if(!empty($categoryId)){ 
				
				JArrayHelper::toInteger($categoryId);
				$query->where('a.catid IN ('.implode(',', $categoryId).')');
			}
		} else if($categoryId > 0 && in_array($categoryId, $authorized_categories)){

			$categories = JCategories::getInstance('Crosswords');
			$category = $categories->get($categoryId);
			
			$query->where('c.lft >= '.(int) $category->lft);
			$query->where('c.rgt <= '.(int) $category->rgt);
		} else if(!empty($authorized_categories)){
			
			JArrayHelper::toInteger($authorized_categories);
			$query->where('a.catid IN ('.implode(',', $authorized_categories).')');
		} else {
			
			JArrayHelper::toInteger($authorized_categories);
			$query->where('a.catid IN (-1)');
		}
		
		$responderId = $this->getState('filter.responder_id');
		if(is_numeric($responderId)){
			
			$query->where('a.id in (select cid from #__crosswords_responses where created_by = '.$responderId.' order by created desc)');
		}
		
		if($user->authorise('core.manage', 'com_crosswords'))
		{
			$published = $this->getState('filter.published');
			if (is_numeric($published))
			{
				// Use poll state if badcats.id is null, otherwise, force 0 for unpublished
				$query->where('a.published = ' . (int) $published);
			}
			elseif (is_array($published))
			{
				JArrayHelper::toInteger($published);
				$published = implode(',', $published);
	
				// Use poll state if badcats.id is null, otherwise, force 0 for unpublished
				$query->where('a.published IN (' . $published . ')');
			}
		}
		else 
		{
			$query->where('a.published = 1');
		}
		
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			
			if (stripos($search, 'id:') === 0) {
				
				$query->where('a.id = '.(int) substr($search, 3));
			} elseif (stripos($search, 'author:') === 0) {
				
				$search = $db->Quote('%'.$db->escape(substr($search, 7), true).'%');
				$query->where('(u.name LIKE '.$search.' OR u.username LIKE '.$search.')');
			} else {
				
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('(a.title LIKE '.$search.' OR a.alias LIKE '.$search.')');
			}
		}
	}
	
	protected function getListQuery() {
		
		$db = JFactory::getDbo();
		
		$orderCol	= $this->state->get('list.ordering', '');
		$orderDirn	= $this->state->get('list.direction', 'desc');
		
		$query = $this->_buildQuery();
		$this->_buildWhere($query);
		
		if(!empty($orderCol)){
		
			$query->order($db->escape($orderCol.' '.$orderDirn));
		}
		
		return $query;
	}
	
	public function set_crosswords_status($id, $column, $status){
	
		if(is_array($id)){
	
			$id = implode(',', $id);
		}
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->update($db->quoteName('#__crosswords'))
			->set($db->quoteName($column).' = '.$status)
			->where('id in ('.$id.')');
		$db->setQuery($query);
		
		if(!$db->execute()){

			return false;
		}else{
	
			return true;
		}
	}
	
	public function delete_crosswords($ids){
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$id = implode(',', $ids);
		
		$query
			->delete('#__crosswords')
			->where('id in ('.$id.')');
		
		$db->setQuery($query);
		
		if($db->execute()){
			
			$query = $db->getQuery(true);
			$query->delete('#__crosswords_responses')->where('cid in ('.$id.')');
			$db->setQuery($query);
			$db->execute();
			
			$query = $db->getQuery(true);
			$query->delete('#__crosswords_response_details')->where('crossword_id in ('.$id.')');
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);
			$query->delete('#__crosswords_questions')->where('cid in ('.$id.')');
			$db->setQuery($query);
			$db->execute();
			
// 			$query = $db->getQuery(true);
// 			$query->update($table)

			return true;
		}
		
		return false;
	}
	
	public function get_crossword($id){
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$query
			->select('a.id, a.title, a.alias, a.description, a.catid, a.created_by, a.published')
			->from('#__crosswords as a')
			->where('a.id = '.$id);
		
		$db->setQuery($query);
		$item = $db->loadObject();
		
		return $item;
	}
	
	public function save_crossword($crossword){
		
		$db = JFactory::getDbo();
		
		if($db->updateObject('#__crosswords', $crossword, 'id')) {
			
			return true;
		}
		
		return false;
	}
    
    function get_questions($catid){
    	
    	$db = JFactory::getDbo();
    	$query = $db->getQuery(true);
    	
    	$query->select('id, question')->from('#__crosswords_keywords')->where('catid = '.$catid.' and published = 1');
    	$db->setQuery($query);
    	$questions = $db->loadObjectList();
    	
    	return $questions;
    }

    public function get_admin_emails($groupId){
    
    	if(!$groupId) return false;
    
    	$userids = JAccess::getUsersByGroup($groupId);
    
    	if(empty($userids)) return false;
    
    	$query = 'select email from #__users where id in ('.implode(',', $userids).')';
    	$this->_db->setQuery($query);
    	$users = $this->_db->loadColumn();
    
    	return $users;
    }
}
?>