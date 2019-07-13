<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2009 - 2018 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();


jimport('joomla.application.component.controller');

class CrosswordsController extends JControllerLegacy 
{
	public function __construct($config = array())
	{
		$this->input = JFactory::getApplication()->input;

		if ($this->input->get('view') === 'crosswords' && $this->input->get('layout') === 'modal')
		{
			JHtml::_('stylesheet', 'system/adminlist.css', array(), true);
			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}

		parent::__construct($config);
	}

	public function display($cachable = false, $urlparams = false)
	{
		$cachable = true;

		$id    = $this->input->getInt('p_id');
		$vName = $this->input->getCmd('view', 'polls');
		$this->input->set('view', $vName);
		
		$doc = JFactory::getDocument();
		
		CjLib::behavior('bscore', array('customtag'=>false));
		CJFunctions::load_jquery(array('libs'=>array('fontawesome')));

		CJFunctions::add_css_to_document($doc, JUri::root(true).'/media/com_crosswords/css/cj.crosswords.min.css', true);
		CJFunctions::add_script_to_document($doc, 'cj.crosswords.min.js', true, JUri::root(true).'/media/com_crosswords/js/');
		
		$safeurlparams = array('catid' => 'INT', 'id' => 'INT', 'cid' => 'ARRAY', 'year' => 'INT', 'month' => 'INT', 'limit' => 'UINT', 'limitstart' => 'UINT',
			'showall' => 'INT', 'return' => 'BASE64', 'filter' => 'STRING', 'filter_order' => 'CMD', 'filter_order_Dir' => 'CMD', 'filter-search' => 'STRING', 'print' => 'BOOLEAN', 'lang' => 'CMD', 'Itemid' => 'INT');

		// Check for edit form.
		if ($vName == 'form' && !$this->checkEditId('com_crosswords.edit.crossword', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			return JError::raiseError(403, JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
		}
// 		/********************************** JQUERY HACKS ********************************************/
// 		$document = JFactory::getDocument();
// 		$headData = $document->getHeadData();
// 		$scripts = $headData['scripts'];
		
// 		if(isset($scripts['/media/system/js/mootools-core.js']))
// 		{
// 			$scripts2['/media/system/js/mootools-core.js'] = $scripts['/media/system/js/mootools-core.js'];
// 			unset($scripts['/media/system/js/mootools-core.js']);
// 		}
		
// 		if(isset($scripts['/media/jui/js/jquery.min.js']))
// 		{
// 			$scripts2['/media/jui/js/jquery.min.js'] = $scripts['/media/jui/js/jquery.min.js'];
// 			unset($scripts['/media/jui/js/jquery.min.js']);
// 		}
		
// 		if(isset($scripts['/media/jui/js/jquery-noconflict.js']))
// 		{
// 			$scripts2['/media/jui/js/jquery-noconflict.js'] = $scripts['/media/jui/js/jquery-noconflict.js'];
// 			unset($scripts['/media/jui/js/jquery-noconflict.js']);
// 		}
			
// 		if(isset($scripts['/media/jui/js/jquery-migrate.min.js']))
// 		{
// 			$scripts2['/media/jui/js/jquery-migrate.min.js'] = $scripts['/media/jui/js/jquery-migrate.min.js'];
// 			unset($scripts['/media/jui/js/jquery-migrate.min.js']);
// 		}
		
// 		unset($scripts['/media/jui/js/bootstrap.min.js']);
		
// 		$scripts = array_merge($scripts2, $scripts);
// 		$headData['scripts'] = $scripts;
// 		$document->setHeadData($headData);
// 		/*********************************** JQUERY HACKS ***********************************************/
		
		parent::display($cachable, $safeurlparams);

		return $this;
	}
}
?>