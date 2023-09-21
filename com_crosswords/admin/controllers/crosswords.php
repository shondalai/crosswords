<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();
jimport('joomla.application.component.controlleradmin');

class CrosswordsControllerCrosswords extends AdminController
{
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('unfeatured', 'featured');
		$this->registerTask('unpublish', 'publish');
		
		if(APP_VERSION < 3)
		{
			$this->input = Factory::getApplication()->input;
		}
	}
	
	public function getModel($name = 'Crossword', $prefix = 'CrosswordsModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
	
		return $model;
	}
	
	protected function postDeleteHook( BaseDatabaseModel $model, $ids = null)
	{
		$model->postDeleteActions($ids);
	}
	
	public function featured()
	{
		// Check for request forgeries
		Session::checkToken() or jexit( Text::_('JINVALID_TOKEN'));

		$user   = Factory::getUser();
		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('featured' => 1, 'unfeatured' => 0);
		$task   = $this->getTask();
		$value  = ArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids))
		{
			JError::raiseWarning(500, Text::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else if (!$user->authorise('core.edit.state', 'com_crosswords'))
		{
			JError::raiseNotice(403, Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
		}
		else 
		{
			// Get the model.
			$model = $this->getModel();

			// Publish the items.
			if (!$model->featured($ids, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}
		}
		
		$this->setRedirect('index.php?option=com_crosswords&view=crosswords');
	}
	
	public function publish()
	{
		$cid = Factory::getApplication()->input->get('cid', array(), 'array');
		parent::publish();
	}
}