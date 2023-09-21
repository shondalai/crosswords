<?php
/**
 * @version        $Id: crossword.php 01 2014-01-26 11:37:09Z maverick $
 * @package        CoreJoomla.crosswords
 * @subpackage     Components
 * @copyright      Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @author         Maverick
 * @link           http://www.corejoomla.com/
 * @license        License GNU General Public License version 2 or later
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

defined( '_JEXEC' ) or die;
jimport( 'joomla.application.component.controllerform' );

class CrosswordsControllerKeyword extends FormController {

	public function __construct( $config = [] ) {
		parent::__construct( $config );
		$this->input = Factory::getApplication()->input;
	}

	protected function allowAdd( $data = [] ) {
		$user       = Factory::getUser();
		$categoryId = ArrayHelper::getValue( $data, 'catid', $this->input->getInt( 'filter_category_id' ), 'int' );
		$allow      = null;

		if ( $categoryId )
		{
			// If the category has been passed in the data or URL check it.
			$allow = $user->authorise( 'core.create', 'com_crosswords.category.' . $categoryId );
		}

		if ( $allow === null )
		{
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd();
		}
		else
		{
			return $allow;
		}
	}

	protected function allowEdit( $data = [], $key = 'id' ) {
		$recordId = (int) isset( $data[$key] ) ? $data[$key] : 0;
		$user     = Factory::getUser();
		$userId   = $user->get( 'id' );

		// Check general edit permission first.
		if ( $user->authorise( 'core.edit', 'com_crosswords' ) )
		{
			return true;
		}

		// Fallback on edit.own.
		// First test if the permission is available.
		if ( $user->authorise( 'core.edit.own', 'com_crosswords' ) )
		{
			// Now test the owner is the user.
			$ownerId = (int) isset( $data['created_by'] ) ? $data['created_by'] : 0;
			if ( empty( $ownerId ) && $recordId )
			{
				// Need to do a lookup from the model.
				$record = $this->getModel()->getItem( $recordId );

				if ( empty( $record ) )
				{
					return false;
				}

				$ownerId = $record->created_by;
			}

			// If the owner matches 'me' then do the test.
			if ( $ownerId == $userId )
			{
				return true;
			}
		}

		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit( $data, $key );
	}

	public function batch( $model = null ) {
		Session::checkToken() or jexit( Text::_( 'JINVALID_TOKEN' ) );

		// Set the model
		$model = $this->getModel( 'Crossword', '', [] );

		// Preset the redirect
		$this->setRedirect( Route::_( 'index.php?option=com_crosswords&view=keywords' . $this->getRedirectToListAppend(), false ) );

		return parent::batch( $model );
	}

	protected function postSaveHook( $model, $validData = [] ) {
		$task = $this->getTask();

		if ( $task == 'save' )
		{
			$this->setRedirect( Route::_( 'index.php?option=com_crosswords&view=keywords', false ) );
		}
	}

}
