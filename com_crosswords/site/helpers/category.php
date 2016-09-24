<?php
/**
 * @version		$Id: categories.php 01 2012-09-20 11:37:09Z maverick $
 * @package		CoreJoomla.Surveys
 * @subpackage	Components.site
 * @copyright	Copyright (C) 2009 - 2013 corejoomla.com, Inc. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.categories');

class CrosswordsCategories extends JCategories{
	
	public function __construct($options = array()){
		
		$options['table'] = '#__crosswords';
		$options['extension'] = 'com_crosswords';
		$options['statefield'] = 'published';
		
		parent::__construct($options);
	}
}
