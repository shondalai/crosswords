<?php
/**
 * @version        $Id: view.html.php 01 2014-01-26 11:37:09Z maverick $
 * @package        CoreJoomla.crosswords
 * @subpackage     Components
 * @copyright      Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @author         Maverick
 * @link           http://www.corejoomla.com/
 * @license        License GNU General Public License version 2 or later
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined( '_JEXEC' ) or die;

class CrosswordsViewCrossword extends HtmlView {

	protected $form;
	protected $item;
	protected $state;

	public function display( $tpl = null ) {
		$app   = Factory::getApplication();
		$model = $this->getModel();

		if ( $this->getLayout() == 'edit' )
		{
			$this->form = $this->get( 'Form' );
		}

		$this->item  = $this->get( 'Item' );
		$this->state = $this->get( 'State' );
		$this->canDo = ContentHelper::getActions( 'com_crosswords', 'category', $this->state->get( 'filter.category_id' ) );

		// Check for errors.
		if ( count( $errors = $this->get( 'Errors' ) ) )
		{
			JError::raiseError( 500, implode( "\n", $errors ) );

			return false;
		}

		if ( $this->getLayout() == 'modal' )
		{
			$this->form->setFieldAttribute( 'language', 'readonly', 'true' );
			$this->form->setFieldAttribute( 'catid', 'readonly', 'true' );
		}

		$params       = &$this->state->params;
		$this->params = $params;
		$this->theme  = $this->params->get( 'theme', 'default' );

		if ( $this->getLayout() == 'edit' )
		{
			$this->addToolbar();
		}
		else
		{
			$this->items      = $model->getItems();
			$this->pagination = $model->getPagination();

			$canDo  = $this->canDo;
			$userId = Factory::getUser()->get( 'id' );
			ToolbarHelper::title( Text::_( 'COM_CROSSWORDS_PAGE_CROSSWORD_DETAILS' ), 'pencil-2 crossword-add' );

			ToolbarHelper::cancel( 'crossword.cancel' );
		}

		if ( APP_VERSION < 3 )
		{
			$tpl = 'j25';
		}

		parent::display( $tpl );
	}

	protected function addToolbar() {
		Factory::getApplication()->input->set( 'hidemainmenu', true );
		$user       = Factory::getUser();
		$userId     = $user->get( 'id' );
		$isNew      = ( $this->item->id == 0 );
		$checkedOut = ! ( $this->item->checked_out == 0 || $this->item->checked_out == $userId );

		// Built the actions for new and existing records.
		$canDo = $this->canDo;
		ToolbarHelper::title( Text::_( 'COM_CROSSWORDS_PAGE_' . ( $checkedOut ? 'VIEW_CROSSWORD' : ( $isNew ? 'ADD_CROSSWORD' : 'EDIT_CROSSWORD' ) ) ),
			'pencil-2 crossword-add' );

		// For new records, check the create permission.
		if ( $isNew && ( count( $user->getAuthorisedCategories( 'com_crosswords', 'core.create' ) ) > 0 ) )
		{
			ToolbarHelper::apply( 'crossword.apply' );
			ToolbarHelper::save( 'crossword.save' );
			ToolbarHelper::save2new( 'crossword.save2new' );
			ToolbarHelper::cancel( 'crossword.cancel' );
		}
		else
		{
			// Can't save the record if it's checked out.
			if ( ! $checkedOut )
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ( $canDo->get( 'core.edit' ) || ( $canDo->get( 'core.edit.own' ) && $this->item->created_by == $userId ) )
				{
					ToolbarHelper::apply( 'crossword.apply' );
					ToolbarHelper::save( 'crossword.save' );

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ( $canDo->get( 'core.create' ) )
					{
						ToolbarHelper::save2new( 'crossword.save2new' );
					}
				}
			}

			if ( $this->state->params->get( 'save_history', 0 ) && $user->authorise( 'core.edit' ) )
			{
				ToolbarHelper::versions( 'com_crosswords.crossword', $this->item->id );
			}

			ToolbarHelper::cancel( 'crossword.cancel', 'JTOOLBAR_CLOSE' );
		}
	}

}
