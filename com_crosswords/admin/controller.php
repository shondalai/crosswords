<?php
/**
 * @version        $Id: controller.php 01 2011-01-11 11:37:09Z maverick $
 * @package        CoreJoomla.crosswords
 * @subpackage     Components.controller
 * @copyright      Copyright (C) 2009 - 2010 corejoomla.com, Inc. All rights reserved.
 * @author         Maverick
 * @link           http://www.corejoomla.com/
 * @license        License GNU General Public License version 2 or later
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined( '_JEXEC' ) or die;

class CrosswordsController extends BaseController {

	protected $default_view = 'dashboard';

	public function display( $cachable = false, $urlparams = false ) {
		$input  = Factory::getApplication()->input;
		$view   = $input->get( 'view', 'dashboard' );
		$layout = $input->get( 'layout', 'default' );
		$id     = $input->getInt( 'id' );

		$doc = Factory::getDocument();
		$doc->addStylesheet( Uri::base() . 'components/com_crosswords/assets/css/cj.crosswords.admin.min.css' );
		$doc->addScript( Uri::base() . 'components/com_crosswords/assets/js/cj.crosswords.admin.min.js' );

		// Check for edit form.
		if ( $view == 'crossword' && $layout == 'edit' && ! $this->checkEditId( 'com_crosswords.edit.crossword', $id ) )
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError( Text::sprintf( 'JLIB_APPLICATION_ERROR_UNHELD_ID', $id ) );
			$this->setMessage( $this->getError(), 'error' );
			$this->setRedirect( Route::_( 'index.php?option=com_crosswords&view=crosswords', false ) );

			return false;
		}

		parent::display();

		return $this;
	}

}
