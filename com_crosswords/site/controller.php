<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2009 - 2018 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Uri\Uri;

defined( '_JEXEC' ) or die();


jimport( 'joomla.application.component.controller' );

class CrosswordsController extends BaseController {

	public function __construct( $config = [] ) {
		$this->input = Factory::getApplication()->input;

		if ( $this->input->get( 'view' ) === 'crosswords' && $this->input->get( 'layout' ) === 'modal' )
		{
			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}

		parent::__construct( $config );
	}

	public function display( $cachable = false, $urlparams = false ) {
		$cachable = true;

		$id    = $this->input->getInt( 'p_id' );
		$vName = $this->input->getCmd( 'view', 'polls' );
		$this->input->set( 'view', $vName );
		$doc    = Factory::getDocument();
		$params = ComponentHelper::getParams( 'com_crosswords' );

		$safeurlparams = [
			'catid'            => 'INT',
			'id'               => 'INT',
			'cid'              => 'ARRAY',
			'year'             => 'INT',
			'month'            => 'INT',
			'limit'            => 'UINT',
			'limitstart'       => 'UINT',
			'showall'          => 'INT',
			'return'           => 'BASE64',
			'filter'           => 'STRING',
			'filter_order'     => 'CMD',
			'filter_order_Dir' => 'CMD',
			'filter-search'    => 'STRING',
			'print'            => 'BOOLEAN',
			'lang'             => 'CMD',
			'Itemid'           => 'INT',
		];

		// Check for edit form.
		if ( $vName == 'form' && ! $this->checkEditId( 'com_crosswords.edit.crossword', $id ) )
		{
			// Somehow the person just went to the form - we don't allow that.
			return JError::raiseError( 403, Text::sprintf( 'JLIB_APPLICATION_ERROR_UNHELD_ID', $id ) );
		}

		if ( APP_VERSION < 4 )
		{
			CjLib::behavior( 'bscore', [ 'customtag' => false ] );
			CjScript::_( 'fontawesome', [ 'custom' => false ] );

			if ( $params->get( 'enable_bootstrap', 1 ) == 1 )
			{
				HTMLHelper::_( 'behavior.framework' );
				CJLib::import( 'corejoomla.ui.bootstrap' );
			}
		}
		else
		{
			$wa = $doc->getWebAssetManager();
			$wa
				->useScript( 'jquery' )
				->useScript( 'bootstrap.tab' )
				->useScript( 'bootstrap.dropdown' )
				->useScript( 'bootstrap.modal' )
				->useStyle( 'fontawesome' );
		}
		CJFunctions::add_css_to_document( $doc, Uri::root( true ) . '/media/com_crosswords/css/cj.crosswords.min.css', true );
		CJFunctions::add_script_to_document( $doc, 'cj.crosswords.min.js', true, Uri::root( true ) . '/media/com_crosswords/js/' );

		parent::display( $cachable, $safeurlparams );

		return $this;
	}

}
