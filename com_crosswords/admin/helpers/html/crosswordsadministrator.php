<?php
/**
 * @version        $Id: helper.php 01 2012-06-30 11:37:09Z maverick $
 * @package        CoreJoomla.Pollss
 * @subpackage     Components
 * @copyright      Copyright (C) 2009 - 2012 corejoomla.com. All rights reserved.
 * @author         Maverick
 * @link           http://www.corejoomla.com/
 * @license        License GNU General Public License version 2 or later
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

defined( '_JEXEC' ) or die;

JLoader::register( 'CrosswordsHelper', JPATH_ADMINISTRATOR . '/components/com_crosswords/helpers/crosswords.php' );

abstract class JHtmlCrosswordsAdministrator {

	public static function association( $id ) {
		// Defaults
		$html = '';

		// Get the associations
		if ( $associations = Associations::getAssociations( 'com_crosswords', '#__crosswords', 'com_crosswords.item', $id ) )
		{
			foreach ( $associations as $tag => $associated )
			{
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated menu items
			$db    = Factory::getDbo();
			$query = $db->getQuery( true )
			            ->select( 'c.*' )
			            ->select( 'l.sef as lang_sef' )
			            ->from( '#__crosswords as c' )
			            ->select( 'cat.title as category_title' )
			            ->join( 'LEFT', '#__categories as cat ON cat.id=c.catid' )
			            ->where( 'c.id IN (' . implode( ',', array_values( $associations ) ) . ')' )
			            ->join( 'LEFT', '#__languages as l ON c.language=l.lang_code' )
			            ->select( 'l.image' )
			            ->select( 'l.title as language_title' );
			$db->setQuery( $query );

			try
			{
				$items = $db->loadObjectList( 'id' );
			}
			catch ( RuntimeException $e )
			{
				throw new Exception( $e->getMessage(), 500 );
			}

			if ( $items )
			{
				foreach ( $items as &$item )
				{
					$text         = strtoupper( $item->lang_sef );
					$url          = Route::_( 'index.php?option=com_crosswords&task=crossword.edit&id=' . (int) $item->id );
					$tooltipParts = [
						HTMLHelper::_( 'image', 'mod_languages/' . $item->image . '.gif',
							$item->language_title,
							[ 'title' => $item->language_title ],
							true
						),
						$item->title,
						'(' . $item->category_title . ')',
					];
					$item->link   = HTMLHelper::_( 'tooltip', implode( ' ', $tooltipParts ), null, null, $text, $url, null,
						'hasTooltip label label-association label-' . $item->lang_sef );
				}
			}

			$html = LayoutHelper::render( 'joomla.content.associations', $items );
		}

		return $html;
	}

	/**
	 * Show the feature/unfeature links
	 *
	 * @param   int      $value      The state value
	 * @param   int      $i          Row number
	 * @param   boolean  $canChange  Is user allowed to change?
	 *
	 * @return  string       HTML code
	 */
	public static function featured( $value = 0, $i, $canChange = true ) {
		if ( APP_VERSION >= 3 )
		{
			HTMLHelper::_( 'bootstrap.tooltip' );
		}

		// Array of image, task, title, action
		$states = [
			0 => [ 'unfeatured', 'crosswords.featured', 'COM_CROSSWORDS_UNFEATURED', 'COM_CROSSWORDS_TOGGLE_TO_FEATURE' ],
			1 => [ 'featured', 'crosswords.unfeatured', 'COM_CROSSWORDS_FEATURED', 'COM_CROSSWORDS_TOGGLE_TO_UNFEATURE' ],
		];
		$state  = ArrayHelper::getValue( $states, (int) $value, $states[1] );
		$icon   = $state[0];

		if ( $canChange )
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip'
			        . ( $value == 1 ? ' active' : '' ) . '" title="' . HTMLHelper::tooltipText( $state[3] ) . '"><i class="icon-'
			        . $icon . '"></i></a>';
		}
		else
		{
			$html = '<a class="btn btn-micro hasTooltip disabled' . ( $value == 1 ? ' active' : '' ) . '" title="' . HTMLHelper::tooltipText( $state[2] ) . '"><i class="icon-'
			        . $icon . '"></i></a>';
		}

		return $html;
	}

}
