<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2009 - 2018 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die();

defined( 'CW_APP_NAME' ) or define( 'CW_APP_NAME', 'com_crosswords' );

require_once JPATH_ROOT . '/components/com_cjlib/framework.php';
CJLib::import( 'corejoomla.framework.core' );

require_once JPATH_ROOT . '/components/com_crosswords/helpers/helper.php';
require_once JPATH_ROOT . '/components/com_crosswords/helpers/route.php';
require_once JPATH_ROOT . '/components/com_crosswords/helpers/constants.php';
require_once JPATH_ROOT . '/components/com_crosswords/helpers/style.php';

$controller = JControllerLegacy::getInstance('Crosswords');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
