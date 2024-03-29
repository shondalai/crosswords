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

$item    = $displayData['item'];
$params  = $displayData['params'];
$form    = $displayData['form'];
$helper  = $displayData['helper'];
$tabName = 'com-crosswords-form';
?>
<form action="<?php echo Route::_( 'index.php?option=com_crosswords&id=' . (int) $item->id ); ?>" method="post" name="crosswordForm" id="crosswordForm"
      class="form-validate form-vertical mt-3"
      enctype="multipart/form-data">
    <fieldset>
		<?php echo HTMLHelper::_( $helper . '.startTabSet', $tabName, [ 'active' => 'editor' ] ); ?>
		<?php echo HTMLHelper::_( $helper . '.addTab', $tabName, 'editor', Text::_( 'COM_CROSSWORDS_CONTENT' ) ); ?>
		<?php echo $form->renderField( 'title' ); ?>

		<?php if ( is_null( $item->id ) && $params->get( 'show_alias_field' ) ) : ?>
			<?php echo $form->renderField( 'alias' ); ?>
		<?php endif; ?>

		<?php echo $form->renderField( 'catid' ); ?>

		<?php if ( $params->get( 'show_language_options', 1 ) ): ?>
			<?php echo $form->renderField( 'language' ); ?>
		<?php endif; ?>

		<?php echo $form->renderField( 'tags' ); ?>

        <div class="clearfix">
			<?php echo $form->getInput( 'description' ); ?>
        </div>

		<?php echo HTMLHelper::_( $helper . '.endTab' ); ?>

		<?php if ( $params->get( 'show_publishing_options', 1 ) == 1 ): ?>
			<?php echo HTMLHelper::_( $helper . '.addTab', $tabName, 'publishing', Text::_( 'COM_CROSSWORDS_PUBLISHING' ) ); ?>

			<?php if ( $item->params->get( 'access-change' ) ) : ?>
				<?php echo $form->renderField( 'state' ); ?>
				<?php echo $form->renderField( 'featured' ); ?>

				<?php if ( $params->get( 'show_publishing_options', 1 ) == 1 ) : ?>
					<?php echo $form->renderField( 'publish_up' ); ?>
					<?php echo $form->renderField( 'publish_down' ); ?>
				<?php endif; ?>
			<?php endif; ?>
			<?php echo $form->renderField( 'access' ); ?>

			<?php if ( is_null( $item->id ) ) : ?>
                <div class="control-group">
                    <div class="control-label">
                    </div>
                    <div class="controls">
						<?php echo Text::_( 'COM_CROSSWORDS_ORDERING' ); ?>
                    </div>
                </div>
			<?php endif; ?>
			<?php echo HTMLHelper::_( $helper . '.endTab' ); ?>
		<?php endif; ?>

		<?php if ( $params->get( 'show_metadata_options', 1 ) ): ?>
			<?php echo HTMLHelper::_( $helper . '.addTab', $tabName, 'metadata', Text::_( 'COM_CROSSWORDS_METADATA' ) ); ?>
			<?php echo $form->renderField( 'metadesc' ); ?>
			<?php echo $form->renderField( 'metakey' ); ?>
			<?php echo HTMLHelper::_( $helper . '.endTab' ); ?>
		<?php endif; ?>
        
		<?php echo HTMLHelper::_( $helper . '.endTabSet' ); ?>

		<?php echo HTMLHelper::_( 'form.token' ); ?>
    </fieldset>
</form>
