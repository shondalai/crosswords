<?php
/**
 * @version		$Id: helper.php 01 2012-06-30 11:37:09Z maverick $
 * @package		CoreJoomla.Pollss
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2012 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;

abstract class JHtmlCrAdministrator
{
	static function featured($value = 0, $i, $canChange = true)
	{
		// Array of image, task, title, action
		$states	= array(
			0	=> array('disabled.png',	'crosswords.featured',	'COM_CROSSWORDS_UNFEATURED',	'COM_CROSSWORDS_TOGGLE_TO_FEATURE'),
			1	=> array('featured.png',		'crosswords.unfeatured',	'COM_CROSSWORDS_FEATURED',		'COM_CROSSWORDS_TOGGLE_TO_UNFEATURE'),
		);
		
		$state	= \Joomla\Utilities\ArrayHelper::getValue($states, (int) $value, $states[1]);
		$html	= JHtml::_('image', 'admin/'.$state[0], JText::_($state[2]), NULL, true);
		
		if ($canChange) {
			
			$html	= '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.JText::_($state[3]).'">'.$html.'</a>';
		}

		return $html;
	}
}
