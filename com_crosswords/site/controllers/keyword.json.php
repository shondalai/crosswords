<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2023 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Response\JsonResponse;

defined( '_JEXEC' ) or die();

class CrosswordsControllerKeyword extends AdminController {

	public function __construct( $config = [] ) {
		parent::__construct( $config );
	}

	public function save() {
		$user = Factory::getUser();
		if ( ! $user->authorise( 'core.keywords', 'com_crosswords' ) )
		{
			throw new Exception( Text::_( 'COM_CROSSWORDS_MSG_NOT_AUTHORIZED' ), 403 );
		}

		$app                 = Factory::getApplication();
		$model               = $this->getModel( 'Keyword' );
		$keyword             = [];
		$keyword['question'] = $app->input->getString( 'keyword', '' );
		$keyword['keyword']  = $app->input->getString( 'question', '' );
		$keyword['catid']    = $app->input->getInt( 'category', 0 );

		if ( empty( $keyword['question'] ) || empty( $keyword['keyword'] ) || empty( $keyword['catid'] ) )
		{
			throw new Exception( Text::_( 'COM_CROSSWORDS_REQUIRED_FIELDS_MISSING' ), 404 );
		}

		if ( $model->save( $keyword ) )
		{
			$keyword['id'] = $model->getState( $model->getName() . '.id' );
			$params        = ComponentHelper::getParams( 'com_crosswords' );

			if ( $params->get( 'notif_admin_new_keyword', 0 ) == '1' )
			{
				$sub          = Text::_( 'COM_CROSSWORDS_EMAIL_ADMIN_NEW_KEYWORD_SUB' );
				$body         = Text::sprintf( 'COM_CROSSWORDS_EMAIL_ADMIN_NEW_KEYWORD_BODY', $user->username, $keyword['question'], $keyword['keyword'],
					$keyword['catid'] );
				$from         = $app->get( 'mailfrom' );
				$fromname     = $app->get( 'fromname' );
				$admin_emails = $model->getAdminEmailIds( $params->get( 'admin_user_groups', 0 ) );

				if ( ! empty( $admin_emails ) )
				{
					CJFunctions::send_email( $from, $fromname, $admin_emails, $sub, $body, 1 );
				}
			}

			echo new JsonResponse( $keyword, Text::_( 'COM_CROSSWORDS_MSG_QUESTION_SUBMITTED' ) );
		}
		else
		{
			throw new Exception( $model->getError(), 500 );
		}
	}

}