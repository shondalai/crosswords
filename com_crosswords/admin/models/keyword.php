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
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;

defined( '_JEXEC' ) or die;

Table::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_crosswords/tables' );

class CrosswordsModelKeyword extends AdminModel {

	protected $text_prefix = 'COM_CROSSWORDS';
	public $typeAlias = 'com_crosswords.keyword';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since 1.0.0
	 */
	protected function canDelete( $record ) {
		// do not allow deleting
		return false;
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since 1.0.0
	 */
	protected function canEditState( $record ) {
		$user = Factory::getUser();

		return parent::canEditState( 'com_crosswords' );
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable    A database object
	 *
	 * @since 1.0.0
	 */
	public function getTable( $type = 'Keyword', $prefix = 'CrosswordsTable', $config = [] ) {
		return Table::getInstance( $type, $prefix, $config );
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0.0
	 */
	public function save( $data ) {
		$date = Factory::getDate();
		$user = Factory::getUser();
		$db   = Factory::getDbo();

		if ( isset( $data['id'] ) && $data['id'] )
		{
			// Existing item
			$data['modified']    = $date->toSql();
			$data['modified_by'] = $user->get( 'id' );
		}
		else
		{
			// New topic. A topic created and created_by field can be set
			// by the user, so we don't touch either of these if they are set.
			if ( empty( $data['created'] ) )
			{
				// Hack, set replied to current date as well to sort recent topics correctly
				$data['created'] = $date->toSql();
			}

			if ( empty( $data['created_by'] ) )
			{
				$data['created_by'] = $user->get( 'id' );
			}

			$data['modified'] = $db->getNullDate();
		}

		if ( parent::save( $data ) )
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   1.0.0
	 */
	public function getForm( $data = [], $loadData = true ) {
		// Get the form.
		$form = $this->loadForm( 'com_crosswords.keyword', 'keyword', [ 'control' => 'jform', 'load_data' => $loadData ] );
		if ( empty( $form ) )
		{
			return false;
		}
		$jinput = Factory::getApplication()->input;

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ( $jinput->get( 'a_id' ) )
		{
			$id = $jinput->get( 'a_id', 0 );
		}
		// The back end uses id so we use that the rest of the time and set it to 0 by default.
		else
		{
			$id = $jinput->get( 'id', 0 );
		}
		// Determine correct permissions to check.
		if ( $this->getState( 'keyword.id' ) )
		{
			$id = $this->getState( 'keyword.id' );
		}

		$user = Factory::getUser();

		// Check for existing article.
		// Modify the form based on Edit State access controls.
		if ( ! $user->authorise( 'core.edit.state', 'com_crosswords' ) )
		{
			// Disable fields for display.
			$form->setFieldAttribute( 'ordering', 'disabled', 'true' );
			$form->setFieldAttribute( 'published', 'disabled', 'true' );

			// Disable fields while saving.
			// The controller has already verified this is an article you can edit.
			$form->setFieldAttribute( 'ordering', 'filter', 'unset' );
			$form->setFieldAttribute( 'published', 'filter', 'unset' );
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since 1.0.0
	 */
	protected function loadFormData() {
		// Check the session for previously entered form data.
		$app  = Factory::getApplication();
		$data = $app->getUserState( 'com_crosswords.edit.keyword.data', [] );

		if ( empty( $data ) )
		{
			$data = $this->getItem();
		}

		$this->preprocessData( 'com_crosswords.keyword', $data );

		return $data;
	}

	/**
	 * Custom clean the cache of com_crosswords and content modules
	 *
	 * @since 1.0.0
	 */
	protected function cleanCache( $group = null, $client_id = 0 ) {
		parent::cleanCache( 'com_crosswords' );
	}

}
