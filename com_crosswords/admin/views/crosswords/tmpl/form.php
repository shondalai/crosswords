<?php
/**
 * @package     corejoomla.admin
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$user = JFactory::getUser();
$editor = $user->authorise('core.wysiwyg', CW_APP_NAME) ? $this->params->get('default_editor', 'bbcode') : 'none';

CjScript::_( 'validate', [ 'custom' => false ] );
$categories = JHtml::_('category.categories', CW_APP_NAME);

foreach ($categories as $id=>$category){
	
	if(!$user->authorise('core.create', CW_APP_NAME.'.category.'.$category->value)) {
		
		unset($categories[$id]);
	}
}

if(version_compare(JVERSION, '3.0', 'ge')) {
	JHTML::_('behavior.framework');
} else {
	JHTML::_('behavior.mootools');
}
JHTML::_('behavior.modal');
$this->loadHelper('select');
?>

<form class="form-horizontal" name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option='.CW_APP_NAME.'&view=crosswords&task=save_crossword')?>" method="post">

	<div class="control-group">
		<label class="control-label" for="title"><?php echo JText::_('COM_CROSSWORDS_FIELD_TITLE');?>:<sup>*</sup></label>
		<div class="controls">
			<input type="text" name="title" id="title" value="<?php echo $this->escape($this->item->title);?>" placeholder="<?php echo JText::_('COM_CROSSWORDS_FIELD_TITLE');?>">
		</div>
	</div>

	<div class="control-group">
		<label class="control-label" for="alias"><?php echo JText::_('COM_CROSSWORDS_FIELD_ALIAS');?>:</label>
		<div class="controls">
			<input type="text" name="alias" id="alias" value="<?php echo $this->escape($this->item->alias);?>" placeholder="<?php echo JText::_('COM_CROSSWORDS_FIELD_ALIAS');?>">
		</div>
	</div>

	<div class="control-group">
		<label class="control-label" for="catid"><?php echo JText::_('COM_CROSSWORDS_FIELD_CATEGORY');?>:<sup>*</sup></label>
		<div class="controls">
			<?php echo JHTML::_('select.genericlist', $categories, 'catid', array('list.select'=>$this->item->catid));?>
		</div>
	</div>

	<div class="control-group">
		<label class="control-label" for="published"><?php echo JText::_('COM_CROSSWORDS_FIELD_STATUS');?>:<sup>*</sup></label>
		<div class="controls">
			<select name="published" id="published" size="1">
				<option value="1"<?php echo $this->item->published == 1 ? ' selected="selected"' : '';?>><?php echo JText::_('COM_CROSSWORDS_PUBLISHED');?></option>
				<option value="0"<?php echo $this->item->published == 1 ? ' selected="selected"' : '';?>><?php echo JText::_('COM_CROSSWORDS_UNPUBLISHED');?></option>
			</select>
		</div>
	</div>
	
	<div class="control-group">
		<label class="control-label" for="description"><?php echo JText::_('COM_CROSSWORDS_FIELD_DESCRIPTION');?>:</label>
		<div class="controls">
			<?php echo CJFunctions::load_editor($editor, 'description', 'description', $this->item->description, '5', '40', '60%', '200px', '', 'width: 99%;'); ?>
		</div>
	</div>
	
	<input type="hidden" name="task" value="save_crossword">
	<input type="hidden" name="id" value="<?php echo $this->item->id;?>">
</form>
