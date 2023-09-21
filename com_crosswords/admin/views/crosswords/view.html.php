<?php
/**
 * @version        $Id: view.html.php 01 2014-01-26 11:37:09Z maverick $
 * @package        CoreJoomla.Crosswords
 * @subpackage     Components
 * @copyright      Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @author         Maverick
 * @link           http://www.corejoomla.com/
 * @license        License GNU General Public License version 2 or later
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined( '_JEXEC' ) or die;

class CrosswordsViewCrosswords extends HtmlView {

	protected $items;
	protected $pagination;
	protected $state;

	public function display( $tpl = null ) {
		if ( $this->getLayout() !== 'modal' )
		{
			CrosswordsHelper::addSubmenu( 'crosswords' );
		}

		$this->items         = $this->get( 'Items' );
		$this->pagination    = $this->get( 'Pagination' );
		$this->state         = $this->get( 'State' );
		$this->authors       = $this->get( 'Authors' );
		$this->filterForm    = $this->get( 'FilterForm' );
		$this->activeFilters = $this->get( 'ActiveFilters' );

		// Check for errors.
		if ( count( $errors = $this->get( 'Errors' ) ) )
		{
			JError::raiseError( 500, implode( "\n", $errors ) );

			return false;
		}

		// Levels filter.
		$options   = [];
		$options[] = HTMLHelper::_( 'select.option', '1', Text::_( 'J1' ) );
		$options[] = HTMLHelper::_( 'select.option', '2', Text::_( 'J2' ) );
		$options[] = HTMLHelper::_( 'select.option', '3', Text::_( 'J3' ) );
		$options[] = HTMLHelper::_( 'select.option', '4', Text::_( 'J4' ) );
		$options[] = HTMLHelper::_( 'select.option', '5', Text::_( 'J5' ) );
		$options[] = HTMLHelper::_( 'select.option', '6', Text::_( 'J6' ) );
		$options[] = HTMLHelper::_( 'select.option', '7', Text::_( 'J7' ) );
		$options[] = HTMLHelper::_( 'select.option', '8', Text::_( 'J8' ) );
		$options[] = HTMLHelper::_( 'select.option', '9', Text::_( 'J9' ) );
		$options[] = HTMLHelper::_( 'select.option', '10', Text::_( 'J10' ) );

		$this->f_levels = $options;

		// We don't need toolbar in the modal window.
		if ( $this->getLayout() !== 'modal' )
		{
			$this->addToolbar();
		}

		if ( APP_VERSION < 3 )
		{
			$tpl = 'j25';
		}

		parent::display( $tpl );
	}

	protected function addToolbar() {
		$canDo = ContentHelper::getActions( 'com_crosswords', 'category', $this->state->get( 'filter.category_id' ) );
		$user  = Factory::getUser();

		// Get the toolbar object instance
		$bar = Toolbar::getInstance( 'toolbar' );

		ToolbarHelper::title( Text::_( 'COM_CROSSWORDS_CROSSWORDS_TITLE' ), 'stack crossword' );

		if ( ( $canDo->get( 'crossword.edit' ) ) || ( $canDo->get( 'core.edit.own' ) ) )
		{
			ToolbarHelper::editList( 'crossword.edit' );
		}

		if ( $canDo->get( 'core.edit.state' ) )
		{
			ToolbarHelper::publish( 'crosswords.publish', 'JTOOLBAR_PUBLISH', true );
			ToolbarHelper::unpublish( 'crosswords.unpublish', 'JTOOLBAR_UNPUBLISH', true );
			ToolbarHelper::custom( 'crosswords.featured', 'featured.png', 'featured_f2.png', 'JFEATURED', true );
			ToolbarHelper::archiveList( 'crosswords.archive' );
			ToolbarHelper::checkin( 'crosswords.checkin' );
		}

		if ( $this->state->get( 'filter.published' ) == - 2 && $canDo->get( 'core.delete' ) )
		{
			ToolbarHelper::deleteList( '', 'crosswords.delete', 'JTOOLBAR_EMPTY_TRASH' );
		}
		elseif ( $canDo->get( 'core.edit.state' ) )
		{
			ToolbarHelper::trash( 'crosswords.trash' );
		}

		// Add a batch button
		if ( APP_VERSION >= 3 && $user->authorise( 'core.create', 'com_crosswords' ) && $user->authorise( 'core.edit', 'com_crosswords' )
		     && $user->authorise( 'core.edit.state', 'com_crosswords' ) )
		{
			HTMLHelper::_( 'bootstrap.modal', 'collapseModal' );
			$title = Text::_( 'JTOOLBAR_BATCH' );

			// Instantiate a new \Joomla\CMS\Layout\FileLayout instance and render the batch button
			$layout = new FileLayout( 'joomla.toolbar.batch' );

			$dhtml = $layout->render( [ 'title' => $title ] );
			$bar->appendButton( 'Custom', $dhtml, 'batch' );
		}

		if ( $user->authorise( 'core.admin', 'com_crosswords' ) )
		{
			ToolbarHelper::preferences( 'com_crosswords' );
		}
	}

	protected function getSortFields() {
		return [
			'a.ordering'     => Text::_( 'JGRID_HEADING_ORDERING' ),
			'a.state'        => Text::_( 'JSTATUS' ),
			'a.title'        => Text::_( 'JGLOBAL_TITLE' ),
			'category_title' => Text::_( 'JCATEGORY' ),
			'access_level'   => Text::_( 'JGRID_HEADING_ACCESS' ),
			'a.created_by'   => Text::_( 'JAUTHOR' ),
			'language'       => Text::_( 'JGRID_HEADING_LANGUAGE' ),
			'a.created'      => Text::_( 'JDATE' ),
			'a.id'           => Text::_( 'JGRID_HEADING_ID' ),
			'a.featured'     => Text::_( 'JFEATURED' ),
		];
	}

}
