<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\FormHelper;

defined('JPATH_PLATFORM') or die;

FormHelper::loadFieldClass('list');

class JFormFieldKAuthors extends ListField
{
	public $type = 'KAuthors';
	protected static $options = array();

	protected function getOptions()
	{
		// Accepted modifiers
		$hash = md5($this->element);

		if (!isset(static::$options[$hash]))
		{
			static::$options[$hash] = parent::getOptions();

			$options = array();

			$db = Factory::getDbo();

			// Construct the query
			$query = $db->getQuery(true)
				->select('u.id AS value, u.name AS text')
				->from('#__users AS u')
				->join('INNER', '#__crosswords_keywords AS c ON c.created_by = u.id')
				->group('u.id, u.name')
				->order('u.name');

			// Setup the query
			$db->setQuery($query);

			// Return the result
			if ($options = $db->loadObjectList())
			{
				static::$options[$hash] = array_merge(static::$options[$hash], $options);
			}
		}

		return static::$options[$hash];
	}
}
