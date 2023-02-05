<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CrosswordsViewDashboard extends JViewLegacy
{
	protected $state;
	
	public function display ($tpl = null)
	{
		$model = $this->getModel();
		$app = JFactory::getApplication();
		
		$model->setState('list.limit', 5);
		
		$model->setState('list.ordering', 'a.created');
		$this->latest = $model->getItems();
		
		$model->setState('list.ordering', 'a.hits');
		$this->popular = $model->getItems();

		CrosswordsHelper::addSubmenu('dashboard');
		$this->addToolbar();

		$version = $app->getUserState('com_crosswords.version');
		
		if(!$version){
		
			$version = CJFunctions::get_component_update_check('com_crosswords', CW_CURR_VERSION);
			$v = array();
		
			if(!empty($version)){
		
				$v['connect'] = (int)$version['connect'];
				$v['version'] = (string)$version['version'];
				$v['released'] = (string)$version['released'];
				$v['changelog'] = (string)$version['changelog'];
				$v['status'] = (string)$version['status'];
		
				$app->setUserState('com_crosswords.version', $v);
			}
		}
		
		$this->version = $version;
		
		parent::display($tpl);
	}

	protected function addToolbar ()
	{
		$user = JFactory::getUser();
		
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		
		JToolbarHelper::title(JText::_('COM_CROSSWORDS_DASHBOARD'), 'stack dashboard');
		
		if ($user->authorise('core.admin', 'com_crosswords'))
		{
			JToolbarHelper::preferences('com_crosswords');
		}
	}
}
