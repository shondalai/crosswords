<?php
/**
 * @version		$Id: view.html.php 01 2014-01-26 11:37:09Z maverick $
 * @package		CoreJoomla.keywords
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;

class CrosswordsViewKeywords extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	public function display($tpl = null)
	{
		if ($this->getLayout() !== 'modal')
		{
			CrosswordsHelper::addSubmenu('keywords');
		}

		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->authors       = $this->get('Authors');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
		}
		
		if(APP_VERSION < 3)
		{
			$tpl = 'j25';
		}

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$canDo = CrosswordsHelper::getActions($this->state->get('filter.category_id'), 0, 'com_crosswords');
		$user  = JFactory::getUser();

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_CROSSWORDS_KEYWORDS_TITLE'), 'stack keyword');

		if (($canDo->get('keyword.create')) || ($canDo->get('core.edit.own')))
		{
			JToolbarHelper::addNew('keyword.add');
		}
		
		if (($canDo->get('keyword.edit')) || ($canDo->get('core.edit.own')))
		{
			JToolbarHelper::editList('keyword.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('keywords.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('keywords.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::archiveList('keywords.archive');
			JToolbarHelper::checkin('keywords.checkin');
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'keywords.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('keywords.trash');
		}

		// Add a batch button
		if (APP_VERSION >= 3 && $user->authorise('core.create', 'com_crosswords') && $user->authorise('core.edit', 'com_crosswords') && $user->authorise('core.edit.state', 'com_crosswords'))
		{
			JHtml::_('bootstrap.modal', 'collapseModal');
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($user->authorise('core.admin', 'com_crosswords'))
		{
			JToolbarHelper::preferences('com_crosswords');
		}
	}

	protected function getSortFields()
	{
		return array(
			'a.ordering'     => JText::_('JGRID_HEADING_ORDERING'),
			'a.state'        => JText::_('JSTATUS'),
			'a.title'        => JText::_('JGLOBAL_TITLE'),
			'category_title' => JText::_('JCATEGORY'),
			'access_level'   => JText::_('JGRID_HEADING_ACCESS'),
			'a.created_by'   => JText::_('JAUTHOR'),
			'language'       => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.created'      => JText::_('JDATE'),
			'a.id'           => JText::_('JGRID_HEADING_ID'),
			'a.featured'     => JText::_('JFEATURED')
		);
	}
}
