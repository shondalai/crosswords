<?php
/**
 * @version		$Id: form.php 01 2011-08-13 11:37:09Z maverick $
 * @package		CoreJoomla.Crosswords
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2011 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
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

	<?php if($this->item->id > 0):?>
	<div class="alert alert-error"><i class="icon icon-warning-sign"></i> <?php echo JText::_('COM_CROSSWORDS_KEYWORD_EDIT_HELP');?></div>
	<?php endif;?>

	<div class="control-group">
		<label class="control-label" for="keyword"><?php echo JText::_('COM_CROSSWORDS_FIELD_KEYWORD');?>:<sup>*</sup></label>
		<div class="controls">
			<input type="text" name="keyword" id="keyword" class="input-xlarge" value="<?php echo $this->escape($this->item->keyword);?>" placeholder="<?php echo JText::_('COM_CROSSWORDS_FIELD_KEYWORD');?>">
		</div>
	</div>

	<div class="control-group">
		<label class="control-label" for="question"><?php echo JText::_('COM_CROSSWORDS_FIELD_QUESTION');?>:<sup>*</sup></label>
		<div class="controls">
			<textarea rows="3" cols="20" class="input-xlarge" id="question" name="question"
				placeholder="<?php echo JText::_('COM_CROSSWORDS_FIELD_QUESTION');?>"><?php echo $this->escape($this->item->question);?></textarea>
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
				<option value="0"<?php echo $this->item->published == 0 ? ' selected="selected"' : '';?>><?php echo JText::_('COM_CROSSWORDS_UNPUBLISHED');?></option>
			</select>
		</div>
	</div>

	<input type="hidden" name="task" value="save_crossword">
	<input type="hidden" name="id" value="<?php echo $this->item->id;?>">
</form>
