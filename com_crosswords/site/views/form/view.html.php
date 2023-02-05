<?php
/**
 * @package     Crosswords
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2023 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( 'JPATH_PLATFORM' ) or die();

class CrosswordsViewForm extends JViewLegacy {

	protected $form;
	protected $item;
	protected $return_page;
	protected $state;

	public function display( $tpl = null ) {
		$user              = JFactory::getUser();
		$this->state       = $this->get( 'State' );
		$this->item        = $this->get( 'Item' );
		$this->form        = $this->get( 'Form' );
		$this->return_page = $this->get( 'ReturnPage' );

		if ( empty( $this->item->id ) )
		{
			$authorised = $user->authorise( 'core.create', 'com_crosswords' ) || ( count( $user->getAuthorisedCategories( 'com_crosswords', 'core.create' ) ) );
		}
		else
		{
			$authorised = $this->item->params->get( 'access-edit' );
		}

		if ( $authorised !== true )
		{
			throw new Exception( JText::_( 'JERROR_ALERTNOAUTHOR' ), 403 );
		}

		$this->item->tags = new JHelperTags;
		if ( ! empty( $this->item->id ) )
		{
			$this->item->tags->getItemTags( 'com_crosswords.crossword.', $this->item->id );
		}

		// Check for errors.
		if ( count( $errors = $this->get( 'Errors' ) ) )
		{
			throw new Exception( implode( "\n", $errors ), 500 );
		}

		// Create a shortcut to the parameters.
		$params = &$this->state->params;

		//Escape strings for HTML output
		$this->pageclass_sfx = $params->get( 'pageclass_sfx', '' ) ? htmlspecialchars( $params->get( 'pageclass_sfx', '' ) ) : '';

		$this->params = $params;
		$this->theme  = $this->params->get( 'theme', 'default' );
		$this->user   = $user;

		$this->_prepareDocument();
		parent::display( $tpl );
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument() {
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ( $menu )
		{
			$this->params->def( 'page_heading', $this->params->get( 'page_title', $menu->title ) );
		}
		else
		{
			$this->params->def( 'page_heading', JText::_( 'COM_CROSSWORD_FORM_EDIT_CROSSWORD' ) );
		}

		$title = $this->params->def( 'page_title', JText::_( 'COM_CROSSWORD_FORM_EDIT_CROSSWORD' ) );
		if ( $app->getCfg( 'sitename_pagetitles', 0 ) == 1 )
		{
			$title = JText::sprintf( 'JPAGETITLE', $app->getCfg( 'sitename' ), $title );
		}
		elseif ( $app->getCfg( 'sitename_pagetitles', 0 ) == 2 )
		{
			$title = JText::sprintf( 'JPAGETITLE', $title, $app->getCfg( 'sitename' ) );
		}
		$this->document->setTitle( $title );

		$pathway = $app->getPathWay();
		$pathway->addItem( $title, '' );

		if ( $this->params->get( 'menu-meta_description' ) )
		{
			$this->document->setDescription( $this->params->get( 'menu-meta_description' ) );
		}

		if ( $this->params->get( 'menu-meta_keywords' ) )
		{
			$this->document->setMetadata( 'keywords', $this->params->get( 'menu-meta_keywords' ) );
		}

		if ( $this->params->get( 'robots' ) )
		{
			$this->document->setMetadata( 'robots', $this->params->get( 'robots' ) );
		}
	}

}
