<?php
/**
 * @version		$Id: crossword.php 01 2014-01-26 11:37:09Z maverick $
 * @package		CoreJoomla.crosswords
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');

class CrosswordsModelCrossword extends JModelAdmin
{
	protected $text_prefix = 'COM_CROSSWORDS';
	public $typeAlias = 'com_crosswords.crossword';
	protected $_item = null;

	protected function batchCopy($value, $pks, $contexts)
	{
		$categoryId = (int) $value;

		$i = 0;

		if (!parent::checkCategoryId($categoryId))
		{
			return false;
		}

		// Parent exists so we let's proceed
		while (!empty($pks))
		{
			// Pop the first ID off the stack
			$pk = array_shift($pks);

			$this->table->reset();

			// Check that the row actually exists
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);

					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Alter the title & alias
			$data = $this->generateNewTitle($categoryId, $this->table->alias, $this->table->title);
			$this->table->title = $data['0'];
			$this->table->alias = $data['1'];

			// Reset the ID because we are making a copy
			$this->table->id = 0;

			// New category ID
			$this->table->catid = $categoryId;

			// TODO: Deal with ordering?
			//$table->ordering	= 1;

			// Get the featured state
			$featured = $this->table->featured;

			// Check the row.
			if (!$this->table->check())
			{
				$this->setError($table->getError());
				return false;
			}

			if(APP_VERSION >= 3)
			{
				parent::createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);
			}

			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($table->getError());
				return false;
			}

			// Get the new item ID
			$newId = $this->table->get('id');

			// Add the new ID to the array
			$newIds[$i] = $newId;
			$i++;
		}

		// Clean the cache
		$this->cleanCache();

		return $newIds;
	}

	protected function canDelete($record)
	{
		if (!empty($record->id))
		{
			if ($record->published != -2)
			{
				return;
			}
			$user = JFactory::getUser();
			return $user->authorise('core.delete', 'com_crosswords.crossword.' . (int) $record->id);
		}
	}

	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check for existing crossword.
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_crosswords.crossword.' . (int) $record->id);
		}
		// New crossword, so check against the category.
		elseif (!empty($record->catid))
		{
			return $user->authorise('core.edit.state', 'com_crosswords.category.' . (int) $record->catid);
		}
		// Default to component settings if neither crossword nor category known.
		else
		{
			return parent::canEditState('com_crosswords');
		}
	}

	protected function prepareTable($table)
	{
		// Set the publish date to now
		$db = $this->getDbo();
		if ($table->published == 1 && (int) $table->publish_up == 0)
		{
			$table->publish_up = JFactory::getDate()->toSql();
		}

		if ($table->published == 1 && intval($table->publish_down) == 0)
		{
			$table->publish_down = $db->getNullDate();
		}

		// Increment the content version number.
		$table->version++;

		// Reorder the crosswords within the category so the new crossword is first
		if (empty($table->id))
		{
			$table->reorder('catid = ' . (int) $table->catid . ' AND published >= 0');
		}
	}

	public function getTable($type = 'Crossword', $prefix = 'CrosswordsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		
		if ($this->_item === null)
		{
			$this->_item = array();
		}
		
		if(!$pk)
		{
			// new item
			if ($item = parent::getItem($pk))
			{
				// Convert the params field to an array.
				$registry = new JRegistry;
				$registry->loadString($item->attribs);
				$item->attribs = $registry->toArray();
				
				// Convert the metadata field to an array.
				$registry = new JRegistry;
				$registry->loadString($item->metadata);
				$item->metadata = $registry->toArray();
				
				if (!empty($item->id))
				{
					$item->tags = new JHelperTags;
					$item->tags->getTagIds($item->id, 'com_crosswords.crossword');
				}
				
				$item->answers = $item->columns = array();
				$form_data = JFactory::getApplication()->getUserState('com_crosswords.edit.crossword.data');
			}
			
			return $item;
		}
		else if (!isset($this->_item[$pk]))
		{
			try
			{
				$user = JFactory::getUser();
				$db = $this->getDbo();
				
				$query = $db->getQuery(true)
					->select(
						$this->getState(
							'item.select', 'a.id, a.asset_id, a.title, a.alias, a.description, a.catid, a.created, a.created_by, a.created_by_alias, ' .
							// If badcats is not null, this means that the crossword is inside an unpublished category
							// In this case, the state is set to 0 to indicate Unpublished (even if the crossword state is Published)
							'CASE WHEN badcats.id is null THEN a.published ELSE 0 END AS published, ' .
							// Use created if modified is 0
							'CASE WHEN a.modified = ' . $db->q($db->getNullDate()) . ' THEN a.created ELSE a.modified END as modified, ' .
							'a.modified_by, a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, ' .
							'a.attribs, a.version, a.ordering, ' .
							'a.metakey, a.metadesc, a.access, a.hits, a.metadata, a.featured, a.language'
						)
				);
				
				$query->from('#__crosswords AS a');
			
				// Join on category table.
				$query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access')
					->join('LEFT', '#__categories AS c on c.id = a.catid');
			
				// Join on user table.
				$query->select('u.name AS author, u.email')
					->join('LEFT', '#__users AS u on u.id = a.created_by');
			
				// Filter by language
				if ($this->getState('filter.language'))
				{
					$query->where('a.language in (' . $db->q(JFactory::getLanguage()->getTag()) . ',' . $db->q('*') . ')');
				}
			
				// Join over the categories to get parent category titles
				$query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias')
					->join('LEFT', '#__categories as parent ON parent.id = c.parent_id')
					
					->where('a.id = ' . (int) $pk);
			
				if ((!$user->authorise('core.edit.state', 'com_crosswords')) && (!$user->authorise('core.edit', 'com_crosswords'))) 
				{
					// Filter by start and end dates.
					$nullDate = $db->q($db->getNullDate());
					$date = JFactory::getDate();
			
					$nowDate = $db->q($date->toSql());
			
					$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
						->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
				}
			
				// Join to check for category published state in parent categories up the tree
				// If all categories are published, badcats.id will be null, and we just use the crossword state
				$subquery = ' (SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ';
				$subquery .= 'ON cat.lft BETWEEN parent.lft AND parent.rgt ';
				$subquery .= 'WHERE parent.extension = ' . $db->q('com_crosswords');
				$subquery .= ' AND parent.published <= 0 GROUP BY cat.id)';
				$query->join('LEFT OUTER', $subquery . ' AS badcats ON badcats.id = c.id');
			
				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived = $this->getState('filter.archived');
			
				if (is_numeric($published))
				{
					$query->where('(a.published = ' . (int) $published . ' OR a.published =' . (int) $archived . ')');
				}
			
				$db->setQuery($query);
			
				$item = $db->loadObject();

				if (empty($item))
				{
					return JError::raiseError(404, JText::_('COM_CROSSWORDS_ERROR_POLL_NOT_FOUND'));
				}
				
				if ($item)
				{
					// Convert the params field to an array.
					$registry = new JRegistry;
					$registry->loadString($item->attribs);
					$item->attribs = $registry->toArray();
				
					// Convert the metadata field to an array.
					$registry = new JRegistry;
					$registry->loadString($item->metadata);
					$item->metadata = $registry->toArray();
				}
				
				if(APP_VERSION >= 3)
				{
					// Load associated content items
					$assoc = JLanguageAssociations::isEnabled();
				
					if ($assoc)
					{
						$item->associations = array();
						
						if ($item->id != null)
						{
							$associations = JLanguageAssociations::getAssociations('com_crosswords', '#__crosswords', 'com_crosswords.item', $item->id);
							
							foreach ($associations as $tag => $association)
							{
								$item->associations[$tag] = $association->id;
							}
						}
					}
				
					if (!empty($item->id))
					{
						$item->tags = new JHelperTags;
						$item->tags->getTagIds($item->id, 'com_crosswords.crossword');
					}
				}
				
				$this->_item[$pk] = $item;
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go thru the error handler to allow Redirect to work.
					JError::raiseError(404, $e->getMessage());
				}
				else
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}
		
		return $this->_item[$pk];
	}

	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_crosswords.crossword', 'crossword', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		$jinput = JFactory::getApplication()->input;

		// The front end calls this model and uses p_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('p_id'))
		{
			$id = $jinput->get('p_id', 0);
		}
		// The back end uses id so we use that the rest of the time and set it to 0 by default.
		else
		{
			$id = $jinput->get('id', 0);
		}
		// Determine correct permissions to check.
		if ($this->getState('crossword.id'))
		{
			$id = $this->getState('crossword.id');
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');
			// Existing record. Can only edit own crosswords in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit.own');
		}
		else
		{
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}

		$user = JFactory::getUser();

		// Check for existing crossword.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_crosswords.crossword.' . (int) $id))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_crosswords'))
		)
		{
			// Disable fields for display.
			$form->setFieldAttribute('featured', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an crossword you can edit.
			$form->setFieldAttribute('featured', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		if(APP_VERSION >= 3)
		{
			// Prevent messing with crossword language and category when editing existing crossword with associations
			$app = JFactory::getApplication();
			$assoc = JLanguageAssociations::isEnabled();
	
			if ($app->isClient('site') && $assoc && $this->getState('crossword.id'))
			{
				$form->setFieldAttribute('language', 'readonly', 'true');
				$form->setFieldAttribute('catid', 'readonly', 'true');
				$form->setFieldAttribute('language', 'filter', 'unset');
				$form->setFieldAttribute('catid', 'filter', 'unset');
			}
		}
				
		return $form;
	}

	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_crosswords.edit.crossword.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('crossword.id') == 0)
			{
				$data->set('catid', $app->input->getInt('catid', $app->getUserState('com_crosswords.crosswords.filter.category_id')));
			}
		}

		if(APP_VERSION >= 3)
		{
			$this->preprocessData('com_crosswords.crossword', $data);
		}

		return $data;
	}

	public function save($data)
	{
		$app = JFactory::getApplication();

		if (parent::save($data))
		{
			$db = JFactory::getDbo();
			$id = (int) $this->getState($this->getName() . '.id');
			$isnew = $this->getState($this->getName() . '.new');
			$item = $this->getItem($id);
			
			if (isset($data['featured']))
			{
				$this->featured($this->getState($this->getName() . '.id'), $data['featured']);
			}

			if(APP_VERSION >= 3)
			{
				$assoc = JLanguageAssociations::isEnabled();
				if ($assoc)
				{
					// Adding self to the association
					$associations = $data['associations'];
	
					foreach ($associations as $tag => $id)
					{
						if (empty($id))
						{
							unset($associations[$tag]);
						}
					}
	
					// Detecting all item menus
					$all_language = $item->language == '*';
	
					if ($all_language && !empty($associations))
					{
						JError::raiseNotice(403, JText::_('COM_CROSSWORDS_ERROR_ALL_LANGUAGE_ASSOCIATED'));
					}
	
					$associations[$item->language] = $item->id;
	
					// Deleting old association for these items
					$db = JFactory::getDbo();
					$query = $db->getQuery(true)
						->delete('#__associations')
						->where('context=' . $db->quote('com_crosswords.item'))
						->where('id IN (' . implode(',', $associations) . ')');
					$db->setQuery($query);
					$db->execute();
	
					if ($error = $db->getErrorMsg())
					{
						$this->setError($error);
						return false;
					}
	
					if (!$all_language && count($associations))
					{
						// Adding new association for these items
						$key = md5(json_encode($associations));
						$query->clear()
							->insert('#__associations');
	
						foreach ($associations as $id)
						{
							$query->values($id . ',' . $db->quote('com_crosswords.item') . ',' . $db->quote($key));
						}
	
						$db->setQuery($query);
						$db->execute();
	
						if ($error = $db->getErrorMsg())
						{
							$this->setError($error);
							return false;
						}
					}
				}
			}
			
			return true;
		}

		return false;
	}

	public function featured($pks, $value = 0)
	{
		// Sanitize the ids.
		$pks = (array) $pks;
		$pks = \Joomla\Utilities\ArrayHelper::toInteger($pks);

		if (empty($pks))
		{
			$this->setError(JText::_('COM_CROSSWORDS_NO_ITEM_SELECTED'));
			return false;
		}

		$table = $this->getTable('Crossword', 'CrosswordsTable');

		try
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
						->update($db->quoteName('#__crosswords'))
						->set('featured = ' . (int) $value)
						->where('id IN (' . implode(',', $pks) . ')');
			$db->setQuery($query);
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		$table->reorder();

		$this->cleanCache();

		return true;
	}

	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'catid = ' . (int) $table->catid;
		return $condition;
	}

	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		if(APP_VERSION >= 3)
		{
			// Association content items
			$assoc = JLanguageAssociations::isEnabled();
			if ($assoc)
			{
				$languages = JLanguageHelper::getLanguages('lang_code');
	
				// force to array (perhaps move to $this->loadFormData())
				$data = (array) $data;
	
				$addform = new SimpleXMLElement('<form />');
				$fields = $addform->addChild('fields');
				$fields->addAttribute('name', 'associations');
				$fieldset = $fields->addChild('fieldset');
				$fieldset->addAttribute('name', 'item_associations');
				$fieldset->addAttribute('description', 'COM_CROSSWORDS_ITEM_ASSOCIATIONS_FIELDSET_DESC');
				$add = false;
				foreach ($languages as $tag => $language)
				{
					if (empty($data['language']) || $tag != $data['language'])
					{
						$add = true;
						$field = $fieldset->addChild('field');
						$field->addAttribute('name', $tag);
						$field->addAttribute('type', 'modal_crossword');
						$field->addAttribute('language', $tag);
						$field->addAttribute('label', $language->title);
						$field->addAttribute('translate_label', 'false');
						$field->addAttribute('edit', 'true');
						$field->addAttribute('clear', 'true');
					}
				}
				if ($add)
				{
					$form->load($addform, false);
				}
			}
		}
		
		parent::preprocessForm($form, $data, $group);
	}
	
	public function validate($form, $data, $group = null)
	{
		$params = JComponentHelper::getParams('com_crosswords');
		$app = JFactory::getApplication();
		
		return parent::validate($form, $data, $group);
	}

	public function postDeleteActions($cid)
	{
	}

	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_crosswords');
	}
}
