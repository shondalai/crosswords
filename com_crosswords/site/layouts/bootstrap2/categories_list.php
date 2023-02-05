<?php
/**
 * @package     Crosswords
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2023 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die;
?>
<div id="listing-categories" class="row-fluid">
    <div class="span12">

		<?php echo CJFunctions::load_module_position( 'crosswords-list-above-categories' ); ?>

		<?php if ( $this->params->get( 'display_cat_list', 1 ) == 1 || ! empty( $this->page_header ) ): ?>
            <div class="well">

				<?php if ( ! empty( $this->categories ) || ! empty( $this->category ) ): ?>
                    <h2 class="page-header no-space-top">
						<?php echo JText::_( 'COM_CROSSWORDS_CATEGORIES' ) . ( ! empty( $this->category ) ? ': <small>' . $this->escape( $this->category->title ) . '</small>'
								: '' ); ?>

						<?php if ( $this->params->get( 'enable_rss_feed', 0 ) == '1' ): ?>
                            <a href="<?php echo JRoute::_( 'index.php?option=' . CW_APP_NAME . '&view=crosswords&task=feed&format=feed' . $catparam . $itemid ); ?>"
                               title="<?php echo JText::_( 'COM_CROSSWORDS_RSS_FEED' ) ?>" class="tooltip-hover">
                                <i class="cjicon-feed"></i>
                            </a>
						<?php endif; ?>
                    </h2>
				<?php elseif ( ! empty( $this->page_header ) ): ?>
                    <h2 class="page-header margin-bottom-10 no-space-top"><?php echo $this->escape( $this->page_header ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $this->page_description ) ): ?>
                    <div class="margin-bottom-10"><?php echo $this->page_description; ?></div>
				<?php endif; ?>

				<?php if ( $this->params->get( 'dispay_search_box', 1 ) == 1 ): ?>
                    <div class="row-fluid margin-top-10">
                        <div class="span12">
                            <form action="<?php echo JRoute::_( 'index.php?option=' . CW_APP_NAME . '&view=crosswords&task=search' . $itemid ); ?>" style="text-align: center;"
                                  class="no-margin-bottom">
                                <div class="input-append center">
                                    <input type="text" class="search-box required" name="q" placeholder="<?php echo JText::_( 'COM_CROSSWORDS_SEARCH' ); ?>">
                                    <button type="submit" class="btn"><?php echo JText::_( 'COM_CROSSWORDS_SEARCH' ); ?></button>
                                </div>
								<?php if ( ! empty( $this->category ) ): ?>
                                    <input type="hidden" name="catid" value="<?php echo $this->category->id; ?>">
								<?php endif; ?>
                            </form>
                        </div>
                    </div>
				<?php endif; ?>

            </div>
		<?php endif; ?>

		<?php echo CJFunctions::load_module_position( 'crosswords-list-below-categories' ); ?>
    </div>
</div>
