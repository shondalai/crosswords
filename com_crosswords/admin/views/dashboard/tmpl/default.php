<?php
/**
 * @version		$Id: default.php 01 2014-01-26 11:37:09Z maverick $
 * @package		CoreJoomla.Polls
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

$app		= JFactory::getApplication();
$user		= JFactory::getUser();
$userId		= $user->id;

if(APP_VERSION >= 3)
{
	JHtml::_('bootstrap.tooltip');
}
else 
{
	CJLib::import('corejoomla.ui.bootstrap');
	JFactory::getDocument()->addStyleSheet(CJLIB_URI.'/framework/assets/cj.framework.css');
}

CJLib::behavior('bscore');
CJLib::behavior('fontawesome');
?>
<div id="cj-wrapper">
	<div class="row-fluid">
		<div class="span8">
			
			<div class="panel panel-default">
				<div class="panel-heading">
					<strong><i class="fa fa-refresh"></i> <?php echo JText::_('COM_CROSSWORDS_LATEST_CROSSWORDS');?></strong>
				</div>
				<?php if(!$this->latest):?>
				<div class="panel-body">
					<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
				<?php else:?>
				<table class="table table-striped table-hover">
					<caption></caption>
					<thead>
						<tr>
							<th><?php echo JText::_('JGLOBAL_TITLE');?></th>
							<th width="10%" class="nowrap hidden-phone"><?php echo JText::_('JAUTHOR');?></th>
							<th width="5%" class="nowrap hidden-phone"><?php echo JText::_('JGRID_HEADING_LANGUAGE');?></th>
							<th width="10%" class="nowrap hidden-phone"><?php echo JText::_('JDATE');?></th>
							<th width="10%"><?php echo JText::_('JGLOBAL_HITS');?></th>
							<th width="1%" class="nowrap hidden-phone"><?php echo JText::_('JGRID_HEADING_ID');?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->latest as $i => $item) :
						$canEdit    = $user->authorise('core.edit',       'com_crosswords.poll.'.$item->id);
						$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
						$canEditOwn = $user->authorise('core.edit.own',   'com_crosswords.poll.'.$item->id) && $item->created_by == $userId;
						$canChange  = $user->authorise('core.edit.state', 'com_crosswords.poll.'.$item->id) && $canCheckin;
						?>
						<tr>
							<td class="has-context">
								<div>
									<?php if ($item->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'polls.', $canCheckin); ?>
									<?php endif; ?>
									<?php if ($item->language == '*'):?>
										<?php $language = JText::alt('JALL', 'language'); ?>
									<?php else:?>
										<?php $language = $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
									<?php endif;?>
									<?php if ($canEdit || $canEditOwn) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_crosswords&task=crossword.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
											<?php echo $this->escape($item->title); ?></a>
									<?php else : ?>
										<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
									<?php endif; ?>
									<div class="small">
										<?php echo JText::_('JCATEGORY') . ": " . $this->escape($item->category_title); ?>
									</div>
								</div>
							</td>
							<td class="small hidden-phone">
								<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.(int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
									<?php echo $this->escape($item->author_name); ?>
								</a>
							</td>
							<td class="small hidden-phone">
								<?php if ($item->language == '*'):?>
									<?php echo JText::alt('JALL', 'language'); ?>
								<?php else:?>
									<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
								<?php endif;?>
							</td>
							<td class="nowrap small hidden-phone">
								<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
							</td>
							<td class="center">
								<?php echo (int) $item->hits; ?>
							</td>
							<td class="center hidden-phone">
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php endif;?>
			</div>
			
			<div class="panel panel-default">
				<div class="panel-heading">
					<strong><i class="fa fa-refresh"></i> <?php echo JText::_('COM_CROSSWORDS_POPULAR_CROSSWORDS');?></strong>
				</div>
				<?php if(!$this->popular):?>
				<div class="panel-body">
					<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
				<?php else:?>
				<table class="table table-striped table-hover">
					<caption></caption>
					<thead>
						<tr>
							<th><?php echo JText::_('JGLOBAL_TITLE');?></th>
							<th width="10%" class="nowrap hidden-phone"><?php echo JText::_('JAUTHOR');?></th>
							<th width="5%" class="nowrap hidden-phone"><?php echo JText::_('JGRID_HEADING_LANGUAGE');?></th>
							<th width="10%" class="nowrap hidden-phone"><?php echo JText::_('JDATE');?></th>
							<th width="10%"><?php echo JText::_('JGLOBAL_HITS');?></th>
							<th width="1%" class="nowrap hidden-phone"><?php echo JText::_('JGRID_HEADING_ID');?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->popular as $i => $item) :
						$canEdit    = $user->authorise('core.edit',       'com_crosswords.poll.'.$item->id);
						$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
						$canEditOwn = $user->authorise('core.edit.own',   'com_crosswords.poll.'.$item->id) && $item->created_by == $userId;
						$canChange  = $user->authorise('core.edit.state', 'com_crosswords.poll.'.$item->id) && $canCheckin;
						?>
						<tr>
							<td class="has-context">
								<div>
									<?php if ($item->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'polls.', $canCheckin); ?>
									<?php endif; ?>
									<?php if ($item->language == '*'):?>
										<?php $language = JText::alt('JALL', 'language'); ?>
									<?php else:?>
										<?php $language = $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
									<?php endif;?>
									<?php if ($canEdit || $canEditOwn) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_crosswords&task=crossword.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
											<?php echo $this->escape($item->title); ?></a>
									<?php else : ?>
										<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
									<?php endif; ?>
									<div class="small">
										<?php echo JText::_('JCATEGORY') . ": " . $this->escape($item->category_title); ?>
									</div>
								</div>
							</td>
							<td class="small hidden-phone">
								<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.(int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
									<?php echo $this->escape($item->author_name); ?>
								</a>
							</td>
							<td class="small hidden-phone">
								<?php if ($item->language == '*'):?>
									<?php echo JText::alt('JALL', 'language'); ?>
								<?php else:?>
									<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
								<?php endif;?>
							</td>
							<td class="nowrap small hidden-phone">
								<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
							</td>
							<td class="center">
								<?php echo (int) $item->hits; ?>
							</td>
							<td class="center hidden-phone">
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php endif;?>
			</div>
		
		</div>
		<div class="span4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<strong><i class="fa fa-bullhorn"></i> <?php echo JText::_('COM_CROSSWORDS_TITLE_VERSION');?></strong>
				</div>
				<table class="table table-striped">
					<thead>
						<tr>
							<td colspan="2">
								<p>If you use Community Polls, please post a rating and a review at the Joomla Extension Directory</p>
								<a class="btn btn-info" href="http://extensions.joomla.org/extensions/sports-a-games/board-a-table-games/21731" target="_blank">
									<i class="icon-share icon-white"></i> <span style="color: white">Post Your Review</span>
								</a>
							</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<th><?php echo JText::_('COM_CROSSWORDS_INSTALLED_VERSION');?>:</th>
							<td><?php echo CW_CURR_VERSION;?></td>
						<tr>
						<?php if(!empty($this->version)):?>
						<tr>
							<th>Latest Version:</th>
							<td><?php echo $this->version['version'];?></td>
						</tr>
						<tr>
							<th>Latest Version Released On:</th>
							<td><?php echo $this->version['released'];?></td>
						</tr>
						<tr>
							<th>CjLib Version</th>
							<td><?php echo CJLIB_VER;?></td>
						</tr>
						<tr>
							<td colspan="2" style="text-align: center;">
								<?php if($this->version['status'] == 1):?>
								<a href="http://www.corejoomla.com/downloads.html" target="_blank" class="btn btn-danger">
									<i class="icon-download icon-white"></i> <span style="color: white">Please Update</span>
								</a>
								<?php else:?>
								<a href="#" class="btn btn-success"><i class="icon-ok icon-white"></i> <span style="color: white">Up-to date</span></a>
								<?php endif;?>
							</td>
						</tr>
						<?php endif;?>
					</tbody>
				</table>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading"><strong>Credits: </strong></div>
				<div class="panel-body">
					<div>Community Crosswords is a free software released under Gnu/GPL license. Copyright© 2009-17 corejoomla.com</div>
					<div>Core Components: Bootstrap, jQuery, FontAwesome and ofcourse Joomla<sup>&reg;</sup>.</div>
				</div>
			</div>
		</div>
	</div>
</div>