<?php
/**
 * @version        $Id: crosswords.php 01 2013-06-14 11:37:09Z maverick $
 * @package        corejoomla.crosswords
 * @subpackage     Components
 * @copyright      Copyright (C) 2009 - 2013 corejoomla.com. All rights reserved.
 * @author         Maverick
 * @link           http://www.corejoomla.com/
 * @license        License GNU General Public License version 2 or later
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

defined( '_JEXEC' ) or die();

jimport( 'joomla.application.component.controller' );

class CrosswordsControllerActions extends BaseController {

	public function submit_keyword() {

		$user = Factory::getUser();

		if ( ! $user->authorise( 'core.keywords', CW_APP_NAME ) )
		{
			echo json_encode( [ 'error' => Text::_( 'COM_CROSSWORDS_MSG_NOT_AUTHORIZED' ) ] );
		}
		else
		{

			$model   = $this->getModel( 'crosswords' );
			$keyword = $model->save_keyword();

			if ( $keyword )
			{

				$app    = Factory::getApplication();
				$params = ComponentHelper::getParams( CW_APP_NAME );

				$question_title = $app->input->getString( 'question-title', '' );

				if ( $params->get( 'notif_admin_new_keyword', 0 ) == '1' )
				{

					$itemid            = CJFunctions::get_active_menu_id();
					$question_keyword  = $app->input->getString( 'question-keyword', '' );
					$question_category = $app->input->getInt( 'question-category', 0 );

					$body     = Text::sprintf( 'COM_CROSSWORDS_EMAIL_ADMIN_NEW_KEYWORD_BODY', $user->username, $question_title, $question_keyword,
						$question_category );
					$from     = $app->getCfg( 'mailfrom' );
					$fromname = $app->getCfg( 'fromname' );

					$admin_emails = $model->get_admin_emails( $params->get( 'admin_user_groups', 0 ) );

					if ( ! empty( $admin_emails ) )
					{

						CJFunctions::send_email( $from, $fromname, $admin_emails, $sub, $body, 1 );
					}
				}

				$question->id       = $keyword;
				$question->question = $question_title;

				echo json_encode( [ 'message' => Text::_( 'COM_CROSSWORDS_MSG_QUESTION_SUBMITTED' ), 'question' => $keyword ] );
			}
			else
			{

				echo json_encode( [ 'error' => $model->getError() ] );
			}
		}

		jexit();
	}

	public function get_questions() {

		$user = Factory::getUser();

		if ( ! $user->authorise( 'core.create', CW_APP_NAME ) )
		{

			echo json_encode( [ 'error' => Text::_( 'COM_CROSSWORDS_MSG_NOT_AUTHORIZED' ) ] );
		}
		else
		{

			$app   = Factory::getApplication();
			$catid = $app->input->getInt( 'catid', 0 );

			if ( $catid <= 0 )
			{

				echo json_encode( [ 'questions' => [] ] );
			}
			else
			{

				$model     = $this->getModel( 'crosswords' );
				$questions = $model->get_questions( $catid );

				if ( empty( $questions ) )
				{

					echo json_encode( [ 'error' => Text::_( 'COM_CROSSWORDS_MSG_NO_KEYWORDS_IN_CATEGORY' ) ] );
				}
				else
				{

					echo json_encode( [ 'questions' => $questions ] );
				}
			}
		}

		jexit();
	}

	public function get_form() {

		$user = Factory::getUser();

		if ( ! $user->authorise( 'core.create', CW_APP_NAME ) )
		{

			throw new Exception( Text::_( 'COM_CROSSWORDS_MSG_NOT_AUTHORIZED' ), 403 );
		}
		else
		{

			$view      = $this->getView( 'form', 'html' );
			$model     = $this->getModel( 'crossword' );
			$cat_model = $this->getModel( 'categories' );

			$view->setModel( $model, true );
			$view->setModel( $cat_model, false );
			$view->assign( 'action', 'form' );

			$view->display();
		}
	}

	public function edit_crossword() {

		$user = Factory::getUser();

		if ( ! $user->authorise( 'core.create', CW_APP_NAME ) )
		{

			throw new Exception( Text::_( 'COM_CROSSWORDS_MSG_NOT_AUTHORIZED' ), 403 );
		}
		else
		{

			$view      = $this->getView( 'form', 'html' );
			$model     = $this->getModel( 'crossword' );
			$cat_model = $this->getModel( 'categories' );

			$view->setModel( $model, true );
			$view->setModel( $cat_model, false );
			$view->assign( 'action', 'form' );

			$view->display();
		}
	}

	public function save_crossword() {
		$app    = Factory::getApplication();
		$user   = Factory::getUser();
		$model  = $this->getModel( 'crossword' );
		$itemid = CJFunctions::get_active_menu_id();

		$failed                 = false;
		$crossword              = new stdClass();
		$crossword->id          = $app->input->post->getInt( 'id', 0 );
		$crossword->title       = $app->input->post->getString( 'title', null );
		$crossword->alias       = $app->input->post->getString( 'alias', null );
		$crossword->description = ComponentHelper::filterText( $app->input->post->get( 'description', '', 'raw' ) );
		$crossword->catid       = $app->input->post->getString( 'catid', 0 );

		if ( $crossword->id > 0 )
		{
			//update
			if ( ! $user->authorise( 'core.edit', CW_APP_NAME . '.category.' . $crossword->catid ) )
			{
				throw new Exception( Text::_( 'COM_CROSSWORDS_MSG_NOT_AUTHORIZED' ), 403 );
			}

			if ( empty( $crossword->title ) || ! $crossword->catid )
			{
				$app->enqueueMessage( Text::_( 'COM_CROSSWORDS_REQUIRED_FIELDS_MISSING' ) );
				$failed = true;
			}
			elseif ( $model->update_crossword( $crossword ) )
			{
				$this->setRedirect(
					Route::_( 'index.php?option=' . CW_APP_NAME . '&view=crosswords&task=view&id=' . $crossword->id . ':' . $crossword->alias . $itemid ),
					Text::_( 'COM_CROSSWORDS_MSG_CROSSWORD_SAVE_SUCCESS' ) );
			}
			else
			{
				$app->enqueueMessage( $model->getError() );
				$failed = true;
			}
		}
		else
		{
			$crossword->size      = $app->input->post->getString( 'grid_size', 15 );
			$crossword->level     = $app->input->post->getString( 'difficulty_level', 1 );
			$crossword->questions = $app->input->post->getArray( [ 'target-list' => 'array' ] );
			$crossword->questions = $crossword->questions['target-list'];

			if ( ! $user->authorise( 'core.create', CW_APP_NAME . '.category.' . $crossword->catid ) )
			{
				throw new Exception( Text::_( 'COM_CROSSWORDS_MSG_NOT_AUTHORIZED' ), 403 );
			}

			if ( empty( $crossword->title ) || ! $crossword->size || ! $crossword->level || ! $crossword->catid )
			{
				$app->enqueueMessage( Text::_( 'COM_CROSSWORDS_REQUIRED_FIELDS_MISSING' ) );
				$failed = true;
			}
			elseif ( $model->create_crossword( $crossword ) )
			{
				$params    = ComponentHelper::getParams( CW_APP_NAME );
				$user_name = $params->get( 'user_display_name', 'name' );
				CrosswordsHelper::awardPoints( $crossword->id, 1 );

				if ( $params->get( 'stream_on_new_crossword', 0 ) == '1' )
				{
					// Stream activity
					CrosswordsHelper::streamActivity( $crossword->id, 1 );
				}

				if ( $params->get( 'notif_admin_new_crossword', 1 ) == 1 )
				{
					$admin_emails = $model->get_admin_emails( $params->get( 'admin_user_groups', 8 ) );
					$username     = $user->guest ? Text::_( 'LBL_GUEST' ) : $user->name . '(' . $user->username . ')';
					$from         = $app->getCfg( 'mailfrom' );
					$fromname     = $app->getCfg( 'fromname' );

					$sub  = Text::sprintf( 'COM_CROSSWORDS_ACTIVITY_CREATED_CROSSWORD', $user->$user_name, $crossword->title );
					$body = Text::sprintf( 'COM_CROSSWORDS_EMAIL_ADMIN_NEW_CROSSWORD_BODY', $user->$user_name, $crossword->title );

					if ( ! empty( $admin_emails ) )
					{
						CJFunctions::send_email( $from, $fromname, $admin_emails, $sub, $body, 1 );
					}
				}

				$this->setRedirect(
					Route::_( 'index.php?option=' . CW_APP_NAME . '&view=crosswords&task=view&id=' . $crossword->id . ':' . $crossword->alias . $itemid ),
					Text::_( 'COM_CROSSWORDS_MSG_CROSSWORD_SAVE_SUCCESS' ) );
			}
			else
			{
				$app->enqueueMessage( $model->getError() );
				$failed = true;
			}
		}

		if ( $failed )
		{
			$view      = $this->getView( 'form', 'html' );
			$cat_model = $this->getModel( 'categories' );

			$view->setModel( $model, true );
			$view->setModel( $cat_model, false );
			$view->assign( 'action', 'form' );
			$view->assignRef( 'item', $crossword );

			$view->display();
		}
	}

	public function save_keyword() {
		$user = Factory::getUser();
		if ( ! $user->authorise( 'core.keywords', CW_APP_NAME ) )
		{
			echo json_encode( [ 'error' => Text::_( 'COM_CROSSWORDS_MSG_NOT_AUTHORIZED' ) ] );
		}
		else
		{
			$model = $this->getModel( 'crossword' );
			$id    = $model->save_keyword();

			if ( $id > 0 )
			{
				$app    = Factory::getApplication();
				$params = ComponentHelper::getParams( 'com_crosswords' );

				$question_title    = $app->input->post->getString( 'question', null );
				$question_keyword  = $app->input->post->getString( 'keyword', null );
				$question_category = $app->input->post->getInt( 'category', null );

				if ( $params->get( 'notif_admin_new_keyword', 0 ) == 1 )
				{
					$from     = $app->get( 'mailfrom' );
					$fromname = $app->get( 'fromname' );
					$sub      = Text::_( 'COM_CROSSWORDS_EMAIL_ADMIN_NEW_KEYWORD_SUB' );
					$body     = Text::sprintf( 'COM_CROSSWORDS_EMAIL_ADMIN_NEW_KEYWORD_BODY', $user->username, $question_title, $question_keyword,
						$question_category );

					$crosswords_model = $this->getModel( 'crosswords' );
					$admin_emails     = $crosswords_model->get_admin_emails( $params->get( 'admin_user_groups', 0 ) );

					if ( ! empty( $admin_emails ) )
					{
						CJFunctions::send_email( $from, $fromname, $admin_emails, $sub, $body, 1 );
					}
				}

				$keyword           = new stdClass();
				$keyword->id       = $id;
				$keyword->question = $question_title;

				echo json_encode( [ 'message' => Text::_( 'COM_CROSSWORDS_MSG_QUESTION_SUBMITTED' ), 'question' => $keyword ] );
			}
			else
			{
				$error = Text::_( 'COM_CROSSWORDS_MSG_ERROR_PROCESSING' );
				switch ( $id )
				{
					case - 1:
						$error = Text::_( 'COM_CROSSWORDS_REQUIRED_FIELDS_MISSING' );
						break;

					case - 2:
						$error = Text::_( 'COM_CROSSWORDS_ERROR_DUPLICATE_KEYWORD' );
						break;

					case - 3:
						$error = Text::_( 'COM_CROSSWORDS_MSG_ERROR_PROCESSING' );
						break;
				}

				echo json_encode( [ 'error' => $error ] );
			}
		}

		jexit();
	}

}