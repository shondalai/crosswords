<?php
/**
 * @version		$Id: communitypolls.php 01 2013-05-10 15:37:09Z maverick $
 * @package		corejoomla.polls
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2013 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;
defined('CW_APP_NAME') or define('CW_APP_NAME', 'com_crosswords');

////////////////////////////////////////// CjLib Includes ///////////////////////////////////////////////
$cjlib = JPATH_ROOT.'/components/com_cjlib/framework.php';
if(file_exists($cjlib))
{
	require_once $cjlib;
}
else
{
	die('CJLib (CoreJoomla API Library) component not found. Please download and install it to continue.');
}
CJLib::import('corejoomla.framework.core');
////////////////////////////////////////// CjLib Includes ///////////////////////////////////////////////

if (!JFactory::getUser()->authorise('core.manage', 'com_crosswords'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

if(APP_VERSION >= 3)
{
	JHtml::_('behavior.tabstate');
}

JLoader::register('CrosswordsHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/crosswords.php');

require_once JPATH_COMPONENT_SITE.'/helpers/constants.php';
require_once JPATH_COMPONENT_SITE.'/helpers/style.php';

JFactory::getLanguage()->load('com_crosswords', JPATH_ROOT);
$controller = JControllerLegacy::getInstance('Crosswords');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();