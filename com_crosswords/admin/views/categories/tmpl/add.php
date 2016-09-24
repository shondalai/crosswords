<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
?>
<form name="adminForm" id="adminForm" action="index.php?option=<?php echo CW_APP_NAME;?>&view=categories" method="post">
	<div class="col100">
		<table class="adminlist">
			<tr>
				<th width="150px"><label class="hasTip" title="<?php echo JText::_('LBL_TITLE'); ?>" for="category"><?php echo JText::_('LBL_TITLE'); ?></label></th>
				<td><input class="text_area" type="text" name="title" id="title" size="32" maxlength="250" value="<?php echo $this->category['title'];?>" /></td>
			</tr>
			<tr>
				<th width="150px"><label class="hasTip" title="<?php echo JText::_('LBL_ALIAS'); ?>" for="category"><?php echo JText::_('LBL_ALIAS'); ?></label></th>
				<td><input class="text_area" type="text" name="alias" id="alias" size="32" maxlength="250" value="<?php echo $this->category['alias'];?>" /></td>
			</tr>
			<tr>
				<th><label class="hasTip" title="<?php echo JText::_('LBL_PARENT_CATEGORY'); ?>" for="category"><?php echo JText::_( 'LBL_PARENT_CATEGORY' ); ?>:</label></th>
				<td>
					<select name="category" id="category">
						<?php if(!empty($this->categories)):?>
						<?php foreach($this->categories as $catid=>$title):?>
						<option value="<?php echo $catid;?>" <?php echo ($this->category['parent_id'] == $catid) ? 'selected="selected"' : '';?>><?php echo CJFunctions::escape($title);?></option>
	                    <?php endforeach;?>
	                    <?php endif;?>
					</select>
				</td>
			</tr>
		</table>
	</div>
    <input type="hidden" name="id" value="<?php echo $this->category['id'];?>">
    <input type="hidden" name="view" value="categories">
    <input type="hidden" name="task" value="add">
</form>