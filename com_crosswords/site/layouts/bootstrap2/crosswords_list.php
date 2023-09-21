<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2021 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined( '_JEXEC' ) or die;

$data       = $displayData;
$items      = $displayData['items'];
$pagination = $displayData['pagination'];
$params     = $displayData['params'];
$action     = isset( $displayData['action'] ) ? $displayData['action'] : '';
$api        = new CjLibApi();
?>
<div class="container-fluid no-space-left no-space-right crosswords-wrapper">

	<?php if ( ! empty( $items ) ): ?>
        <div id="listing-body" class="row-fluid">
            <div class="span12">
				<?php foreach ( $items as $item ): ?>
                    <div class="media">
						<?php if ( $params->get( 'user_avatar' ) != 'none' ): ?>
                            <div class="pull-left margin-right-10 avatar hidden-phone">
								<?php echo $api->getUserAvatar(
									$params->get( 'user_avatar' ),
									$params->get( 'user_avatar' ),
									$item->created_by,
									$params->get( 'user_display_name' ),
									$params->get( 'avatar_size' ),
									$item->email,
									[ 'class' => 'thumbnail tooltip-hover', 'title' => $item->user_name ],
									[ 'class' => 'media-object', 'style' => 'height:' . $params->get( 'avatar_size' ) . 'px' ] ); ?>
                            </div>
						<?php endif; ?>

						<?php if ( $params->get( 'display_hits_count', 1 ) == 1 ): ?>
                            <div class="pull-left hidden-phone thumbnail num-box">
                                <h2 class="num-header"><?php echo $item->hits; ?></h2>
                                <span class="muted"><?php echo $item->hits == 1 ? Text::_( 'COM_CROSSWORDS_HIT' ) : Text::_( 'COM_CROSSWORDS_HITS' ); ?></span>
                            </div>
						<?php endif; ?>

                        <div class="media-body">

                            <h4 class="media-heading">
                                <a href="<?php echo Route::_( CrosswordsHelperRoute::getCrosswordRoute( $item->id . ':' . $item->alias, $item->catid ) ); ?>">
									<?php echo $this->escape( $item->title ) ?>
                                </a>
                            </h4>

							<?php if ( $params->get( 'display_meta_info', 1 ) == 1 ): ?>
                                <div class="muted">
                                    <small>
										<?php
										$category_name  = HTMLHelper::link( Route::_( 'index.php?task=' . $action . '&id=' . $item->catid . ':' . $item->category_alias ),
											$this->escape( $item->category_title ) );
										$user_name      = $item->created_by > 0
											? $api->getUserProfileUrl( $params->get( 'user_avatar' ), $item->created_by . false, $this->escape( $item->user_name ) )
											: $this->escape( $item->username );
										$formatted_date = CjLibDateUtils::getHumanReadableDate( $item->created );

										echo Text::sprintf( 'COM_CROSSWORDS_LIST_ITEM_META', $user_name, $category_name, $formatted_date );
										?>
                                    </small>
                                </div>
                                <div class="muted"><small><?php echo Text::sprintf( 'COM_CROSSWORDS_N_PEOPLE_SOLVED', $item->solved ); ?></small></div>
							<?php endif; ?>
                        </div>
                    </div>
				<?php endforeach; ?>
            </div>
        </div>

        <div class="pagination margin-top-20">
            <div class="clearfix">
				<?php if ( $params->def( 'show_pagination_results', 1 ) ) : ?>
                    <p class="counter pull-right"><?php echo $pagination->getPagesCounter(); ?></p>
				<?php endif; ?>
				<?php echo $pagination->getPagesLinks(); ?>
            </div>
        </div>

	<?php else: ?>
        <div class="alert alert-info"><i class="icon-info-sign"></i> <?php echo Text::_( 'COM_CROSSWORDS_MSG_NO_RESULTS' ) ?></div>
	<?php endif; ?>

</div>
