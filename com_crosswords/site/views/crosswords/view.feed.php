<?php
/**
 * @version        $Id: view.feed.php 01 2012-12-13 11:37:09Z maverick $
 * @package        CoreJoomla.Surveys
 * @subpackage     Components
 * @copyright      Copyright (C) 2010 - 2012 corejoomla.com. All rights reserved.
 * @author         Maverick
 * @link           http://www.corejoomla.com/
 * @license        License GNU General Public License version 2 or later
 */

// no direct access
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\Feed\FeedItem;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );
jimport( 'joomla.document.feed.feed' );

class CommunitySurveysViewSurvey extends HtmlView {

	protected $params;

	public function display( $data = null ) {

		$app    = Factory::getApplication();
		$doc    = Factory::getDocument();
		$params = $app->getParams();
		$model  = $this->getModel();

		// Get some data from the model
		$limit      = $app->getCfg( 'feed_limit' );
		$catid      = $app->input->getInt( 'catid', 0 );
		$active     = $app->getMenu()->getActive();
		$site_email = $app->getCfg( 'mailfrom' );
		$itemid     = CJFunctions::get_active_menu_id( false );

		if ( ! $catid )
		{

			$menuparams = $app->getMenu()->getParams( $itemid );
			$catid      = (int) $menuparams->get( 'catid', 0 );
			$app->input->set( 'catid', $catid );
		}

		/********************************** PARAMS *****************************/
		$appparams  = ComponentHelper::getParams( CW_APP_NAME );
		$menuParams = new Registry();

		if ( $active )
		{

			$menuParams->loadString( $active->params );
		}

		$this->params = clone $menuParams;
		$this->params->merge( $appparams );
		/********************************** PARAMS *****************************/

		$options = [ 'catid' => $catid, 'limit' => $limit, 'limitstart' => 0, 'order' => 'a.created', 'order_dir' => 'desc' ];
		$return  = $model->get_surveys( 1, $options, $this->params );

		if ( ! empty( $return['surveys'] ) )
		{

			foreach ( $return['surveys'] as $row )
			{

				// strip html from feed item title
				$title = $this->escape( $row->title );
				$title = html_entity_decode( $title, ENT_COMPAT, 'UTF-8' );

				// url link to question
				$link = Route::_( 'index.php?option=' . Q_APP_NAME . '&view=response&task=take_survey&id=' . $row->id . ':' . $row->alias . '&Itemid=' . $itemid );

				// strip html from feed item description text
				$description = $row->description;
				$author      = $row->created_by_alias;
				@$date = ( $row->created ? date( 'r', strtotime( $row->created ) ) : '' );

				// load individual item creator class
				$item = new FeedItem();

				$item->title       = $title;
				$item->link        = $link;
				$item->description = $description;
				$item->date        = $date;
				$item->category    = $row->category_title;

				$item->author = $author;

				if ( $site_email == 'site' )
				{

					$item->authorEmail = $site_email;
				}
				else
				{

					$item->authorEmail = $row->email;
				}

				// loads item info into rss array
				$doc->addItem( $item );
			}
		}
	}

}