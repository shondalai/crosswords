<?php
/**
 * @version		$Id: view.html.php 01 2011-01-11 11:37:09Z maverick $
 * @package		CoreJoomla.Polls
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2010 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Import Joomla! libraries
jimport( 'joomla.application.component.view');

class CrosswordsViewCategories extends JViewLegacy {
	
    function display($tpl = null) {
    	
        JToolBarHelper::title(JText::_('TITLE_COMMUNITY_CROSSWORDS').": <small><small>[".JText::_("LBL_CATEGORIES")."]</small></small>", 'polls.png');
        $model = $this->getModel('categories');
        
        if($this->getLayout() == 'list') {
        	
            JToolBarHelper::custom('refresh','refresh.png','refresh.png','Refresh Categories',false, false);
            JToolBarHelper::addNewX();
        	
            $categories = $model->get_categories();
            $this->assignRef('categories',$categories);
            
        }else if($this->getLayout() == 'add') {
        	
            JToolBarHelper::save();
            JToolBarHelper::cancel();
        	
            $id = JRequest::getVar('id', 0, '', 'int');
            $category = array();
            
            if($id){
            	
                $category = $model->get_category($id);
            } else{
            	
            	$category['id'] = $category['locked'] = $category['crosswords'] = $category['parent_id'] = 0;
            	$category['title'] = $category['alias'] = '';
            }

            $this->assignRef('category', $category);
            $categories = $model->get_categories_tree(0, true);
            $this->assignRef('categories', $categories);
        }
        
        parent::display($tpl);
    }
}
?>