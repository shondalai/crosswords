<?php
/**
 * @version        $Id: modal.php 01 2012-06-30 11:37:09Z maverick $
 * @package        CoreJoomla.crosswords
 * @subpackage     Components
 * @copyright      Copyright (C) 2009 - 2012 corejoomla.com. All rights reserved.
 * @author         Maverick
 * @link           http://www.corejoomla.com/
 * @license        License GNU General Public License version 2 or later
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

defined( '_JEXEC' ) or die;

// Include the component HTML helpers.
HTMLHelper::addIncludePath( JPATH_COMPONENT . '/helpers/html' );


HTMLHelper::_( 'behavior.formvalidator' );
HTMLHelper::_( 'behavior.keepalive' );
HTMLHelper::_( 'formbehavior.chosen', 'select' );

$this->hiddenFieldsets    = [];
$this->hiddenFieldsets[0] = 'basic-limited';
$this->configFieldsets    = [];
$this->configFieldsets[0] = 'editorConfig';

// Create shortcut to parameters.
$params = $this->state->get( 'params' );
//$params = $params->toArray();

$app   = Factory::getApplication();
$input = $app->input;
$assoc = Associations::isEnabled();

// This checks if the config options have ever been saved. If they haven't they will fall back to the original settings.
$params        = json_decode( $params );
$editoroptions = isset( $params->show_publishing_options );

if ( ! $editoroptions )
{
	$params->show_publishing_options   = '1';
	$params->show_poll_options         = '1';
	$params->show_urls_images_backend  = '0';
	$params->show_urls_images_frontend = '0';
}

// Check if the poll uses configuration settings besides global. If so, use them.
if ( isset( $this->item->attribs['show_publishing_options'] ) && $this->item->attribs['show_publishing_options'] != '' )
{
	$params->show_publishing_options = $this->item->attribs['show_publishing_options'];
}

if ( isset( $this->item->attribs['show_poll_options'] ) && $this->item->attribs['show_poll_options'] != '' )
{
	$params->show_poll_options = $this->item->attribs['show_poll_options'];
}

if ( isset( $this->item->attribs['show_urls_images_frontend'] ) && $this->item->attribs['show_urls_images_frontend'] != '' )
{
	$params->show_urls_images_frontend = $this->item->attribs['show_urls_images_frontend'];
}

if ( isset( $this->item->attribs['show_urls_images_backend'] ) && $this->item->attribs['show_urls_images_backend'] != '' )
{
	$params->show_urls_images_backend = $this->item->attribs['show_urls_images_backend'];
}

?>

<script type="text/javascript">
  Joomla.submitbutton = function (task) {
    if (task == 'poll.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
		<?php echo $this->form->getField( 'description' )->save(); ?>

      if (window.opener && (task == 'poll.save' || task == 'poll.cancel')) {
        window.opener.document.closeEditWindow = self
        window.opener.setTimeout('window.document.closeEditWindow.close()', 1000)
      }

      Joomla.submitform(task, document.getElementById('item-form'))
    }
  }
</script>
<div class="container-popup">

    <div class="pull-right">
        <button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('poll.apply');"><?php echo Text::_( 'JTOOLBAR_APPLY' ) ?></button>
        <button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('poll.save');"><?php echo Text::_( 'JTOOLBAR_SAVE' ) ?></button>
        <button class="btn" type="button" onclick="Joomla.submitbutton('poll.cancel');"><?php echo Text::_( 'JCANCEL' ) ?></button>
    </div>

    <div class="clearfix"></div>
    <hr class="hr-condensed"/>

    <form action="<?php echo Route::_( 'index.php?option=com_communitypolls&layout=modal&tmpl=component&id=' . (int) $this->item->id ); ?>" method="post" name="adminForm"
          id="item-form" class="form-validate">
		<?php echo LayoutHelper::render( 'joomla.edit.title_alias', $this ); ?>

        <div class="form-horizontal">
			<?php echo HTMLHelper::_( 'bootstrap.startTabSet', 'myTab', [ 'active' => 'general' ] ); ?>

			<?php echo HTMLHelper::_( 'bootstrap.addTab', 'myTab', 'general', Text::_( 'COM_COMMUNITYPOLLS_POLL_CONTENT', true ) ); ?>
            <div class="row-fluid">
                <div class="span9">
                    <fieldset class="adminform">
						<?php echo $this->form->getInput( 'description' ); ?>
                    </fieldset>
                </div>
                <div class="span3">
					<?php echo LayoutHelper::render( 'joomla.edit.global', $this ); ?>
                </div>
            </div>
			<?php echo HTMLHelper::_( 'bootstrap.endTab' ); ?>

			<?php // Do not show the publishing options if the edit form is configured not to. ?>
			<?php if ( $params->show_publishing_options == 1 ) : ?>
				<?php echo HTMLHelper::_( 'bootstrap.addTab', 'myTab', 'publishing', Text::_( 'com_communitypolls_FIELDSET_PUBLISHING', true ) ); ?>
                <div class="row-fluid form-horizontal-desktop">
                    <div class="span6">
						<?php echo LayoutHelper::render( 'joomla.edit.publishingdata', $this ); ?>
                    </div>
                    <div class="span6">
						<?php echo LayoutHelper::render( 'joomla.edit.metadata', $this ); ?>
                    </div>
                </div>
				<?php echo HTMLHelper::_( 'bootstrap.endTab' ); ?>
			<?php endif; ?>

			<?php // Do not show the images and links options if the edit form is configured not to. ?>
			<?php if ( $params->show_urls_images_backend == 1 ) : ?>
				<?php echo HTMLHelper::_( 'bootstrap.addTab', 'myTab', 'images', Text::_( 'COM_COMMUNITYPOLLS_FIELDSET_URLS_AND_IMAGES', true ) ); ?>
                <div class="row-fluid form-horizontal-desktop">
                    <div class="span6">
						<?php echo $this->form->renderField( 'images' ); ?>
						<?php foreach ( $this->form->getFieldset( 'images' ) as $field ) : ?>
							<?php echo $field->renderField(); ?>
						<?php endforeach; ?>
                    </div>
                    <div class="span6">
						<?php foreach ( $this->form->getFieldset( 'urls' ) as $field ) : ?>
							<?php echo $field->renderField(); ?>
						<?php endforeach; ?>
                    </div>
                </div>
				<?php echo HTMLHelper::_( 'bootstrap.endTab' ); ?>
			<?php endif; ?>

			<?php if ( isset( $assoc ) ) : ?>
                <div class="hidden"><?php echo $this->loadTemplate( 'associations' ); ?></div>
			<?php endif; ?>

			<?php $this->show_options = $params->show_poll_options; ?>
			<?php echo LayoutHelper::render( 'joomla.edit.params', $this ); ?>

			<?php if ( $this->canDo->get( 'core.admin' ) ) : ?>
				<?php echo HTMLHelper::_( 'bootstrap.addTab', 'myTab', 'editor', Text::_( 'COM_COMMUNITYPOLLS_SLIDER_EDITOR_CONFIG', true ) ); ?>
				<?php foreach ( $this->form->getFieldset( 'editorConfig' ) as $field ) : ?>
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $field->label; ?>
                        </div>
                        <div class="controls">
							<?php echo $field->input; ?>
                        </div>
                    </div>
				<?php endforeach; ?>
				<?php echo HTMLHelper::_( 'bootstrap.endTab' ); ?>
			<?php endif; ?>

			<?php if ( $this->canDo->get( 'core.admin' ) ) : ?>
				<?php echo HTMLHelper::_( 'bootstrap.addTab', 'myTab', 'permissions', Text::_( 'COM_COMMUNITYPOLLS_FIELDSET_RULES', true ) ); ?>
				<?php echo $this->form->getInput( 'rules' ); ?>
				<?php echo HTMLHelper::_( 'bootstrap.endTab' ); ?>
			<?php endif; ?>

			<?php echo HTMLHelper::_( 'bootstrap.endTabSet' ); ?>

            <input type="hidden" name="task" value=""/>
            <input type="hidden" name="return" value="<?php echo $input->getCmd( 'return' ); ?>"/>
			<?php echo HTMLHelper::_( 'form.token' ); ?>
        </div>
    </form>
