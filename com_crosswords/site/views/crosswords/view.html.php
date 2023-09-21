<?php
/**
 * @version        $Id: view.html.php 01 2013-01-13 11:37:09Z maverick $
 * @package        CoreJoomla.crosswords
 * @subpackage     Components
 * @copyright      Copyright (C) 2009 - 2013 corejoomla.com. All rights reserved.
 * @author         Maverick
 * @link           http://www.corejoomla.com/
 * @license        License GNU General Public License version 2 or later
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Plugin\PluginHelper;

defined( '_JEXEC' ) or die();

class CrosswordsViewCrosswords extends HtmlView {

	/**
	 * The model state
	 *
	 * @var   \Joomla\CMS\Object\CMSObject
	 * @since 4.0.0
	 */
	protected $state = null;

	/**
	 * An array containing archived articles
	 *
	 * @var   \stdClass[]
	 * @since 4.0.0
	 */
	protected $items = [];

	/**
	 * The pagination object
	 *
	 * @var   \Joomla\CMS\Pagination\Pagination|null
	 * @since 4.0.0
	 */
	protected $pagination = null;

	/**
	 * The page parameters
	 *
	 * @var    \Joomla\Registry\Registry|null
	 * @since  4.0.0
	 */
	protected $params = null;

	/**
	 * The search query used on any archived articles (note this may not be displayed depending on the value of the
	 * filter_field component parameter)
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $filter = '';

	/**
	 * The user object
	 *
	 * @var    \Joomla\CMS\User\User
	 * @since  4.0.0
	 */
	protected $user = null;

	/**
	 * The page class suffix
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $pageclass_sfx = '';

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since 4.0.0
	 */
	public function display( $tpl = null ) {
		$user       = Factory::getUser();
		$app        = Factory::getApplication();
		$state      = $this->get( 'State' );
		$items      = $this->get( 'Items' );
		$pagination = $this->get( 'Pagination' );

		if ( $errors = $this->getModel()->getErrors() )
		{
			throw new Exception( implode( "\n", $errors ), 500 );
		}

		// Flag indicates to not add limitstart=0 to URL
		$pagination->hideEmptyLimitstart = true;

		// Get the page/component configuration
		$params = &$state->params;

		PluginHelper::importPlugin( 'content' );

		foreach ( $items as $item )
		{
			$item->slug = $item->alias ? ( $item->id . ':' . $item->alias ) : $item->id;

			// No link for ROOT category
			if ( $item->parent_alias === 'root' )
			{
				$item->parent_id = null;
			}

			$item->event = new stdClass();

			// Old plugins: Ensure that text property is available
			if ( ! isset( $item->text ) )
			{
				$item->text = $item->description;
			}

			Factory::getApplication()->triggerEvent( 'onContentPrepare', [ 'com_crosswords.crossword', &$item, &$item->params, 0 ] );

			// Old plugins: Use processed text as introtext
			$item->introtext = $item->text;

			$results                        = $app->triggerEvent( 'onContentAfterTitle', [ 'com_content.archive', &$item, &$item->params, 0 ] );
			$item->event->afterDisplayTitle = trim( implode( "\n", $results ) );

			$results                           = $app->triggerEvent( 'onContentBeforeDisplay', [ 'com_content.archive', &$item, &$item->params, 0 ] );
			$item->event->beforeDisplayContent = trim( implode( "\n", $results ) );

			$results                          = $app->triggerEvent( 'onContentAfterDisplay', [ 'com_content.archive', &$item, &$item->params, 0 ] );
			$item->event->afterDisplayContent = trim( implode( "\n", $results ) );
		}

		$form             = new stdClass();
		$form->limitField = $pagination->getLimitBox();

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars( $params->get( 'pageclass_sfx', '' ) );

		$this->filter     = $state->get( 'list.filter' );
		$this->form       = &$form;
		$this->items      = &$items;
		$this->params     = &$params;
		$this->user       = &$user;
		$this->pagination = &$pagination;

		$this->_prepareDocument();

		parent::display( $tpl );
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since 4.0.0
	 */
	protected function _prepareDocument() {
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = Factory::getApplication()->getMenu()->getActive();

		if ( $menu )
		{
			$this->params->def( 'page_heading', $this->params->get( 'page_title', $menu->title ) );
		}
		else
		{
			$this->params->def( 'page_heading', Text::_( 'COM_CROSSWORDS_CROSSWORDS' ) );
		}

		$this->setDocumentTitle( $this->params->get( 'page_title', '' ) );

		if ( $this->params->get( 'menu-meta_description' ) )
		{
			$this->document->setDescription( $this->params->get( 'menu-meta_description' ) );
		}

		if ( $this->params->get( 'robots' ) )
		{
			$this->document->setMetaData( 'robots', $this->params->get( 'robots' ) );
		}
	}

}