<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2023 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined( '_JEXEC' ) or die;

$items            = $displayData['items'];
$pagination       = $displayData['pagination'];
$params           = $displayData['params'];
$theme            = $params->get( 'theme', 'default' );
$avatarComponent  = $params->get( 'avatar_component', 'none' );
$profileComponent = $params->get( 'profile_component', 'none' );
$avatarSize       = $params->get( 'list_avatar_size', 48 );
$api              = new CjLibApi();
$user             = Factory::getUser();

if ( ! empty( $items ) )
{
	?>
    <div class="crosswords-list-wrap mt-3">
        <div class="list-group crosswords-list">
			<?php
			foreach ( $items as $crosswordNum => $item )
			{
				$author       = $item->author;
				$profileUrl   = $api->getUserProfileUrl( $profileComponent, $item->created_by );
				$crosswordUri = CrosswordsHelperRoute::getCrosswordRoute( $item->slug, $item->catslug, $item->language );
				$userAvatar   = $api->getUserAvatarImage( $avatarComponent, $item->created_by, $item->author_email, $avatarSize, true );
				?>
                <div class="crosswords-list-item list-group-item">
                    <div class="d-flex flex-row">

						<?php if ( $avatarComponent != 'none' ): ?>
                            <div class="d-none d-sm-block me-3">
								<?php if ( $profileComponent != 'none' ): ?>
                                    <a href="<?php echo $profileUrl; ?>" title="<?php echo $author ?>" class="thumbnail no-margin-bottom" data-bs-toggle="tooltip">
                                        <img src="<?php echo $userAvatar; ?>" alt="<?php echo $author; ?>" class="media-object" style="min-width: <?php echo $avatarSize; ?>px">
                                    </a>
								<?php else: ?>
                                    <div class="thumbnail">
                                        <img src="<?php echo $userAvatar; ?>" alt="<?php echo $author; ?>" class="media-object" style="min-width: <?php echo $avatarSize; ?>px">
                                    </div>
								<?php endif; ?>
                            </div>
						<?php endif; ?>

                        <div class="w-100">
                            <div class="crossword-title-wrap">
								<?php
								if ( $item->locked )
								{
									?>
                                    <small class="text-muted text-success" title="<?php echo Text::_( 'COM_CROSSWORDS_TOPIC_LOCKED' ) ?>"
                                           data-bs-toggle="tooltip"><i
                                                class="fa fa-lock"></i></small>
									<?php
								}

								if ( $item->attachments > 0 )
								{
									?>
                                    <small class="text-muted" title="<?php echo Text::_( 'COM_CROSSWORDS_ATTACHMENTS' ) ?>" data-bs-toggle="tooltip"><i
                                                class="fa fa-paperclip"></i></small>
									<?php
								}

								if ( in_array( $item->access, $user->getAuthorisedViewLevels() ) )
								{
									?><a href="<?php echo Route::_( $crosswordUri ); ?>"
                                         class="fs-5 fw-light crossword-title"><?php echo $this->escape( $item->title ); ?></a><?php
								}
								else
								{
									echo $this->escape( $item->title ) . ' : ';

									$itemId  = Factory::getApplication()->getMenu()->getActive()->id;
									$fullURL = Route::_( 'index.php?option=com_users&view=login&Itemid=' . $itemId . '&return=' . base64_encode( $crosswordUri ) );
									?>
                                    <a href="<?php echo $fullURL; ?>" class="register">
										<?php echo Text::_( 'COM_CROSSWORDS_REGISTER_TO_READ_MORE' ); ?>
                                    </a>
									<?php
								}
								?>
                            </div>

                            <ul class="list-inline forum-info">
								<?php if ( $item->state == 0 || $item->state == - 2 || $item->featured == 1 ): ?>
                                    <li class="list-inline-item">
										<?php if ( $item->state == 0 ): ?>
                                            <span class="label label-warning"><?php echo Text::_( 'JUNPUBLISHED' ); ?></span>
										<?php endif; ?>

										<?php if ( $item->state == - 2 ): ?>
                                            <span class="label label-danger"><?php echo Text::_( 'JTRASHED' ); ?></span>
										<?php endif; ?>
                                    </li>
								<?php endif; ?>
                                <li class="list-inline-item text-muted">
									<?php
									if ( $profileComponent != 'none' )
									{
										$profileLink = HTMLHelper::link( $profileUrl, $author );
										echo Text::sprintf( 'COM_CROSSWORDS_POSTED_BY', $profileLink );
									}
									else
									{
										echo Text::sprintf( 'COM_CROSSWORDS_POSTED_BY', $author );
									}
									?>
                                </li>
								<?php if ( $params->get( 'list_show_parent', 1 ) == 1 ): ?>
                                    <li class="list-inline-item text-muted">
										<?php echo Text::sprintf( 'COM_CROSSWORDS_CATEGORY_IN',
											HTMLHelper::link( CrosswordsHelperRoute::getCategoryRoute( $item->catid, $item->language ), $item->category_title ) ); ?>
                                    </li>
								<?php endif; ?>

								<?php if ( isset( $item->displayDate ) ): ?>
                                    <li class="list-inline-item text-muted">
										<?php echo CjLibDateUtils::getHumanReadableDate( $item->displayDate ); ?>.
                                    </li>
								<?php endif; ?>
                            </ul>

							<?php if ( $params->get( 'list_show_tags', 1 ) && ! empty( $item->tags ) ): ?>
                                <div class="margin-top-5 tags">
									<?php
									$item->tagLayout = new FileLayout( 'joomla.content.tags' );
									echo $item->tagLayout->render( $item->tags->itemTags );
									?>
                                </div>
							<?php endif; ?>

							<?php if ( $params->get( 'list_show_intro' ) ): ?>
                                <div class="text-muted"><?php echo HTMLHelper::_( 'string.truncate', strip_tags( $item->introtext ),
										(int) $params->get( 'list_intro_limit', 180 ) ); ?></div>
							<?php endif; ?>
                        </div>

						<?php if ( $params->get( 'list_show_hits', 1 ) == 1 ): ?>
                            <div class="d-none d-sm-block ms-2">
                                <div class="card card-<?php echo $theme; ?> item-count-box">
                                    <div class="card-body center item-count-num"><?php echo CjLibUtils::formatNumber( $item->hits ); ?></div>
                                    <div class="card-footer text-nowrap text-muted item-count-caption"><?php echo Text::plural( 'COM_CROSSWORDS_HITS',
											$item->hits ); ?></div>
                                </div>
                            </div>
						<?php endif; ?>
                    </div>
                </div>
				<?php
			}
			?>
        </div>

		<?php if ( ( $params->get( 'list_show_pagination', 2 ) == 2 || ( $params->get( 'list_show_pagination', 2 ) == 3 ) ) && ( $pagination->pagesTotal > 1 ) ) : ?>
            <form action="<?php echo htmlspecialchars( Uri::getInstance()->toString() ); ?>" method="post" name="adminForm" id="adminForm" class="no-margin-bottom clearfix">
                <div class="d-flex">
					<?php echo $pagination->getPagesLinks(); ?>
					<?php if ( $params->get( 'show_pagination_results', 1 ) ) : ?>
                        <p class="counter ms-auto mt-3">
							<?php echo $pagination->getPagesCounter(); ?>
                        </p>
					<?php endif; ?>
                </div>
            </form>
		<?php endif; ?>
    </div>
	<?php
}
elseif ( $params->get( 'show_no_crosswords' ) )
{
	?>
    <div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo Text::_( 'COM_CROSSWORDS_NO_TOPICS' ) ?></div>
	<?php
}
