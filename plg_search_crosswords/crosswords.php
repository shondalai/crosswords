<?php
/**
 * @version		$Id: crosswords.php 01 2013-03-30 11:37:09Z maverick $
 * @package		CoreJoomla.crosswords
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2013 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined( '_JEXEC' ) or die();

jimport('joomla.plugin.plugin');

class plgSearchCrosswords extends JPlugin {

	public function __construct(& $subject, $config){
		
		parent::__construct($subject, $config);
	}

	function onSearchAreas(){
		
		return $this->onContentSearchAreas();
	}

	function onSearch($text, $phrase='', $ordering='', $areas=null){
		
		return $this->onContentSearch($text, $phrase, $ordering, $areas);
	}

	function onContentSearchAreas(){
		
		static $areas = array('Crosswords' => 'SEARCH_CROSSWORDS');
		return $areas;
	}

	function onContentSearch($text, $phrase = '', $ordering = '', $areas = null){
		
		$db		= JFactory::getDbo();
		$app	= JFactory::getApplication();
		$user	= JFactory::getUser();
		$lang 	= JFactory::getLanguage();

		$lang->load('plg_search_crosswords', JPATH_ADMINISTRATOR);

		if (is_array($areas)) {
			
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
				
				return array();
			}
		}

		$limit = $app->input->getInt('limit', 50);
		$section = JText::_('SEARCH_CROSSWORDS');
		
		$text = trim($text);
		if ($text == '') return array();
		
		$wheres = array();
		$wheres2 = array();
		$wheres[] = 'a.published = 1';
		
		switch ($phrase) {
			
			case 'exact':
				
				$wheres2[] = 'a.title like \'%'.$db->escape($text).'%\'';
				$wheres2[] = 'a.description like \'%'.$db->escape($text).'%\'';
				
				$wheres[] = '(' . implode(') OR (', $wheres2) . ')';
				
				break;
				
			case 'all':
			case 'any':
			default:
				
				$words  = explode( ' ', $text );
				
				foreach ($words as $word){
					
					$wheres3 = array();
					$wheres3[] = 'a.title like \'%'.$db->escape( $word ).'%\'';
					$wheres3[] = 'a.description like \'%'.$db->escape($word).'%\'';
					
					$wheres2[] = '('.implode(') or (', $wheres3).')';
				}
				
				$wheres[] = '('.implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres2 ).')';
				
				break;
		}

		$where = '('.implode(') and (', $wheres ).')';
		$order = '';
		
		switch ( $ordering ) {
			
			//alphabetic, ascending
			case 'alpha':
				
				$order = 'order by a.title asc';
				break;

				//oldest first
			case 'oldest':
				
				$order = 'order by a.created asc';
				break;
					
				//popular first
			case 'popular':
				
				$order = 'order by a.hits desc';
				break;
					
				//newest first
			case 'newest':
				
				$order = 'order by a.created desc';
				break;
		}

		$rows = array();
		$query = '
				select 
					a.id, a.alias, a.title, a.created, concat_ws( " / ", '. $db->quote($section) .', c.title ) as section, "1" AS browsernav
				from 
					#__crosswords as a
				left join 
					#__categories as c ON c.id = a.catid
				where 
					'. $where .' '.$order;
		
		$db->setQuery( $query, 0, $limit );
		$rows = $db->loadObjectList();

		$menu = $app->getMenu();
		$mnuitem = $menu->getItems('link', 'index.php?option=com_crosswords&view=crosswords', true);
		$ritemid = $app->input->getInt('Itemid');
		$itemid = isset($mnuitem) ? '&Itemid='.$mnuitem->id : (isset($ritemid)?'&Itemid='.$ritemid:'');

		//The 'output' of the displayed link
		if(!empty($rows)){
			
			foreach($rows as $key => $row) {
				
				$rows[$key]->href = 'index.php?option=com_crosswords&view=crosswords&task=view&id='.$row->id.':'.$row->alias.$itemid;
				$rows[$key]->text = $row->title;
			}
		}

		return $rows;
	}
}
