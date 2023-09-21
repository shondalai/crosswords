<?php
/**
 * @version        $Id: view.html.php 01 2011-01-11 11:37:09Z maverick $
 * @package        CoreJoomla.Polls
 * @subpackage     Components
 * @copyright      Copyright (C) 2009 - 2010 corejoomla.com. All rights reserved.
 * @author         Maverick
 * @link           http://www.corejoomla.com/
 * @license        License GNU General Public License version 2 or later
 */

// no direct access
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined( '_JEXEC' ) or die( 'Restricted access' );

class CrosswordsViewCategories extends HtmlView {

	function display( $tpl = null ) {

		ToolbarHelper::title( Text::_( 'TITLE_COMMUNITY_CROSSWORDS' ) . ": <small><small>[" . Text::_( "LBL_CATEGORIES" ) . "]</small></small>", 'polls.png' );
		$model = $this->getModel( 'categories' );

		if ( $this->getLayout() == 'list' )
		{

			ToolbarHelper::custom( 'refresh', 'refresh.png', 'refresh.png', 'Refresh Categories', false, false );
			ToolbarHelper::addNew();

			$categories = $model->get_categories();
			$this->assignRef( 'categories', $categories );

		}
		elseif ( $this->getLayout() == 'add' )
		{

			ToolbarHelper::save();
			ToolbarHelper::cancel();

			$id       = JRequest::getVar( 'id', 0, '', 'int' );
			$category = [];

			if ( $id )
			{

				$category = $model->get_category( $id );
			}
			else
			{

				$category['id']    = $category['locked'] = $category['crosswords'] = $category['parent_id'] = 0;
				$category['title'] = $category['alias'] = '';
			}

			$this->assignRef( 'category', $category );
			$categories = $model->get_categories_tree( 0, true );
			$this->assignRef( 'categories', $categories );
		}

		parent::display( $tpl );
	}

}

?>