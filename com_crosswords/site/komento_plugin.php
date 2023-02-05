<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2009 - 2018 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die();


// Always load abstract class
require_once( JPATH_ROOT . '/components/com_komento/komento_plugins/abstract.php' );

class KomentoComcrosswords extends KomentoExtension {

	public $_item;
	public $component = 'com_crosswords';

	public $_map
		= [
			'id'         => 'id',
			'title'      => 'title',
			'hits'       => 'hits',
			'created_by' => 'created_by',
			'catid'      => 'catid',
		];

	public function __construct( $component ) {
		parent::__construct( $component );
		JFactory::getLanguage()->load('com_crosswords');
	}

	public function load( $cid ) {
		static $instances = [];
		if ( ! isset( $instances[$cid] ) )
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery( true )
			            ->select( 'a.id, a.title, a.alias, a.catid, a.created_by, a.hits, a.language' )
			            ->select( 'c.title AS category_title, c.alias AS category_alias, u.name AS author' )
			            ->from( $db->quoteName( '#__crosswords', 'a' ) )
			            ->join( 'left', '#__categories AS c', 'c.id = a.catid' )
			            ->join( 'left', '#__users AS u', 'u.id = a.created_by' )
			            ->where( 'a.id = :id' )
			            ->bind( ':id', $cid );
			$db->setQuery( $query );
			if ( ! $result = $db->loadObject() )
			{
				return false;
			}

			$instances[$cid] = $result;
		}

		$this->_item = $instances[$cid];

		return $this;
	}

	public function getContentIds( $categories = '' ) {
		$db = JFactory::getDbo();

		if ( empty( $categories ) )
		{
			$query = 'select `id` from #__crosswords ORDER BY `id`';
		}
		else
		{
			if ( is_array( $categories ) )
			{
				$categories = implode( ',', $categories );
			}
			$query = 'select `id` from #__crosswords ORDER BY `id` where `catid` in (' . $categories . ') ORDER BY `id`';
		}

		$db->setQuery( $query );

		return $db->loadColumn();
	}

	public function getCategories() {
		$db    = JFactory::getDbo();
		$query = $db->getQuery( true )
		            ->select( 'node.id, node.title, (COUNT(parent.id) - 1) as level, node.parent_id' )
		            ->from( $db->quoteName( '#__categories', 'node' ) )
		            ->from( $db->quoteName( '#__categories', 'parent' ) )
		            ->where( 'node.lft BETWEEN parent.lft AND parent.rgt and node.extension = ' . $db->q( 'com_crosswords' ) )
		            ->group( 'node.id' )
		            ->order( 'node.lft' );
		$db->setQuery( $query );
		$categories = $db->loadObjectList();

		foreach ( $categories as $i => &$row )
		{
			$row->treename = str_repeat( '.&#160;&#160;&#160;', $row->level ) . ( $row->level - 1 > 0 ? '|_&#160;' : '' ) . $row->title;
		}

		return $categories;
	}

	// to determine if is listing view
	public function isListingView() {
		return JFactory::getApplication()->input->getCmd( 'view' ) == 'crosswords';
	}

	// to determine if is entry view
	public function isEntryView() {
		return JFactory::getApplication()->input->getCmd( 'view' ) == 'crossword';
	}

	public function onExecute( &$article, $html, $view, $options = [] ) {
		return $html;
	}

	public function getContentPermalink() {
		// CJLib includes
		$cjlib = JPATH_ROOT . '/components/com_cjlib/framework.php';
		if ( ! file_exists( $cjlib ) )
		{
			return false;
		}

		require_once $cjlib;
		CJLib::import( 'corejoomla.framework.core' );
		require_once JPATH_ROOT . '/components/com_crosswords/helpers/route.php';

		$link = JRoute::_( CrosswordsHelperRoute::getCrosswordRoute( $this->_item->id, $this->_item->catid, $this->_item->language ) );
		$link = $this->prepareLink( $link );

		return $link;
	}

}