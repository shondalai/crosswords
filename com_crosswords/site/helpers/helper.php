<?php
/**
 * @version		$Id: helper.php 01 2011-01-11 11:37:09Z maverick $
 * @package		CoreJoomla.crosswords
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2010 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class CrosswordsHelper 
{
    public static function awardPoints($itemId, $type)
    {
    	$user = JFactory::getUser();
    	$api = new CjLibApi();
    	$db = JFactory::getDbo();
    
    	$query = $db->getQuery(true)
	    	->select('a.id, a.title, a.alias, a.catid, a.description')
	    	->select('c.alias as category_alias')
	    	->select('u.name AS author')
	    	->from('#__crosswords AS a')
	    	->join('left', '#__categories AS c ON a.catid = c.id')
	    	->join('left', '#__users AS u on a.created_by = u.id')
	    	->where('a.id = '.$itemId);
    	$db->setQuery($query);
    
    	try
    	{
    		$item = $db->loadObject();
    	}
    	catch (Exception $e)
    	{
    		return false;
    	}
    
    	if($item)
    	{
    		$params 			= JComponentHelper::getParams('com_crosswords');
    		$pointsComponent 	= $params->get('points_system', 'none');
    		$profileComponent 	= $params->get('user_avatar', 'none');
    			
    		$item->slug 		= $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
    		$item->catslug 	= $item->category_alias ? ($item->catid . ':' . $item->category_alias) : $item->catid;
    		$menuid 			= CJFunctions::get_active_menu_id();
    		$itemUrl 			= JRoute::_('index.php?option=com_crosswords&view=crosswords&task=view&id='.$item->slug.$menuid);
    		$itemLink 			= JHtml::link($itemUrl, JComponentHelper::filterText($item->title));
    		$info 				= '';
    		$reference 			= $itemId;
    		$awardedTo 			= $user->id;
    			
    		switch ($type)
    		{
    			case 1: // new crossword
    				$function = 'com_crosswords.create';
    				$title = JText::sprintf('COM_CROSSWORDS_POINTS_NEW_CROSSWORD', $itemeLink);
    				$info = $item->description;
    				break;
    					
    			case 2: // solve crossword
    				$function = 'com_crosswords.solve';
    				$title = JText::sprintf('COM_CROSSWORDS_POINTS_SOLVED_CROSSWORD', $itemLink);
    				$info = $item->description;
    				break;
    		}
    
    		$options = array('function'=>$function, 'reference'=>$reference, 'info'=>$info, 'component'=>'com_crosswords', 'title'=>$title);
    		$api->awardPoints($pointsComponent, $awardedTo, $options);
    	}
    
    	return true;
    }
    
    public static function streamActivity($itemId, $type)
    {
    	$params = JComponentHelper::getParams('com_crosswords');
    	$streamApp = $params->get('activity_stream_type', 'none');
    
    	// Activity stream
    	if(empty($streamApp) || $streamApp == 'none')
    	{
    		return true;
    	}
    
    	$db = JFactory::getDbo();
    	$item = null;
    
    	$query = $db->getQuery(true)
	    	->select('a.id, a.title, a.alias, a.catid, a.description')
	    	->select('c.alias as category_alias')
	    	->select('u.name AS author')
	    	->from('#__crosswords AS a')
	    	->join('left', '#__categories AS c ON a.catid = c.id')
	    	->join('left', '#__users AS u on a.created_by = u.id')
	    	->where('a.id = '.$itemId);
    	$db->setQuery($query);
    
    	try
    	{
    		$item = $db->loadObject();
    	}
    	catch (Exception $e)
    	{
    		return false;
    	}
    
    	if($item)
    	{
    		$user = JFactory::getUser();
    		$language = JFactory::getLanguage();
    		$language->load('com_crosswords');
    		$api = new CjLibApi();
    
    		$item->slug 		= $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
    		$item->catslug 	= !empty($item->category_alias) ? ($item->catid . ':' . $item->category_alias) : $item->catid;
    			
    		$profileComponent 	= $params->get('user_avatar', 'none');
    		$userName 			= $api->getUserProfileUrl($profileComponent, $user->id, false, $user->name);
    		$menuid 			= CJFunctions::get_active_menu_id();
    		$itemUrl 			= JRoute::_('index.php?option=com_crosswords&view=crosswords&task=view&id='.$item->slug.$menuid);
    		$itemLink 			= JHtml::link($itemUrl, JComponentHelper::filterText($item->title));
    		$parentId 			= 0;
    			
    		switch ($type)
    		{
    			case 1: // new crossword
    				$title = JText::sprintf('COM_CROSSWORDS_ACTIVITY_NEW_CROSSWORD', $userName, $itemLink);
    				$description = $item->description;
    				$function = 'com_crosswords.create';
    				break;

    			case 2: // solve crossword
    				$title = JText::sprintf('COM_CROSSWORDS_ACTIVITY_SOLVED_CROSSWORD', $userName, $itemLink);
    				$description = $item->description;
    				$function = 'com_crosswords.solve';
    				break;
    		}
    
    		$activity = new stdClass();
    		$activity->type = $function;
    		$activity->href = $itemUrl;
    		$activity->title = $title;
    		$activity->description = $description;
    		$activity->userId = $user->id;
    		$activity->featured = 0;
    		$activity->language = $language->getTag();
    		$activity->itemId = $itemId;
    		$activity->parentId = $parentId;
    		$activity->length = $params->get('stream_character_limit', 0);
    
    		$api->pushActivity($streamApp, $activity);
    	}
    
    	return true;
    }
}
?>