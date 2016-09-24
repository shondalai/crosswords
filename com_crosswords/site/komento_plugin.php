<?php
/**
 * @version		$Id: komento_plugin.php 01 2011-08-13 11:37:09Z maverick $
 * @package		CoreJoomla.gpstools
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2013 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Always load abstract class
require_once( JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_komento'.DIRECTORY_SEPARATOR.'komento_plugins'.DIRECTORY_SEPARATOR.'abstract.php' );

class KomentoComCrosswords extends KomentoExtension{
	
	public $_item;
	public $component = 'com_crosswords';

	public $_map = array(
			'id'            => 'id',
			'title'         => 'title',
			'hits'          => 'hits',
			'created_by'    => 'created_by',
			'catid'         => 'catid'
	);

	public function __construct( $component ){
		
		parent::__construct( $component );
	}
	
	public function load( $cid ){
		
		static $instances = array();

		if( !isset( $instances[$cid] ) ){
			
			$db		= JFactory::getDbo();
			
			$query	= '
					select 
						a.id, a.title, a.alias, a.catid, a.created_by, a.hits,
						c.title AS category_title, c.alias AS category_alias,
						u.name AS author
					from 
						#__crosswords AS a
					left join
						#__categories AS c ON c.id = a.catid
					left join
						#__users AS u ON u.id = a.created_by
					where 
						a.id = ' . $db->quote( (int) $cid );
			
			$db->setQuery( $query );

			if( !$result = $db->loadObject() ){
	
				return false;
			}

			$instances[$cid] = $result;
		}

		$this->_item = $instances[$cid];

		return $this;
	}

	public function getContentIds( $categories = '' ){
		
		$db = JFactory::getDbo();
		$query = '';

		if( empty( $categories ) ){
			
			$query = 'select `id` from #__crosswords ORDER BY `id`';
		} else {
			
			if( is_array( $categories ) ){
				
				$categories = implode( ',', $categories );
			}

			$query = 'select `id` from #__crosswords ORDER BY `id` where `catid` in (' . $categories . ') ORDER BY `id`';
		}

		$db->setQuery( $query );
		
		return $db->loadColumn();
	}

	public function getCategories(){
		
		$db	= JFactory::getDbo();
		$query	= '
				select 
					node.id, node.title, (COUNT(parent.id) - 1) as level, node.parent_id
				from 
					`#__categories` AS node,
					`#__categories` AS parent
				where
					node.lft BETWEEN parent.lft AND parent.rgt and node.extension = '.$db->q('com_crosswords').'
				group by
					node.id
				order by
					node.lft';

		$db->setQuery( $query );
		$categories = $db->loadObjectList();
		
		foreach( $categories as $i=>&$row ){
			
			$row->treename = str_repeat( '.&#160;&#160;&#160;', $row->level ) . ( $row->level - 1 > 0 ? '|_&#160;' : '' ) . $row->title;
		}

		return $categories;
	}

	// to determine if is listing view
	public function isListingView(){
		
		$app = JFactory::getApplication();
		$tasks = array('', 'popular', 'latest', 'solved');
		
		return ($app->input->getCmd('view') == 'crosswords') && (in_array($app->input->getCmd('task'), $tasks));
	}

	// to determine if is entry view
	public function isEntryView(){
		
		$app = JFactory::getApplication();
		
		return ($app->input->getCmd('view', 'crosswords') == 'crosswords') && ($app->input->getCmd('task') == 'view');
	}

	public function onExecute( &$article, $html, $view, $options = array() ){
		
		return $html;
	}
	
	public function getContentPermalink(){
		
		// CJLib includes
		$cjlib = JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_cjlib'.DIRECTORY_SEPARATOR.'framework.php';
		if(file_exists($cjlib)){
		
			require_once $cjlib;
		}else{
		
			die('CJLib (CoreJoomla API Library) component not found. Please download and install it to continue.');
		}
		CJLib::import('corejoomla.framework.core');
		
		$itemid = CJFunctions::get_active_menu_id(true, 'index.php?option=com_crosswords&view=crosswords');
		$slug = $this->_item->alias ? ($this->_item->id.':'.$this->_item->alias) : $this->_item->id;
		$link = JRoute::_('index.php?option=com_crosswords&view=crosswords&task=view&id='.$slug.$itemid);
		$link = $this->prepareLink( $link );
	
		return $link;
	}
}