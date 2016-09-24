<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.modellist' );
require_once JPATH_COMPONENT.'/models/crosswords.php';

class CrosswordsModelDashboard extends CrosswordsModelCrosswords
{
	public function __construct ($config = array())
	{
		parent::__construct($config);
	}

	protected function populateState ($ordering = null, $direction = null)
	{
		parent::populateState('a.created', 'asc');
		
		$this->setState('list.direction', 'desc');
		$this->setState('list.limit', 5);
	}
}