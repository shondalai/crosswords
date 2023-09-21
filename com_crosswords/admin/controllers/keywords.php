<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;

defined( '_JEXEC' ) or die();

class CrosswordsControllerKeywords extends AdminController {

	public function __construct( $config = [] ) {
		parent::__construct( $config );
		$this->registerTask( 'unpublish', 'publish' );

		if ( APP_VERSION < 3 )
		{
			$this->input = Factory::getApplication()->input;
		}
	}

	public function getModel( $name = 'Keyword', $prefix = 'CrosswordsModel', $config = [ 'ignore_request' => true ] ) {
		$model = parent::getModel( $name, $prefix, $config );

		return $model;
	}

	protected function postDeleteHook( $model, $ids = null ) {
		$model->postDeleteActions( $ids );
	}

	public function publish() {
		$cid = Factory::getApplication()->input->get( 'cid', [], 'array' );
		parent::publish();
	}

}