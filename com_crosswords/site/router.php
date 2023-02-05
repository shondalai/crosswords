<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2021 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die;

use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;

class CrosswordsRouter extends JComponentRouterView {

	protected $noIDs = false;

	/**
	 * CjForum Component router constructor
	 *
	 * @param   JApplicationCms  $app   The application object
	 * @param   JMenu            $menu  The menu object to work with
	 *
	 * @since 4.0.0
	 */
	public function __construct( $app = null, $menu = null ) {
		$params      = JComponentHelper::getParams( 'com_crosswords' );
		$this->noIDs = (bool) $params->get( 'sef_ids' );

		$categories = new RouterViewConfiguration( 'categories' );
		$categories->setKey( 'id' );
		$this->registerView( $categories );

		$category = new RouterViewConfiguration( 'category' );
		$category->setKey( 'id' )->setParent( $categories, 'catid' )->setNestable();
		$this->registerView( $category );

		$topic = new RouterViewConfiguration( 'crossword' );
		$topic->setKey( 'id' )->setParent( $category, 'catid' );
		$this->registerView( $topic );

		$form = new RouterViewConfiguration( 'form' );
		$this->registerView( $form );

		$this->registerView(new RouterViewConfiguration('crosswords'));

		parent::__construct( $app, $menu );

		$this->attachRule( new MenuRules( $this ) );
		$this->attachRule( new StandardRules( $this ) );
		$this->attachRule( new NomenuRules( $this ) );
	}

	/**
	 * Method to get the segment(s) for a category
	 *
	 * @param   string  $id     ID of the category to retrieve the segments for
	 * @param   array   $query  The request that is build right now
	 *
	 * @return  array|string  The segments of this item
	 *
	 * @since 4.0.0
	 */
	public function getCategorySegment( $id, $query ) {
		$category = JCategories::getInstance( $this->getName() )->get( $id );
		if ( $category )
		{
			$path    = array_reverse( $category->getPath(), true );
			$path[0] = '1:root';

			if ( $this->noIDs )
			{
				foreach ( $path as &$segment )
				{
					[ $id, $segment ] = explode( ':', $segment, 2 );
				}
			}

			return $path;
		}

		return [];
	}

	/**
	 * Method to get the segment(s) for categories
	 *
	 * @param   string  $id     ID of the category to retrieve the segments for
	 * @param   array   $query  The request that is build right now
	 *
	 * @return  array|string  The segments of this item
	 *
	 * @since 4.0.0
	 */
	public function getCategoriesSegment( $id, $query ) {
		return $this->getCategorySegment( $id, $query );
	}

	/**
	 * Method to get the segment(s) for an topic
	 *
	 * @param   string  $id     ID of the topic to retrieve the segments for
	 * @param   array   $query  The request that is build right now
	 *
	 * @return  array|string  The segments of this item
	 *
	 * @since 4.0.0
	 */
	public function getCrosswordSegment( $id, $query ) {
		if ( ! strpos( $id, ':' ) )
		{
			$db      = \JFactory::getDbo();
			$dbquery = $db->getQuery( true );
			$dbquery->select( $dbquery->qn( 'alias' ) )
			        ->from( $dbquery->qn( '#__crosswords' ) )
			        ->where( 'id = ' . $dbquery->q( (int) $id ) );
			$db->setQuery( $dbquery );

			$id .= ':' . $db->loadResult();
		}

		if ( $this->noIDs )
		{
			[ $void, $segment ] = explode( ':', $id, 2 );

			return [ $void => $segment ];
		}

		return [ (int) $id => $id ];
	}

	/**
	 * Method to get the id for a category
	 *
	 * @param   string  $segment  Segment to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 *
	 * @since 4.0.0
	 */
	public function getCategoryId( $segment, $query ) {
		if ( isset( $query['id'] ) )
		{
			$category = \JCategories::getInstance( $this->getName(), [ 'access' => false ] )->get( $query['id'] );

			if ( $category )
			{
				foreach ( $category->getChildren() as $child )
				{
					if ( $this->noIDs )
					{
						if ( $child->alias == $segment )
						{
							return $child->id;
						}
					}
					else
					{
						if ( $child->id == (int) $segment )
						{
							return $child->id;
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Method to get the segment(s) for a category
	 *
	 * @param   string  $segment  Segment to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 *
	 * @since 4.0.0
	 */
	public function getCategoriesId( $segment, $query ) {
		return $this->getCategoryId( $segment, $query );
	}

	/**
	 * Method to get the segment(s) for an topic
	 *
	 * @param   string  $segment  Segment of the topic to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 *
	 * @since 4.0.0
	 */
	public function getCrosswordId( $segment, $query ) {
		if ( $this->noIDs )
		{
			$db      = \JFactory::getDbo();
			$dbquery = $db->getQuery( true );
			$dbquery->select( $dbquery->qn( 'id' ) )
			        ->from( $dbquery->qn( '#__crosswords' ) )
			        ->where( 'alias = ' . $dbquery->q( $segment ) )
			        ->where( 'catid = ' . $dbquery->q( $query['id'] ) );
			$db->setQuery( $dbquery );

			return (int) $db->loadResult();
		}

		return (int) $segment;
	}

}