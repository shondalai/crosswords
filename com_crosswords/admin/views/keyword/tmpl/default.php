<?php
/**
 * @version		$Id: view.html.php 01 2014-01-26 11:37:09Z maverick $
 * @package		CoreJoomla.Polls
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

CJLib::behavior('bscore');
CJFunctions::load_jquery(array('libs'=>array('validate', 'form', 'fontawesome')));

$app 		= JFactory::getApplication();
$user		= JFactory::getUser();
$params 	= $this->state->get('params');
$editor 	= $this->params->get('dafault_editor', 'wysiwygbb');
$input 		= $app->input;
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$saveOrder	= $listOrder == 'a.voted_on';
$palette 	= ChartsHelper::get_rgb_colors($this->item->pallete);
?>

<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<div id="cj-wrapper">
	<form action="<?php echo JRoute::_('index.php?option=com_communitypolls&view=poll&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
		<h2 class="page-header"><?php echo $this->escape($this->item->title);?></h2>
		<div class="row-fluid">
			<div class="span8">
				<?php if (empty($this->items)) : ?>
				<div class="alert alert-no-items">
					<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
				<?php else : ?>
				<table class="table table-striped table-hover table-condensed" id="pollList">
					<thead>
						<tr>
							<th width="1%" class="hidden-phone">
								<?php echo JHtml::_('grid.checkall'); ?>
							</th>
							<th>
								<?php echo JHtml::_('searchtools.sort', 'COM_COMMUNITYPOLLS_ANSWER', 'a.option_id', $listDirn, $listOrder); ?>
							</th>
							<?php if($this->item->type == 'grid'):?>
							<th>
								<?php echo JHtml::_('searchtools.sort', 'COM_COMMUNITYPOLLS_COLUMN', 'a.column_id', $listDirn, $listOrder); ?>
							</th>
							<?php endif;?>
							
							<?php if($this->item->custom_answer != 0):?>
							<th>
								<?php echo JHtml::_('searchtools.sort',  'COM_COMMUNITYPOLLS_FIELD_CUSTOM_ANSWER_LABEL', 'a.custom_answer', $listDirn, $listOrder); ?>
							</th>
							<?php endif;?>
							
							<th width="15%">
								<?php echo JHtml::_('searchtools.sort',  'JAUTHOR', 'a.voter_id', $listDirn, $listOrder); ?>
							</th>
							<th width="10%" class="nowrap hidden-phone">
								<?php echo JHtml::_('searchtools.sort', 'COM_COMMUNITYPOLLS_IP_ADDRESS', 'a.ip_address', $listDirn, $listOrder); ?>
							</th>
							<th width="10%" class="nowrap hidden-phone">
								<?php echo JHtml::_('searchtools.sort', 'JDATE', 'a.voted_on', $listDirn, $listOrder); ?>
							</th>
							<th width="1%" class="nowrap hidden-phone">
								<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->items as $i => $item) :?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="center hidden-phone">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td>
								<?php echo $this->escape($item->answer_title);?>
							</td>
							
							<?php if($this->item->type == 'grid'):?>
							<td>
								<?php echo $this->escape($item->column_title);?>
							</td>
							<?php endif;?>
							
							<?php if($this->item->custom_answer != 0):?>
							<td class="small">
								<?php echo $this->escape($item->custom_answer);?>
							</td>
							<?php endif;?>
							
							<td class="small">
								<?php echo $item->voter_id > 0 ? $this->escape($item->author_name) : JText::_('COM_COMMUNITYPOLLS_GUEST');?>
							</td>
							<td class="nowrap small hidden-phone">
								<?php echo $this->escape($item->ip_address);?>
							</td>
							<td class="nowrap small hidden-phone">
								<?php echo JHtml::_('date', $item->voted_on, JText::_('DATE_FORMAT_LC4')); ?>
							</td>
							<td class="center hidden-phone">
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
					<?php endforeach;?>
					</tbody>
				</table>
				
				<div class="center">
					<?php echo $this->pagination->getListFooter(); ?>
				</div>
				<?php endif;?>
			</div>
			<div class="span4">
				<div class="answers">
					<?php foreach ($this->item->answers as $i=>$answer):?>
					<label><i class="icon-asterisk"></i> <?php echo $answer->title.' ('.$answer->votes.' '.strtolower(JText::plural('COM_COMMUNITYPOLLS_VOTES', $answer->votes)).' / '.$answer->pct.'%)';?></label>
					<div class="progress progress-striped">
						<div class="bar progress-bar" role="progressbar" aria-valuenow="<?php echo $answer->pct?>" aria-valuemin="0" aria-valuemax="100" 
							style="width: <?php echo $answer->pct?>%; background-color: <?php echo $palette[$i%count($palette)]?>">
							<span class="sr-only"><?php echo JText::sprintf('COM_COMMUNITYPOLLS_SR_ONLY_PCT_COMPLETE', $answer->pct);?></span>
						</div>
					</div>
					<?php endforeach;?>
				</div>
				<table class="table table-striped table-bordered table-condensed table-hover">
					<tbody>
						<tr>
							<th><?php echo JText::_('JAUTHOR')?></th>
							<td><?php echo $this->item->author?></td>
						</tr>
						<tr>
							<th><?php echo JText::_('COM_COMMUNITYPOLLS_FIELD_CLOSING_DATE_LABEL')?></th>
							<td><?php echo $this->item->close_date?></td>
						</tr>
						<tr>
							<th><?php echo JText::_('COM_COMMUNITYPOLLS_FIELD_POLL_TYPE_LABEL')?></th>
							<td><?php echo $this->escape($this->item->type)?></td>
						</tr>
						<tr>
							<th><?php echo JText::_('COM_COMMUNITYPOLLS_FIELD_RESULTS_UP_LABEL')?></th>
							<td><?php echo $this->item->results_up?></td>
						</tr>
						<tr>
							<th><?php echo JText::_('COM_COMMUNITYPOLLS_FIELD_CUSTOM_ANSWER_LABEL')?></th>
							<td><?php echo $this->item->custom_answer == 1 ? JText::_('JYES') : JText::_('JNO');?></td>
						</tr>
						<tr>
							<th><?php echo JText::_('COM_COMMUNITYPOLLS_FIELD_ANONYMOUS_LABEL')?></th>
							<td><?php echo $this->item->anonymous == 1 ? JText::_('JYES') : JText::_('JNO');?></td>
						</tr>
						<tr>
							<th><?php echo JText::_('COM_COMMUNITYPOLLS_FIELD_PRIVATE_LABEL')?></th>
							<td><?php echo $this->item->private == 1 ? JText::_('JYES') : JText::_('JNO');?></td>
						</tr>
						<tr>
							<th><?php echo JText::_('COM_COMMUNITYPOLLS_FIELD_CHART_TYPE_LABEL')?></th>
							<td><?php echo $this->escape($this->item->chart_type);?></td>
						</tr>
						<tr>
							<th><?php echo JText::_('COM_COMMUNITYPOLLS_FIELD_ANSWERS_ORDER_LABEL')?></th>
							<td><?php echo $this->escape($this->item->answers_order ? $this->item->answers_order : 'order');?></td>
						</tr>
						<tr>
							<th><?php echo JText::_('COM_COMMUNITYPOLLS_FIELD_MODIFY_ANSWERS_LABEL')?></th>
							<td><?php echo $this->item->modify_answers == 1 ? JText::_('JYES') : JText::_('JNO');?></td>
						</tr>
						<tr>
							<th><?php echo JText::_('COM_COMMUNITYPOLLS_FIELD_COLOR_PALETTE_LABEL')?></th>
							<td><?php echo $this->escape($this->item->pallete);?></td>
						</tr>
						<tr>
							<th><?php echo JText::_('COM_COMMUNITYPOLLS_FIELD_PRIVATE_LABEL');?></th>
							<td><?php echo $this->item->private == 1 ? JText::_('JYES') : JText::_('JNO');?></td>
						</tr>
						<?php if($this->item->type == 'checkbox' && $this->item->max_answers > $this->item->min_answers):?>
						<tr>
							<th><?php echo JText::_('COM_COMMUNITYPOLLS_FIELD_MINIMUM_ANSWERS_LABEL');?></th>
							<td><?php echo $this->item->min_answers;?></td>
						</tr>
						<tr>
							<th><?php echo JText::_('COM_COMMUNITYPOLLS_FIELD_MAXIMUM_ANSWERS_LABEL');?></th>
							<td><?php echo $this->item->max_answers;?></td>
						</tr>
						<?php endif;?>
					</tbody>
				</table>
			</div>
		</div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="pid" value="<?php echo $this->item->id?>" />
		<input type="hidden" name="boxchecked" id="boxchecked" value="0">
		<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
