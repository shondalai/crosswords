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

defined('CW_APP_NAME') or define('CW_APP_NAME', 'com_crosswords');
require_once JPATH_ROOT.'/components/com_cjlib/framework.php';

CJLib::import('corejoomla.framework.core');
CJLib::import('corejoomla.nestedtree.core');

require_once JPATH_ROOT.'/components/com_crosswords/helpers/helper.php';
require_once JPATH_ROOT.'/components/com_crosswords/helpers/constants.php';
require_once JPATH_ROOT.'/components/com_crosswords/helpers/style.php';

$app = JFactory::getApplication();
$params = JComponentHelper::getParams('com_crosswords');
$view = $app->input->getCmd('view', 'crosswords');

if($params->get('enable_bootstrap', 1) == 1)
{
	JHtml::_('behavior.framework');
	CJLib::import('corejoomla.ui.bootstrap');
}

if( JFile::exists(JPATH_COMPONENT.'/controllers/'.$view.'.php') )
{
	CrosswordsHelpersStyle::load(true);

	require_once (JPATH_COMPONENT.'/controllers/'.$view.'.php');
	$classname = 'CrosswordsController' . JString::ucfirst($view);
	$controller = new $classname;
}
else
{
	$controller = JControllerLegacy::getInstance('Crosswords');
}

/********************************** JQUERY HACKS ********************************************/
$document = JFactory::getDocument();
$headData = $document->getHeadData();
$scripts = $headData['scripts'];
if(!empty($scripts))
{
	unset($scripts['/media/system/js/mootools-core.js']);
	unset($scripts['/media/system/js/mootools-more.js']);
	$headData['scripts'] = $scripts;
	$document->setHeadData($headData);
}
/*********************************** JQUERY HACKS ***********************************************/

$controller->execute( $app->input->getCmd('task'));
$controller->redirect();