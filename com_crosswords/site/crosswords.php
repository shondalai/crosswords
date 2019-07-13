<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2009 - 2018 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

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

$controller->execute( $app->input->getCmd('task'));
$controller->redirect();