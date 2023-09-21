<?php
/**
 * @version        $Id: style.php 01 2011-08-13 11:37:09Z maverick $
 * @package        CoreJoomla.Crosswords
 * @subpackage     Components
 * @copyright      Copyright (C) 2009 - 2011 corejoomla.com. All rights reserved.
 * @author         Maverick
 * @link           http://www.corejoomla.com/
 * @license        License GNU General Public License version 2 or later
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined( '_JEXEC' ) or die;

class CrosswordsHelpersStyle {

	public static function load( $site = false, $customTag = true ) {
		$document = Factory::getDocument();
		CJFunctions::add_css_to_document( $document, CJLIB_URI . '/framework/assets/cj.framework.css', $customTag );

		if ( $site )
		{
			CJFunctions::add_css_to_document( $document, CW_MEDIA_URI . 'css/cj.crosswords.min.css', $customTag );
			CJFunctions::add_script( CW_MEDIA_URI . 'js/cj.crosswords.min.js', $customTag );
		}
		else
		{
			CJFunctions::add_css_to_document( $document, Uri::base() . 'components/com_crosswords/assets/css/cj.crosswords.admin.min.css', $customTag );
			CJFunctions::add_script( Uri::base() . 'components/com_crosswords/assets/js/cj.crosswords.admin.min.js', $customTag );
		}

		// 		$headData = $document->getHeadData();
		// 		$scripts = $headData['scripts'];
		// // 		if(\Joomla\CMS\Factory::getUser()->authorise('core.manage'))
		// // 		{
		// // 			var_dump($scripts);
		// // 		}
		// 		foreach ($scripts as $script=>$attribs)
		// 		{
		// 			if(strpos('mootools-core.js', $script) > 0 || strpos('mootools-more.js', $script) > 0)
		// 			{
		// 				unset($scripts[$script]);
		// 			}
		// 		}
		// // 		if(\Joomla\CMS\Factory::getUser()->authorise('core.manage'))
		// // 		{
		// // 			var_dump($scripts);
		// // 		}
		// 		$headData['scripts'] = $scripts;
		// 		$document->setHeadData($headData);
	}

}