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
	
	public static function countItems(&$items)
	{
		$db = JFactory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;
			$query = $db->getQuery(true);
			$query->select('published as state, count(*) AS count')
				->from($db->qn('#__crosswords'))
				->where('catid = ' . (int) $item->id)
				->group('published');
			$db->setQuery($query);
			$topics = $db->loadObjectList();

			foreach ($topics as $topic)
			{
				if ($topic->state == 1)
				{
					$item->count_published = $topic->count;
				}

				if ($topic->state == 0)
				{
					$item->count_unpublished = $topic->count;
				}

				if ($topic->state == 2)
				{
					$item->count_archived = $topic->count;
				}

				if ($topic->state == -2)
				{
					$item->count_trashed = $topic->count;
				}
			}
		}

		return $items;
	}
}
