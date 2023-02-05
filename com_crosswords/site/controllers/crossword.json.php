<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2023 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die();

class CrosswordsControllerCrossword extends JControllerAdmin {

	public function __construct( $config = [] ) {
		parent::__construct( $config );
	}

	public function solveQuestion() {

		$user   = JFactory::getUser();
		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams( 'com_crosswords' );

		if ( ! $params->get( 'enable_solve_question', 1 ) || ! $user->authorise( 'core.solve', 'com_crosswords' ) )
		{
			throw new Exception( JText::_( 'COM_CROSSWORDS_MSG_NOT_AUTHORIZED' ), 403 );
		}

		$id   = $app->input->getInt( 'id', 0 );
		$axis = $app->input->getWord( 'axis', null );
		$pos  = $app->input->getInt( 'pos', 0 );

		if ( ! $id || ! $axis || ! $pos )
		{
			throw new Exception( JText::_( 'COM_CROSSWORDS_REQUIRED_FIELDS_MISSING' ), 404 );
		}

		$axis    = ( strcmp( $axis, 'x' ) == 0 ) ? 1 : 2;
		$model   = $this->getModel( 'Crossword' );
		$keyword = $model->getKeyword( $id, $axis, $pos );

		if ( ! $keyword )
		{
			throw new Exception( JText::_( 'COM_CROSSWORDS_MSG_ERROR_PROCESSING' ), 500 );
		}

		$chars = preg_split( '//u', $keyword, - 1, PREG_SPLIT_NO_EMPTY );
		echo new JResponseJson( $chars );
	}

	public function checkResult() {
		$user = JFactory::getUser();
		if ( ! $user->authorise( 'core.solve', 'com_crosswords' ) )
		{
			throw new Exception( JText::_( 'COM_CROSSWORDS_MSG_NOT_AUTHORIZED' ), 403 );
		}

		$model  = $this->getModel( 'Crossword' );
		$failed = $model->checkResult();

		if ( $failed === false )
		{
			throw new Exception( $model->getError(), 500 );
		}
		elseif ( empty( $failed ) )
		{
			$app    = JFactory::getApplication();
			$params = JComponentHelper::getParams( 'com_crosswords' );
			$cid    = $app->input->getInt( 'id', 0 );

			CrosswordsHelper::awardPoints( $cid, 2 );

			if ( $params->get( 'stream_on_solved_crossword', 0 ) )
			{
				CrosswordsHelper::streamActivity( $cid, 2 );
			}

			if ( $params->get( 'notif_crossword_solved', 0 ) )
			{
				$item      = $model->getItem();
				$from      = $app->get( 'mailfrom' );
				$fromname  = $app->get( 'fromname' );
				$sitename  = $app->get( 'sitename' );
				$user_name = $params->get( 'user_display_name', 'name' );
				$link      = JHtml::link( JRoute::_( CrosswordsHelperRoute::getCrosswordRoute( $item->id, $item->catid ), false, - 1 ), $item->title );

				$sub  = JText::sprintf( 'COM_CROSSWORDS_EMAIL_SOLVED_CROSSWORD_SUB', $user->$user_name );
				$body = JText::sprintf( 'COM_CROSSWORDS_EMAIL_SOLVED_CROSSWORD_BODY', $item->user_name, $item->title, $user->$user_name, $link, $sitename );
				CJFunctions::send_email( $from, $fromname, $item->email, $sub, $body, 1 );
			}

			echo new JResponseJson( [ 'status' => 1, 'message' => JText::_( 'COM_CROSSWORDS_MSG_CROSSWORD_SOLVED' ) ] );
		}
		else
		{
			echo new JResponseJson( [ 'status' => 2, 'failed' => $failed ] );
		}
	}

	public function solveCrossword() {
		$user = JFactory::getUser();
		if ( ! $user->authorise( 'core.solve', 'com_crosswords' ) )
		{
			throw new Exception( JText::_( 'COM_CROSSWORDS_MSG_NOT_AUTHORIZED' ), 403 );
		}
		$app = JFactory::getApplication();
		$id  = $app->input->getInt( 'id', 0 );

		if ( ! $id )
		{
			throw new Exception( JText::_( 'COM_CROSSWORDS_REQUIRED_FIELDS_MISSING' ), 404 );
		}
		$model   = $this->getModel( 'Crossword' );
		$answers = $model->solveCrossword( $id );

		if ( ! empty( $answers ) )
		{
			$return = [];
			foreach ( $answers as $answer )
			{
				$chars = preg_split( '//u', $answer->keyword, - 1, PREG_SPLIT_NO_EMPTY );

				if ( $answer->axis == 1 )
				{ // rows
					foreach ( $chars as $i => $char )
					{
						$celement        = new stdClass();
						$celement->id    = 'x-' . $answer->row . '-y-' . ( $answer->column + $i );
						$celement->value = $char;
						$return[]        = $celement;
					}
				}
				else
				{
					foreach ( $chars as $i => $char )
					{
						$celement        = new stdClass();
						$celement->id    = 'x-' . ( $answer->row + $i ) . '-y-' . $answer->column;
						$celement->value = $char;
						$return[]        = $celement;
					}
				}
			}

			echo new JResponseJson( $return );
		}
		else
		{
			throw new Exception( $model->getError(), 500 );
		}
	}

}