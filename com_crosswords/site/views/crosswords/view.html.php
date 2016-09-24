<?php
/**
 * @version		$Id: view.html.php 01 2013-01-13 11:37:09Z maverick $
 * @package		CoreJoomla.crosswords
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2013 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die();
jimport ( 'joomla.application.component.view' );

class CrosswordsViewCrosswords extends JViewLegacy {
	
	protected $params;
	protected $print;
	protected $state;
	protected $canDo;
	
	function display($tpl = null) {
		
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$user = JFactory::getUser();
		$model = $this->getModel();
		$categories_model = $this->getModel('categories');
		
		$pathway = $app->getPathway();
		$active = $app->getMenu()->getActive();
		$itemid = CJFunctions::get_active_menu_id();
		
		$this->print = $app->input->getBool('print');
		$page_heading = '';
		
		/********************************** PARAMS *****************************/
		$appparams = JComponentHelper::getParams(CW_APP_NAME);
		$menuParams = new JRegistry;
		
		if ($active) {
		
			$menuParams->loadString($active->params);
		}
		
		$this->params = clone $menuParams;
		$this->params->merge($appparams);
		/********************************** PARAMS *****************************/

		$limit = $this->params->get('list_length', $app->getCfg('list_limit', 20));
		$limitstart = $app->input->getInt('start', 0);
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$catid = $app->input->getInt('id', 0);
		
		if(!$catid){
				
			$menuid = CJFunctions::get_active_menu_id(false);
			$menuparams = $app->getMenu()->getParams( $menuid );
			$catid = (int)$menuparams->get('catid', 0);
			$app->input->set('id', $catid);
		}
		
		$app->input->set('cwcatid', $catid);
		
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		
		if(!empty($this->items)){
		
			$userids = array();
			
			foreach($this->items as $item){
		
				$userids[] = $item->created_by;
			}
			
			$userids = array_unique($userids);
			CJFunctions::load_users($this->params->get('user_avatar'), $userids);
		}
		
		if($this->params->get('display_cat_list', 1) == 1){
				
			$this->categories = $categories_model->get_categories($catid);
		}
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		switch ($this->action){
			
			case 'all':
				$page_heading = JText::_('COM_CROSSWORDS_ALL_CROSSWORDS');
				break;
			case 'latest':
				$page_heading = JText::_('COM_CROSSWORDS_LATEST_CROSSWORDS');
				break;
			case 'popular':
				$page_heading = JText::_('COM_CROSSWORDS_POPULAR_CROSSWORDS');
				break;
			case 'solved':
				$page_heading = JText::_('COM_CROSSWORDS_SOLVED_CROSSWORDS');
				break;
			case 'mycrosswords':
				$page_heading = JText::_('COM_CROSSWORDS_MY_CROSSWORDS');
				break;
			case 'myresponses':
				$page_heading = JText::_('COM_CROSSWORDS_MY_RESPONSES');
				break;
		}
		
		if($catid > 0){
			
			$category = JCategories::getInstance('Crosswords')->get($catid);
			$this->assignRef('category', $category);
			
			if(!empty($category)){

				// breadcrumbs
				if(!in_array($this->action, array('search'))){
					
					$temp = $category;

					while ($temp && $temp->id > 1){
					
						$pathway->addItem($temp->title, JRoute::_('index.php?option='.CW_APP_NAME.'&view=crosswords&task='.$this->action.'&id='.$temp->id.':'.$temp->alias.$itemid));
						$temp = $temp->getParent();
					}
				}
				
				// add to pathway
				$pathway->addItem($page_heading);

				$page_heading = $page_heading . ' - '. $category->title;

				// set browser title
				$this->params->set('page_heading', $this->params->get('page_heading', $page_heading));
			}
		}
		
		$title = $this->params->get('page_title', $app->getCfg('sitename'));
		
		if ($app->getCfg('sitename_pagetitles', 0) == 1) {
				
			$document->setTitle(JText::sprintf('JPAGETITLE', $title, $page_heading));
		} elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
				
			$document->setTitle(JText::sprintf('JPAGETITLE', $page_heading, $title));
		} else {
			
			$document->setTitle($page_heading);
		}
		
		// set meta description
		if ($this->params->get('menu-meta_description')){
				
			$document->setDescription($this->params->get('menu-meta_description'));
		}
		
		// set meta keywords
		if ($this->params->get('menu-meta_keywords')){
				
			$document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		
		// set robots
		if ($this->params->get('robots')){
				
			$document->setMetadata('robots', $this->params->get('robots'));
		}
		
		// set nofollow if it is print
		if ($this->print){
				
			$document->setMetaData('robots', 'noindex, nofollow');
		}
		
		parent::display($tpl);
	}
	
	private function load_users($items){
		
		if(empty($items)) return;
		
		$ids = array();
		
		foreach($items as $item){
			
			$ids[] = $item->created_by;
		}
		
		if(!empty($ids)){
			
			CJFunctions::load_users($this->params->get('user_avatar'), $ids);
		}
	}
}