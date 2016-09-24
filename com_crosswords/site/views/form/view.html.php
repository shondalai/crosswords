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
defined('_JEXEC') or die;
jimport ( 'joomla.application.component.view' );

class CrosswordsViewForm extends JViewLegacy {
	
	protected $item;
	protected $params;
	protected $print;
	protected $state;
	protected $user;

	function display($tpl = null) {
		
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$user = JFactory::getUser();
		$model = $this->getModel();
		$categories_model = $this->getModel('categories');
		
		$pathway = $app->getPathway();
		$active = $app->getMenu()->getActive();
		$itemid = CJFunctions::get_active_menu_id();
		
		/********************************** PARAMS *****************************/
		$appparams = JComponentHelper::getParams(CW_APP_NAME);
		$menuParams = new JRegistry;
		
		if ($active) {
		
			$menuParams->loadString($active->params);
		}
		
		$this->params = clone $menuParams;
		$this->params->merge($appparams);
		/********************************** PARAMS *****************************/
		
		if(empty($this->item)){
		
			$id = $app->input->getInt('id', 0);
			
			if($id){
			
				$this->item = $this->get('Item');
			} else {
				
				$item = new stdClass();
				$item->title = $item->description = '';
				$item->id = $item->size = $item->level = $item->catid = 0;
				
				$this->item = $item;
			}
		}
		
		$this->print		= $app->input->getBool('print');
		$this->state		= $this->get('State');
		$this->user			= $user;
		$this->form_view	= true;
		
		$page_heading = JText::_('COM_CROSSWORDS_CROSSWORD_FORM');
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