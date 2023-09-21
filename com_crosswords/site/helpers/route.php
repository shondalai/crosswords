<?php
/**
 * @version        $Id: route.php 01 2014-01-26 11:37:09Z maverick $
 * @package        CoreJoomla.crosswords
 * @subpackage     Components
 * @copyright      Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @author         Maverick
 * @link           http://www.corejoomla.com/
 * @license        License GNU General Public License version 2 or later
 */

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;

defined( '_JEXEC' ) or die;

abstract class CrosswordsHelperRoute {

	protected static $lookup = [];
	protected static $lang_lookup = [];

	public static function getCrosswordsRoute( $id = null, $language = 0 ) {
		$needles = [ 'questions' => [ 0 ] ];
		$link    = 'index.php?option=com_crosswords&view=crosswords';

		if ( $language && $language != "*" && Multilanguage::isEnabled() )
		{
			self::buildLanguageLookup();

			if ( isset( self::$lang_lookup[$language] ) )
			{
				$link                .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}

		if ( $item = self::_findItem( $needles ) )
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	public static function getCrosswordRoute( $id, $catid = 0, $language = 0 ) {
		$needles = [ 'crossword' => [ (int) $id ] ];

		//Create the link
		$link = 'index.php?option=com_crosswords&view=crossword&id=' . $id;

		if ( (int) $catid > 1 )
		{
			$categories = Categories::getInstance( 'Crosswords' );
			$category   = $categories->get( (int) $catid );

			if ( $category )
			{
				$needles['category']   = array_reverse( $category->getPath() );
				$needles['categories'] = $needles['category'];
				$link                  .= '&catid=' . $catid;
			}
		}

		if ( $language && $language != "*" && Multilanguage::isEnabled() )
		{
			self::buildLanguageLookup();

			if ( isset( self::$lang_lookup[$language] ) )
			{
				$link                .= '&lang=' . self::$lang_lookup[$language];
				$needles['language'] = $language;
			}
		}

		if ( $item = self::_findItem( $needles ) )
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	public static function getCategoryRoute( $catid, $language = 0 ) {
		if ( $catid instanceof CategoryNode )
		{
			$id       = $catid->id;
			$category = $catid;
		}
		else
		{
			$id       = (int) $catid;
			$category = Categories::getInstance( 'Crosswords' )->get( $id );
		}

		if ( $id < 1 || ! ( $category instanceof CategoryNode ) )
		{
			$link = '';
		}
		else
		{
			$needles = [];

			$link = 'index.php?option=com_crosswords&view=category&id=' . $id;

			$catids                = array_reverse( $category->getPath() );
			$needles['category']   = $catids;
			$needles['categories'] = $catids;

			if ( $language && $language != "*" && Multilanguage::isEnabled() )
			{
				self::buildLanguageLookup();

				if ( isset( self::$lang_lookup[$language] ) )
				{
					$link                .= '&lang=' . self::$lang_lookup[$language];
					$needles['language'] = $language;
				}
			}

			if ( $item = self::_findItem( $needles ) )
			{
				$link .= '&Itemid=' . $item;
			}
		}

		return $link;
	}

	public static function getFormRoute( $id = 0 ) {
		//Create the link
		if ( $id )
		{
			$link = 'index.php?option=com_crosswords&task=crossword.edit&p_id=' . $id;
		}
		else
		{
			$link = 'index.php?option=com_crosswords&task=crossword.add';
		}

		return $link;
	}

	protected static function buildLanguageLookup() {
		if ( count( self::$lang_lookup ) == 0 )
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery( true )
			            ->select( 'a.sef AS sef' )
			            ->select( 'a.lang_code AS lang_code' )
			            ->from( '#__languages AS a' );

			$db->setQuery( $query );
			$langs = $db->loadObjectList();

			foreach ( $langs as $lang )
			{
				self::$lang_lookup[$lang->lang_code] = $lang->sef;
			}
		}
	}

	protected static function _findItem( $needles = null ) {
		$app      = Factory::getApplication();
		$menus    = $app->getMenu( 'site' );
		$language = isset( $needles['language'] ) ? $needles['language'] : '*';

		// Prepare the reverse lookup array.
		if ( ! isset( self::$lookup[$language] ) )
		{
			self::$lookup[$language] = [];

			$component = ComponentHelper::getComponent( 'com_crosswords' );

			$attributes = [ 'component_id' ];
			$values     = [ $component->id ];

			if ( $language != '*' )
			{
				$attributes[] = 'language';
				$values[]     = [ $needles['language'], '*' ];
			}

			$items = $menus->getItems( $attributes, $values );

			foreach ( $items as $item )
			{
				if ( isset( $item->query ) && isset( $item->query['view'] ) )
				{
					$view = $item->query['view'];

					if ( ! isset( self::$lookup[$language][$view] ) )
					{
						self::$lookup[$language][$view] = [];
					}

					if ( isset( $item->query['id'] ) )
					{

						// here it will become a bit tricky
						// language != * can override existing entries
						// language == * cannot override existing entries
						if ( ! isset( self::$lookup[$language][$view][$item->query['id']] ) || $item->language != '*' )
						{
							self::$lookup[$language][$view][$item->query['id']] = $item->id;
						}
					}
					elseif ( $view == 'crosswords' )
					{
						// may be the crosswords list menu item
						if ( ! isset( self::$lookup[$language][$view][0] ) || $item->language != '*' )
						{
							self::$lookup[$language][$view][0] = $item->id;
						}
					}
				}
			}
		}

		if ( $needles )
		{
			foreach ( $needles as $view => $ids )
			{
				if ( isset( self::$lookup[$language][$view] ) )
				{
					foreach ( $ids as $id )
					{
						if ( isset( self::$lookup[$language][$view][(int) $id] ) )
						{
							return self::$lookup[$language][$view][(int) $id];
						}
					}

					// no menu item found. return the home layout menu item.
					if ( isset( self::$lookup[$language][$view][0] ) && self::$lookup[$language][$view][0] )
					{
						return self::$lookup[$language][$view][0];
					}
				}
			}
		}

		// Check if the active menuitem matches the requested language
		$active = $menus->getActive();
		if ( $active && $active->component == 'com_crosswords'
		     && ( $language == '*' || in_array( $active->language, [ '*', $language ] )
		          || ! Multilanguage::isEnabled() ) )
		{
			return $active->id;
		}

		// check if crosswords menu item exists, if yes, return it
		if ( isset( self::$lookup[$language]['crosswords'][0] ) )
		{
			return self::$lookup[$language]['crosswords'][0];
		}

		// If not found, return language specific home link
		$default = $menus->getDefault( $language );

		return ! empty( $default->id ) ? $default->id : null;
	}

}
