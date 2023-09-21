<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2021 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined( '_JEXEC' ) or die;

$item              = $displayData['item'];
$params            = $displayData['params'];
$avatarComponent   = $params->get( 'avatar_component', 'none' );
$commentsComponent = $params->get( 'comments_component', 'none' );
$displayName       = $params->get( 'user_display_name', 'name' );
$user              = Factory::getUser();
$api               = new CjLibApi();

$user_profile_link = $item->created_by > 0
	? $api->getUserProfileUrl( $avatarComponent, $item->created_by, false, $this->escape( $item->user_name ) )
	: $this->escape( $item->user_name );
$category_name     = HTMLHelper::link(
	Route::_( 'index.php?option=' . CW_APP_NAME . '&view=crosswords&id=' . $item->catid . ':' . $item->category_alias ),
	$this->escape( $item->category_title ) );
$formatted_date    = CjLibDateUtils::getHumanReadableDate( $item->created );
?>

<div class="container-fluid no-space-left no-space-right crosswords-wrapper">

    <div class="row-fluid">
        <div class="span12">

            <div class="media margin-bottom-20">
                <div class="pull-left avatar">
					<?php echo $api->getUserAvatar( $avatarComponent, $avatarComponent, $item->created_by, $displayName, 64, $item->email,
						[ 'class' => 'thumbnail' ], [ 'class' => 'media-object' ] ); ?>
                </div>
                <div class="media-body">

                    <h2 class="no-margin-top"><?php echo $this->escape( $item->title ); ?></h2>

                    <div class="description">
						<?php echo CJFunctions::process_html(
							$item->description,
							$params->get( 'default_editor' ) == 'bbcode',
							$params->get( 'process_content_plugins', false ) == 1 ); ?>
                    </div>

                    <div class="muted margin-top-10">
						<?php echo Text::sprintf( 'COM_CROSSWORDS_LIST_ITEM_META', $user_profile_link, $category_name, $formatted_date ); ?>
                    </div>

                    <div class="crossword-controls margin-top-10">
						<?php if ( ( ! $user->guest && $user->id == $item->created_by && $user->authorise( 'core.edit', CW_APP_NAME ) )
						           || $user->authorise( 'core.manage', CW_APP_NAME ) ): ?>
                            <a href="<?php echo Route::_( 'index.php?option=' . CW_APP_NAME . '&view=crosswords&task=edit&id=' . $item->id . ':' . $item->alias ); ?>"
                               class="muted btn-edit-crossword"><?php echo Text::_( 'JGLOBAL_EDIT' ); ?></a>
						<?php endif; ?>
                    </div>
                </div>
            </div>

            <form id="crossword-form" action="<?php echo Route::_( 'index.php?option=' . CW_APP_NAME . '&view=crosswords&task=check_result' ); ?>" method="post">
                <table id="crossword-grid" style="width: auto;">
                    <tbody>
                    <tr class="grid-header">
                        <td colspan="<?php echo $item->columns + 2 ?>" class="question-highlight-box">
							<?php if ( $item->solved == '1' ): ?>
								<?php echo Text::_( "COM_CROSSWORDS_YOU_HAVE_SOLVED" ); ?>
							<?php else: ?>
								<?php echo Text::_( 'COM_CROSSWORDS_NO_QUESTION_SELECTED' ); ?>
							<?php endif; ?>
                        </td>
                    </tr>
					<?php for ( $row = - 1; $row <= $item->rows; $row ++ ): ?>
                        <tr>

							<?php for ( $col = - 1; $col <= $item->columns; $col ++ ): ?>

								<?php if ( $item->cells[$row][$col]->valid ): ?>
                                    <td style="padding: 0px !important;">
                                        <div class="textcell<?php echo $item->cells[$row][$col]->topclaz ?>" style="width: 25px !important; height: 25px !important;">
                                            <div class="<?php echo $item->cells[$row][$col]->bottomclaz ?>" style="width: 25px !important; height: 25px !important;">
                                                <input <?php echo $item->solved == '1' ? 'readonly="readonly"' : ''; ?>
                                                        name="cell_<?php echo $col . '_' . $row ?>"
                                                        type="text"
                                                        id="<?php echo $item->cells[$row][$col]->id; ?>"
                                                        class="<?php echo implode( ' ', $item->cells[$row][$col]->claz ) ?>"
                                                        value="<?php echo $item->cells[$row][$col]->value; ?>"
                                                        maxlength="1"
                                                        style="width: 25px !important; height: 25px !important; line-height: 20px !important; padding: 0px !important;">
                                            </div>
                                        </div>
                                    </td>
								<?php else: ?>
                                    <td class="disabled"></td>
								<?php endif; ?>

							<?php endfor; ?>

                        </tr>
					<?php endfor; ?>
                    </tbody>
                </table>
                <input type="hidden" name="id" value="<?php echo $item->id; ?>"/>
            </form>
        </div>
    </div>

	<?php if ( $item->solved != '1' ): ?>
        <div class="row-fluid margin-top-20 navigation">
            <button class="btn btn-primary" id="btn-check-result"><?php echo Text::_( 'COM_CROSSWORDS_CHECK_RESULT' ); ?></button>

			<?php if ( $params->get( 'enable_solve_question', 1 ) == 1 ): ?>
                <button class="btn btn-info" id="btn-solve-question"><?php echo Text::_( 'COM_CROSSWORDS_SOLVE_QUESTION' ); ?></button>
			<?php endif; ?>

            <button class="btn btn-success" id="btn-solve-crossword"><?php echo Text::_( 'COM_CROSSWORDS_SOLVE_CROSSWORD' ); ?></button>
        </div>
	<?php endif; ?>

    <div class="row-fluid">

		<?php echo CJFunctions::load_module_position( 'cw_details_below_navigation' ); ?>

		<?php
		$h_questions = "";
		$v_questions = "";
		foreach ( $item->questions as $question )
		{
			if ( $question->axis == '1' )
			{
				$h_questions = $h_questions .
				               '<tr class="question-title" id="x-' . $question->position . '">
 						<td width="12px">' . $question->position . '</td>
 						<td>' . $this->escape( $question->question ) . '</td>
					</tr>';
			}
			else
			{
				$v_questions = $v_questions .
				               '<tr class="question-title" id="y-' . $question->position . '">
						<td width="12px">' . $question->position . '</td>
						<td>' . $this->escape( $question->question ) . '</td>
					</tr>';
			}
		}
		?>

        <div class="questions">
            <div class="span6">
                <h3 class="page-header margin-bottom-10 no-pad-bottom"><?php echo Text::_( "COM_CROSSWORDS_ACROSS" ); ?></h3>
                <table class="table table-bordered table-striped table-hover">
                    <tbody><?php echo $h_questions; ?></tbody>
                </table>
            </div>
            <div class="span6">
                <h3 class="page-header margin-bottom-10 no-pad-bottom"><?php echo Text::_( "COM_CROSSWORDS_DOWN" ); ?></h3>
                <table class="table table-bordered table-striped table-hover">
                    <tbody><?php echo $v_questions; ?></tbody>
                </table>
            </div>
        </div>
    </div>

    <!--*********************** START: Solved Users **********************-->
    <div class="row-fluid">
        <div class="span12">
            <h3 class="page-header margin-bottom-10"><?php echo Text::_( 'COM_CROSSWORDS_WHO_SOLVED_THIS' ); ?></h3>
			<?php if ( ! empty( $item->users_solved ) ): ?>
                <div class="solved-users-listing clearfix">
					<?php foreach ( $item->users_solved as $solved_user ): ?>
                        <div class="pull-left margin-right-10 margin-bottom-10">
							<?php if ( $params->get( 'user_avatar', 'none' ) != 'none' ): ?>
								<?php echo $api->getUserAvatar(
									$params->get( 'User_avatar' ),
									$params->get( 'user_avatar' ),
									$solved_user->id,
									$params->get( 'user_display_name' ),
									$params->get( 'avatar_size' ),
									$solved_user->email,
									[ 'class' => 'thumbnail tooltip-hover', 'title' => $this->escape( $solved_user->user_name ) ],
									[ 'class' => 'media-object', 'style' => 'height:' . $params->get( 'avatar_size' ) . 'px' ] ); ?>
							<?php else: ?>
                                <div class="pull-left margin-right-20 margin-bottom-10">
                                    &bull;&nbsp;<?php echo $this->escape( $solved_user->user_name ); ?>
                                </div>
							<?php endif; ?>
                        </div>
					<?php endforeach; ?>
                </div>
			<?php else: ?>
                <div class="alert alert-info"><?php echo Text::_( 'COM_CROSSWORDS_NO_USERS_SOLVED_THIS' ); ?></div>
			<?php endif; ?>
        </div>
    </div>
    <!--*********************** END: Solved Users ***********************-->

    <!--********************** START: Social Sharing *********************-->
    <div class="row-fluid">
        <div class="span12">
            <div class="social-sharing">
				<?php if ( ! empty( $sharing_services ) ): ?>
                    <h3 class="page-header margin-bottom-10"><?php echo Text::_( 'COM_CROSSWORDS_SHARE_THIS_CROSSWORD' ); ?></h3>
                    <p><?php echo Text::_( 'COM_CROSSWORDS_SOCIAL_SHARING_DESC' ); ?></p>
                    <div class="addthis_toolbox addthis_default_style ">
						<?php if ( in_array( 'fblike', $sharing_services ) ): ?>
                            <a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
						<?php endif; ?>
						<?php if ( in_array( 'tweet', $sharing_services ) ): ?>
                            <a class="addthis_button_tweet"></a>
						<?php endif; ?>
						<?php if ( in_array( 'googleplus', $sharing_services ) ): ?>
                            <a class="addthis_button_google_plusone" g:plusone:size="medium"></a>
						<?php endif; ?>
						<?php if ( in_array( 'addthis', $sharing_services ) ): ?>
                            <a class="addthis_counter addthis_pill_style"></a>
						<?php endif; ?>
                    </div>
				<?php endif; ?>
            </div>
        </div>
    </div>
    <!--*********************** END: Social Sharing ***********************-->

    <!--************************* START: Comments *************************-->
	<?php if ( $commentsComponent != 'none' ): ?>
        <div id="cwcomments" class="row-fluid">
            <div class="span12">
                <h3 class="page-header"><?php echo Text::_( 'COM_CROSSWORDS_COMMENTS' ); ?></h3>
				<?php
				$fburl = Route::_( 'index.php?option=' . CW_APP_NAME . '&view=crosswords&task=view&id=' . $item->id . ':' . $item->alias, false, - 1 );
				echo CJFunctions::load_comments( $comment_system, CW_APP_NAME, $item->id, $this->escape( $item->title ), $fburl,
					$params->get( 'disqus_intdbt_id' ), $item );
				?>
            </div>
        </div>
	<?php endif; ?>
    <!--************************* END: Comments *************************-->

    <div style="display: none;">
        <input type="hidden" id="cjpageid" value="crossword_deails">
        <span id="lbl_cancel"><?php echo Text::_( "COM_CROSSWORDS_CANCEL" ); ?></span>
        <span id="lbl_info"><?php echo Text::_( "COM_CROSSWORDS_INFO" ); ?></span>
        <span id="lbl_alert"><?php echo Text::_( "COM_CROSSWORDS_ALERT" ); ?></span>
        <span id="lbl_error"><?php echo Text::_( "COM_CROSSWORDS_ERROR" ); ?></span>
        <span id="lbl_confirm"><?php echo Text::_( "COM_CROSSWORDS_CONFIRM" ); ?></span>
        <span id="msg_failed_answers"><?php echo Text::_( "COM_CROSSWORDS_MSG_CROSSWORD_UNSOLVED" ); ?></span>
        <span id="msg_select_question"><?php echo Text::_( 'COM_CROSSWORDS_MSG_SELECT_QUESTION' ); ?></span>
        <img id="progress-confirm" alt="..." src="<?php echo CW_MEDIA_URI; ?>images/ui-anim_basic_16x16.gif"/>
        <span id="url_solve_question"><?php echo Route::_( 'index.php?option=' . CW_APP_NAME . '&view=crosswords&task=solvequestion&id=' . $item->id
		                                                    . $home_itemid ); ?></span>
        <span id="url_solve_crossword"><?php echo Route::_( 'index.php?option=' . CW_APP_NAME . '&view=crosswords&task=solvecrossword&id=' . $item->id
		                                                     . $home_itemid ); ?></span>
        <span id="lbl_sharing_services"><?php echo $params->get( 'sharing_services' ); ?></span>
        <span id="enable_auto_move"><?php echo $params->get( 'enable_automove', 1 ); ?></span>
    </div>

    <div id="message-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3 id="myModalLabel"><?php echo Text::_( 'COM_CROSSWORDS_ALERT' ); ?></h3>
        </div>
        <div class="modal-body"></div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo Text::_( 'COM_CROSSWORDS_CLOSE' ); ?></button>
        </div>
    </div>

    <div id="confirm-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3 id="confirmModalLabel"><?php echo Text::_( 'COM_CROSSWORDS_ALERT' ); ?></h3>
        </div>
        <div class="modal-body"><?php echo Text::_( 'COM_CROSSWORDS_MSG_CONFIRM_SOLVE_CROSSWORD' ) ?></div>
        <div class="modal-footer">
            <button class="btn btn-cancel" data-dismiss="modal" aria-hidden="true"><i class="icon-remove"></i> <?php echo Text::_( 'COM_CROSSWORDS_CLOSE' ); ?></button>
            <button class="btn btn-primary btn-confirm-solve-crossword" aria-hidden="true"><i
                        class="icon-thumbs-up icon-white"></i> <?php echo Text::_( 'COM_CROSSWORDS_CONFIRM' ); ?></button>
        </div>
    </div>
</div>