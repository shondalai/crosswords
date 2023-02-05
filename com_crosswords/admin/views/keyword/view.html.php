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

defined( '_JEXEC' ) or die;

class CrosswordsViewKeyword extends JViewLegacy {

	protected $form;
	protected $item;
	protected $state;

	public function display( $tpl = null ) {
		$app   = JFactory::getApplication();
		$model = $this->getModel();

		if ( $this->getLayout() == 'edit' )
		{
			$this->form = $this->get( 'Form' );
		}

		$this->item  = $this->get( 'Item' );
		$this->state = $this->get( 'State' );
		$this->canDo = JHelperContent::getActions( 'com_crosswords', 'category', $this->state->get( 'filter.category_id' ) );

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
			$userId = JFactory::getUser()->get( 'id' );
			JToolbarHelper::title( JText::_( 'COM_CROSSWORDS_PAGE_KEYWORD_DETAILS' ), 'pencil-2 keyword-add' );

			JToolbarHelper::cancel( 'crossword.cancel' );
		}

		if ( APP_VERSION < 3 )
		{
			$tpl = 'j25';
		}

		parent::display( $tpl );
	}

	protected function addToolbar() {
		JFactory::getApplication()->input->set( 'hidemainmenu', true );
		$user       = JFactory::getUser();
		$userId     = $user->get( 'id' );
		$isNew      = ( $this->item->id == 0 );
		$checkedOut = ! ( $this->item->checked_out == 0 || $this->item->checked_out == $userId );

		// Built the actions for new and existing records.
		$canDo = $this->canDo;
		JToolbarHelper::title( JText::_( 'COM_CROSSWORDS_PAGE_' . ( $checkedOut ? 'VIEW_KEYWORD' : ( $isNew ? 'ADD_KEYWORD' : 'EDIT_KEYWORD' ) ) ), 'pencil-2 keyword-add' );

		// For new records, check the create permission.
		if ( $isNew && ( count( $user->getAuthorisedCategories( 'com_crosswords', 'core.create' ) ) > 0 ) )
		{
			JToolbarHelper::apply( 'keyword.apply' );
			JToolbarHelper::save( 'keyword.save' );
			JToolbarHelper::save2new( 'keyword.save2new' );
			JToolbarHelper::cancel( 'keyword.cancel' );
		}
		else
		{
			// Can't save the record if it's checked out.
			if ( ! $checkedOut )
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ( $canDo->get( 'core.edit' ) || ( $canDo->get( 'core.edit.own' ) && $this->item->created_by == $userId ) )
				{
					JToolbarHelper::apply( 'keyword.apply' );
					JToolbarHelper::save( 'keyword.save' );

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ( $canDo->get( 'core.create' ) )
					{
						JToolbarHelper::save2new( 'keyword.save2new' );
					}
				}
			}

			JToolbarHelper::cancel( 'keyword.cancel', 'JTOOLBAR_CLOSE' );
		}
	}

}
