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
	
		<div id="listing-categories" class="row-fluid">
			<div class="span12">
			
				<?php echo CJFunctions::load_module_position('crosswords-list-above-categories');?>
				
				<?php if($this->params->get('display_cat_list', 1) == 1 || !empty($this->page_header)):?>
				<div class="well">
					
					<?php if(!empty($this->categories) || !empty($this->category)):?>
					<h2 class="page-header no-space-top">
						<?php echo JText::_('COM_CROSSWORDS_CATEGORIES').(!empty($this->category) ? ': <small>'.$this->escape($this->category->title).'</small>' : '');?>
						
						<?php if($this->params->get('enable_rss_feed', 0) == '1'):?>
						<a href="<?php echo JRoute::_('index.php?option='.CW_APP_NAME.'&view=crosswords&task=feed&format=feed'.$catparam.$itemid);?>" 
							title="<?php echo JText::_('COM_CROSSWORDS_RSS_FEED')?>" class="tooltip-hover">
							<i class="cjicon-feed"></i>
						</a>
						<?php endif;?>
					</h2>
					<?php elseif(!empty($this->page_header)):?>
					<h2 class="page-header margin-bottom-10 no-space-top"><?php echo $this->escape($this->page_header);?></h2>
					<?php endif;?>
					
					<?php if(!empty($this->page_description)):?>
					<div class="margin-bottom-10"><?php echo $this->page_description;?></div>
					<?php endif;?>
					
					<?php 
					if($this->params->get('display_cat_list', 1) == 1){
	
						echo CJFunctions::get_joomla_categories_table_markup
							(
								$this->categories, 
								array(
									'max_columns'=>$this->params->get('num_cat_list_columns', 3), 
									'max_children'=>0, 
									'base_url'=>$base_uri.'&task='.$this->action, 
									'menu_id'=>$itemid,
									'stat_primary'=>'numitems',
									'stat_tooltip'=>'COM_CROSSWORDS_CATEGORY_TOOLTIP'
								)
							);
					} 
					?>
					
					<?php if($this->params->get('dispay_search_box', 1) == 1):?>
					<div class="row-fluid margin-top-10">
						<div class="span12">
							<form action="<?php echo JRoute::_('index.php?option='.CW_APP_NAME.'&view=crosswords&task=search'.$itemid);?>" style="text-align: center;" class="no-margin-bottom">
								<div class="input-append center">
									<input type="text" class="search-box required" name="q" placeholder="<?php echo JText::_('COM_CROSSWORDS_SEARCH');?>">
									<button type="submit" class="btn"><?php echo JText::_('COM_CROSSWORDS_SEARCH');?></button>
								</div>
								<?php if(!empty($this->category)):?>
								<input type="hidden" name="catid" value="<?php echo $this->category->id;?>">
								<?php endif;?>
							</form>
						</div>
					</div>
					<?php endif;?>
					
				</div>
				<?php endif;?>
				
				<?php echo CJFunctions::load_module_position('crosswords-list-below-categories');?>
			</div>
		</div>
		
		<?php if(!empty($this->items)):?>
		<div id="listing-body" class="row-fluid">
			<div class="span12">
				<?php foreach ($this->items as $item):?>
				<div class="media">
					<?php if($this->params->get('user_avatar') != 'none'):?>
					<div class="pull-left margin-right-10 avatar hidden-phone">
						<?php echo CJFunctions::get_user_avatar(
							$this->params->get('user_avatar'), 
							$item->created_by, 
							$this->params->get('user_display_name'), 
							$this->params->get('avatar_size'),
							$item->email,
							array('class'=>'thumbnail tooltip-hover', 'title'=>$item->user_name),
							array('class'=>'media-object', 'style'=>'height:'.$this->params->get('avatar_size').'px'));?>
					</div>
					<?php endif;?>
					
					<?php if($this->params->get('display_hits_count', 1) == 1):?>
					<div class="pull-left hidden-phone thumbnail num-box">
						<h2 class="num-header"><?php echo $item->hits;?></h2>
						<span class="muted"><?php echo $item->hits == 1 ? JText::_('COM_CROSSWORDS_HIT') : JText::_('COM_CROSSWORDS_HITS');?></span>
					</div>
					<?php endif;?>
					
					<div class="media-body">

						<h4 class="media-heading">
							<a href="<?php echo JRoute::_('index.php?option='.CW_APP_NAME.'&view=crosswords&task=view&id='.$item->id.':'.$item->alias.$itemid);?>">
								<?php echo $this->escape($item->title)?>
							</a>
						</h4>
						
						<?php if($this->params->get('display_meta_info', 1) == 1):?>
						<div class="muted">
							<small>
							<?php 
							$category_name = JHtml::link(
								JRoute::_($base_uri.'&task='.$this->action.'&id='.$item->catid.':'.$item->category_alias.$itemid),
								$this->escape($item->category_title));
							$user_name = $item->created_by > 0 
								? CJFunctions::get_user_profile_link($this->params->get('user_avatar'), $item->created_by, $this->escape($item->user_name))
								: $this->escape($item->username);
							$formatted_date = CJFunctions::get_formatted_date($item->created);
							
							echo JText::sprintf('COM_CROSSWORDS_LIST_ITEM_META', $user_name, $category_name, $formatted_date);
							?>
							</small>
						</div>
						<div class="muted"><small><?php echo JText::sprintf('COM_CROSSWORDS_N_PEOPLE_SOLVED', $item->solved);?></small></div>
						<?php endif;?>
					</div>
				</div>
				<?php endforeach;?>
			</div>
		</div>
		
		<div class="pagination margin-top-20">
			<div class="clearfix">
				<?php if ($this->params->def('show_pagination_results', 1)) : ?>
		 		<p class="counter pull-right"><?php echo $this->pagination->getPagesCounter(); ?></p>
				<?php endif; ?>
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		</div>

		<?php else:?>
		<div class="alert alert-info"><i class="icon-info-sign"></i> <?php echo JText::_('COM_CROSSWORDS_MSG_NO_RESULTS')?></div>
		<?php endif;?>
		
	</div>
</div>