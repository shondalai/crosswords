<?php
/**
 * @package     Crosswords
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2023 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined( '_JEXEC' ) or die;

$item       = $displayData['item'];
$params     = $displayData['params'];
$user       = Factory::getUser();
$editor     = $user->authorise( 'core.wysiwyg', 'com_crosswords' ) ? $params->get( 'default_editor', 'bbcode' ) : 'none';
$categories = HTMLHelper::_( 'category.categories', 'com_crosswords' );

foreach ( $categories as $id => $category )
{
	if ( ! $user->authorise( 'core.create', 'com_crosswords.category.' . $category->value ) )
	{
		unset( $categories[$id] );
	}
}
?>
<form class="crossword-form" action="<?php echo Route::_( 'index.php?option=com_crosswords&view=crosswords&task=save_crossword' ); ?>" method="post">

    <fieldset>
        <legend><?php echo Text::_( 'COM_CROSSWORDS_BASIC_INFORMATION' ); ?></legend>

        <div class="clearfix">
            <label><?php echo Text::_( 'JGLOBAL_TITLE' ); ?><sup>*</sup>:</label>
            <input type="text" class="input-xxlarge" name="title" id="title" value="<?php echo $item->title; ?>">
        </div>

		<?php if ( $item->id > 0 ): ?>
            <div class="clearfix">
                <label><?php echo Text::_( 'COM_CROSSWORDS_ALIAS' ); ?><sup>*</sup>:</label>
                <input type="text" class="input-xxlarge" name="alias" id="alias" value="<?php echo $item->alias; ?>">
            </div>
		<?php endif; ?>

		<?php if ( $item->id == 0 ): ?>
            <div class="clearfix">
                <label><?php echo Text::_( 'COM_CROSSWORDS_GRID_SIZE' ); ?><sup>*</sup>:</label>
                <select name="grid_size" id="grid_size" size="1">
                    <option value="15"<?php echo $item->size == 15 ? ' selected="selected"' : ''; ?>>15</option>
                    <option value="20"<?php echo $item->size == 20 ? ' selected="selected"' : ''; ?>>20</option>
                    <option value="23"<?php echo $item->size == 23 ? ' selected="selected"' : ''; ?>>23</option>
                </select>
            </div>

            <div class="clearfix">
                <label><?php echo Text::_( 'COM_CROSSWORDS_DIFFICULTY_LEVEL' ) ?><sup>*</sup>:</label>
                <select name="difficulty_level" id="difficulty_level" size="1">
                    <option value="1"<?php echo $item->level == 1 ? ' selected="selected"' : ''; ?>><?php echo Text::_( 'COM_CROSSWORDS_LEVEL_EASY' ); ?></option>
                    <option value="2"<?php echo $item->level == 2 ? ' selected="selected"' : ''; ?>><?php echo Text::_( 'COM_CROSSWORDS_LEVEL_MODERATE' ); ?></option>
                    <option value="3"<?php echo $item->level == 3 ? ' selected="selected"' : ''; ?>><?php echo Text::_( 'COM_CROSSWORDS_LEVEL_HARD' ); ?></option>
                </select>
            </div>
		<?php endif; ?>

        <div class="clearfix">
            <label><?php echo Text::_( 'JGLOBAL_DESCRIPTION' ); ?>:</label>
			<?php echo CJFunctions::load_editor( $editor, 'description', 'description', $item->description, '5', '40', '100%', '200px', '', 'width: 99%;' ); ?>
        </div>

        <div class="clearfix">
            <label class="control-label" for="catid"><?php echo Text::_( 'COM_CROSSWORDS_CATEGORY' ); ?>:<sup>*</sup></label>
			<?php echo HTMLHelper::_( 'select.genericlist', $categories, 'catid', [ 'list.select' => $item->catid ] ); ?>
        </div>

    </fieldset>

	<?php if ( $item->id == 0 ): ?>
        <h3 class="margin-bottom-20">
            <a href="#" onclick="return false;" id="toggle-advanced-setup">
                <i class="icon-random"></i> <?php echo Text::_( 'COM_CROSSWORDS_ADVANCED_SETUP' ); ?>
            </a>
        </h3>
        <fieldset id="fieldset-advanced-setup" style="display: none;">

            <legend><?php echo Text::_( 'COM_CROSSWORDS_ADVANCED_SETUP' ) ?></legend>
            <div class="alert alert-info"><i class="icon-info-sign"></i> <?php echo Text::_( 'COM_CROSSWORDS_ADVANCED_INFO_ALERT' ); ?></div>
            <div class="well"><?php echo Text::_( 'COM_CROSSWORDS_ADVANCED_INFO_HELP' ); ?></div>

            <table class="multiselect-container">
                <tr>
                    <td width="45%">
                        <select name="source-list" class="input-xlarge source-list" size="10" multiple="multiple" style="height: 100% !important;"></select>
                    </td>
                    <td width="10%">
                        <button type="button" class="btn btn-mini btn-move-right tooltip-hover" title="<?php echo Text::_( 'COM_CROSSWORDS_MOVE_RIGHT' ) ?>">
                            <i class="icon icon-forward"></i>
                        </button>
                        <button type="button" class="btn btn-mini btn-move-left tooltip-hover" title="<?php echo Text::_( 'COM_CROSSWORDS_MOVE_LEFT' ) ?>">
                            <i class="icon icon-backward"></i>
                        </button>
                        <button type="button" class="btn btn-mini btn-all-right tooltip-hover" title="<?php echo Text::_( 'COM_CROSSWORDS_MOVE_ALL_RIGHT' ) ?>">
                            <i class="icon icon-fast-forward"></i>
                        </button>
                        <button type="button" class="btn btn-mini btn-all-left tooltip-hover" title="<?php echo Text::_( 'COM_CROSSWORDS_MOVE_ALL_LEFT' ) ?>">
                            <i class="icon icon-fast-backward"></i>
                        </button>
                    </td>
                    <td width="45%">
                        <select name="target-list[]" class="input-xlarge target-list" size="10" multiple="multiple" style="height: 100% !important;"></select>
                    </td>
                </tr>
                <tr>
                    <td class="center">
                        <button type="button" class="btn btn-mini btn-select-all tooltip-hover" title="<?php echo Text::_( 'COM_CROSSWORDS_SELECT_ALL' ) ?>">
                            <i class="icon icon-ok-circle"></i>
                        </button>
                        <button type="button" class="btn btn-mini btn-deselect-all tooltip-hover" title="<?php echo Text::_( 'COM_CROSSWORDS_DESELECT_ALL' ) ?>">
                            <i class="icon icon-ban-circle"></i>
                        </button>
                    </td>
                    <td></td>
                    <td class="center">
                        <button type="button" class="btn btn-mini btn-select-all tooltip-hover" title="<?php echo Text::_( 'COM_CROSSWORDS_SELECT_ALL' ) ?>">
                            <i class="icon icon-ok-circle"></i>
                        </button>
                        <button type="button" class="btn btn-mini btn-deselect-all tooltip-hover" title="<?php echo Text::_( 'COM_CROSSWORDS_DESELECT_ALL' ) ?>">
                            <i class="icon icon-ban-circle"></i>
                        </button>
                    </td>
                </tr>
            </table>

        </fieldset>
	<?php endif; ?>

    <div class="well well-transperant center">
        <a class="btn"><?php echo Text::_( 'JCANCEL' ); ?></a>
        <button type="submit" class="btn btn-primary btn-save-crossword" onclick="jQuery(this).button('loading');">
			<?php echo Text::_( 'JSUBMIT' ); ?>
        </button>
    </div>

    <input type="hidden" name="id" value="<?php echo $item->id ?>">
    <input type="hidden" id="cjpageid" value="crossword_form">
</form>

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

<div style="display: none;">
    <img id="progress-confirm" alt="..." src="<?php echo CW_MEDIA_URI; ?>images/ui-anim_basic_16x16.gif" style="display: none;"/>
    <span id="url_get_questions"><?php echo Route::_( 'index.php?option=com_crosswords&view=crosswords&task=get_questions' ); ?></span>
</div>
