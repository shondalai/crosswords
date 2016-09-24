<?php
/**
 * @version		$Id: default.php 01 2011-08-13 11:37:09Z maverick $
 * @package		CoreJoomla.Crosswords
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2011 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;
?>

<div id="cj-wrapper" class="cj-wrapper-main">
	<?php include_once JPATH_COMPONENT.DS.'helpers'.DS.'header.php';?>
	
	<div class="container-fluid no-space-left no-space-right crosswords-wrapper">
		<div class="row-fluid">
			<div class="span12">
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th><?php echo JText::_('JGLOBAL_TITLE');?></th>
							<th><?php echo JText::_('JDATE');?></th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($this->items)):?>
						<?php foreach ($this->items as $item):?>
						<tr>
							<td><?php echo $this->escape($item->title);?></td>
							<td><?php echo CJFunctions::get_formatted_date($item->created);?></td>
						</tr>
						<?php endforeach;?>
						<?php endif;?>
					</tbody>
				</table>
				
				<div class="row-fluid margin-top-20">
					<div class="span12 clearfix">
						<?php if ($this->params->def('show_pagination_results', 1)) : ?>
				 		<p class="counter pull-right"><?php echo $this->pagination->getPagesCounter(); ?></p>
						<?php endif; ?>
						<?php echo $this->pagination->getPagesLinks(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>