<?php
/**
 * @version		$Id: helper.php 01 2012-06-30 11:37:09Z maverick $
 * @package		CoreJoomla.crosswords
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2013 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// No direct access to this file
defined('_JEXEC') or die;

abstract class CrosswordsHelper{

	public static function addSubmenu($vName){
		
		JSubMenuHelper::addEntry(JText::_('COM_CROSSWORDS_DASHBOARD'), 'index.php?option=com_crosswords&view=dashboard', $vName == 'dashboard');
		JSubMenuHelper::addEntry(JText::_('COM_CROSSWORDS_CROSSWORDS'), 'index.php?option=com_crosswords&view=crosswords', $vName == 'crosswords');
		JSubMenuHelper::addEntry(JText::_('COM_CROSSWORDS_KEYWORDS'), 'index.php?option=com_crosswords&view=keywords', $vName == 'keywords');
		JSubMenuHelper::addEntry(JText::_('COM_CROSSWORDS_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_crosswords', $vName == 'categories');
	}
	
	public static function getActions($itemid = 0){
		
		jimport('joomla.access.access');
		$user   = JFactory::getUser();
		$result = new JObject;

		if (empty($itemid)) {
			
			$assetName = 'com_crosswords';
			$level = 'component';
		}else {
			$assetName = 'com_crosswords.category.'.(int) $itemid;
			$level = 'category';
		}

		$actions = JAccess::getActions('com_crosswords', $level);

		foreach ($actions as $action) {
			
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}
	
	public static function award_points($params, $userid, $action, $reference, $info){
	
		$functions = null;
	
		switch ($params->get('points_system', 'none')){
	
			case 'cjblog':
			case 'jomsocial':
	
				$functions = array(
				'question'=>'com_crosswords.newquestion',
				'solve'=>'com_crosswords.solvedcrossword');
	
				break;
	
			case 'aup':
	
				$functions = array(
				'question'=>'sysplgaup_submitcwquestion',
				'solve'=>'sysplgaup_solvecrossword');
	
				break;
	
			default:
	
				return false;
		}
	
		switch ($action){
	
			case 1: // new crossword
	
				CJFunctions::award_points($params->get('points_system'), $userid, array(
					'reference'=>$reference,
					'info'=>$info,
					'function'=>$functions['question']
				));
	
				break;
	
			case 2: // solved crossword
	
				CJFunctions::award_points($params->get('points_system'), $userid, array(
					'reference'=>$reference,
					'info'=>$info,
					'function'=>$functions['solve']
				));
	
				break;
		}
	}
	
	public static function countItems(&$query)
	{
		// Join articles to categories and count published items
		$query->select('COUNT(DISTINCT cp.id) AS count_published');
		$query->join('LEFT', '#__crosswords AS cp ON cp.catid = a.id AND cp.published = 1');
	
		// Count unpublished items
		$query->select('COUNT(DISTINCT cu.id) AS count_unpublished');
		$query->join('LEFT', '#__crosswords AS cu ON cu.catid = a.id AND cu.published = 0');
	
		// Count archived items
		$query->select('COUNT(DISTINCT ca.id) AS count_archived');
		$query->join('LEFT', '#__crosswords AS ca ON ca.catid = a.id AND ca.published = 2');
	
		// Count trashed items
		$query->select('COUNT(DISTINCT ct.id) AS count_trashed');
		$query->join('LEFT', '#__crosswords AS ct ON ct.catid = a.id AND ct.published = -2');
	
		return $query;
	}
}
