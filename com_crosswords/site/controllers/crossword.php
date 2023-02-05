<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2021 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die();

class CrosswordsControllerCrossword extends JControllerForm {

	protected $view_item = 'form';
	protected $view_list = 'crosswords';
	protected $urlVar = 'a_id';
	protected $text_prefix = 'COM_CROSSWORDS_CROSSWORD';

	public function add() {
		$result = parent::add();
		$user   = JFactory::getUser();

		if ( ! $result && $user->guest )
		{
			$redirectUrl = base64_encode( JRoute::_( CrosswordsHelperRoute::getCrosswordsRoute(), false ) );
			$loginUrl    = JRoute::_( 'index.php?option=com_users&view=login&return=' . $redirectUrl, false );
			$this->setRedirect( $loginUrl, JText::_( 'COM_CROSSWORDS_ERROR_LOGIN_TO_EXECUTE' ) );
		}

		return $result;
	}

	protected function allowAdd( $data = [] ) {
		$user       = JFactory::getUser();
		$categoryId = Joomla\Utilities\ArrayHelper::getValue( (array) $data, 'catid', $this->input->getInt( 'catid' ), 'int' );
		$allow      = null;

		if ( $categoryId )
		{
			// If the category has been passed in the data or URL check it.
			$allow = $user->authorise( 'core.create', 'com_crosswords.category.' . $categoryId );
		}

		if ( $allow === null )
		{
			// In the absense of better information, revert to the component
			// permissions.
			return parent::allowAdd();
		}
		else
		{
			return $allow;
		}
	}

	protected function allowEdit( $data = [], $key = 'id' ) {
		$recordId = (int) isset( $data[$key] ) ? $data[$key] : 0;
		$user     = JFactory::getUser();
		$userId   = $user->id;
		$asset    = 'com_crosswords.crossword.' . $recordId;

		// Check general edit permission first.
		if ( $user->authorise( 'core.edit', $asset ) )
		{
			return true;
		}

		// Fallback on edit.own.
		// First test if the permission is available.
		if ( $user->authorise( 'core.edit.own', $asset ) )
		{
			// Now test the owner is the user.
			$ownerId = (int) isset( $data['created_by'] ) ? $data['created_by'] : 0;

			// Need to do a lookup from the model.
			$record = $this->getModel()->getItem( $recordId );

			if ( empty( $record ) )
			{
				return false;
			}

			if ( empty( $ownerId ) )
			{
				$ownerId = $record->created_by;
			}

			// If the owner matches 'me' then do the test.
			if ( $ownerId == $userId )
			{
				// Check if the disallow edit after days option is enabled
				$params  = JComponentHelper::getParams( 'com_crosswords' );
				$numDays = (int) $params->get( 'disallow_editing_after', 0 );

				if ( $numDays )
				{
					$after = new JDate( JHtml::date( $record->created, 'Y-m-d H:i:s' ) );
					$after->modify( '+' . $numDays . ' day' );
					$now = new JDate( JHtml::date( 'now', 'Y-m-d H:i:s' ) );

					if ( $now > $after )
					{
						return false;
					}
				}

				return true;
			}
		}

		// Since there is no asset tracking, revert to the component
		// permissions.
		return parent::allowEdit( $data, $key );
	}

	public function cancel( $key = 'a_id' ) {
		parent::cancel( $key );

		// Redirect to the return page.
		$this->setRedirect( $this->getReturnPage() );
	}

	public function edit( $key = null, $urlVar = 'a_id' ) {
		$result = parent::edit( $key, $urlVar );
		$user   = JFactory::getUser();

		if ( ! $result && $user->guest )
		{
			$redirectUrl = base64_encode( JRoute::_( CrosswordsHelperRoute::getCrosswordsRoute(), false ) );
			$loginUrl    = JRoute::_( 'index.php?option=com_users&view=login&return=' . $redirectUrl, false );
			$this->setRedirect( $loginUrl, JText::_( 'COM_CROSSWORDS_ERROR_LOGIN_TO_EXECUTE' ) );
		}

		return $result;
	}

	public function getModel( $name = 'form', $prefix = '', $config = [ 'ignore_request' => true ] ) {
		$model = parent::getModel( $name, $prefix, $config );

		return $model;
	}

	protected function getRedirectToItemAppend( $recordId = null, $urlVar = 'a_id' ) {
		// Need to override the parent method completely.
		$tmpl   = $this->input->get( 'tmpl' );
		$append = '';

		// Setup redirect info.
		if ( $tmpl )
		{
			$append .= '&tmpl=' . $tmpl;
		}

		$append .= '&layout=edit';
		if ( $recordId )
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		$itemId = $this->input->getInt( 'Itemid' );
		$return = $this->getReturnPage();
		$catId  = $this->input->getInt( 'catid', null, 'get' );
		$title  = $this->input->getString( 'title', null, 'get' );

		if ( $itemId )
		{
			$append .= '&Itemid=' . $itemId;
		}

		if ( $catId )
		{
			$append .= '&catid=' . $catId;
		}

		if ( $title )
		{
			$append .= '&title=' . $title;
		}

		if ( $return )
		{
			$append .= '&return=' . base64_encode( $return );
		}

		return $append;
	}

	protected function getReturnPage() {
		$return = $this->input->get( 'return', null, 'base64' );

		if ( empty( $return ) || ! JUri::isInternal( base64_decode( $return ) ) )
		{
			return JUri::base();
		}
		else
		{
			return base64_decode( $return );
		}
	}

	protected function postSaveHook( JModelLegacy $model, $validData = [] ) {
		$crosswordId = $model->getState( 'form.id' );
		$isNew       = $model->getState( 'form.new' );
		$user        = JFactory::getUser();

		if ( $isNew && $user->authorise( 'core.moderate.crossword', 'com_crosswords.category.' . $validData['catid'] )
		     &&
		     ! $user->authorise( 'core.admin', 'com_crosswords' ) )
		{
			JFactory::getApplication()->enqueueMessage( JText::_( 'COM_CROSSWORDS_CROSSWORD_SENT_FOR_MODERATION' ) );
			$this->setRedirect( JRoute::_( CrosswordsHelperRoute::getCrosswordsRoute() ) );

			return;
		}

		$crosswordModel = $this->getModel( 'crossword' );
		$item           = $crosswordModel->getItem( $crosswordId );
		$item->slug     = $item->alias ? ( $item->id . ':' . $item->alias ) : $item->id;
		$item->catslug  = $item->category_alias ? ( $item->catid . ':' . $item->category_alias ) : $item->catid;

		$this->setRedirect( JRoute::_( CrosswordsHelperRoute::getCrosswordRoute( $item->slug, $item->catslug, $item->language ) ) );
	}

}
