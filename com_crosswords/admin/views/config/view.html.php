<?php
/**
 * @version		$Id: view.html.php 01 2011-01-11 11:37:09Z maverick $
 * @package		CoreJoomla.Crosswords
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2011 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Import Joomla! libraries
jimport( 'joomla.application.component.view');
class CrosswordsViewConfig extends JView {
    function display($tpl = null) {
        JToolBarHelper::title(JText::_('TITLE_COMMUNITY_CROSSWORDS').':&nbsp;<small><small>['.JText::_('LBL_CONFIG').']</small></small>', 'crosswords.png');
        if($this->getLayout() == 'config') {
			JToolBarHelper::save();
			$config = CrosswordsHelper::getConfig(true);
			$this->assignRef('config', $config);
        }
        
        parent::display($tpl);
    }
}
?>