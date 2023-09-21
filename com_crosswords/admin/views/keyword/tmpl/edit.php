<?php
/**
 * @version        $Id: view.html.php 01 2014-01-26 11:37:09Z maverick $
 * @package        CoreJoomla.Polls
 * @subpackage     Components
 * @copyright      Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
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

if ( APP_VERSION < 4 )
{
	HTMLHelper::_( 'behavior.formvalidator' );
	HTMLHelper::_( 'behavior.keepalive' );
	HTMLHelper::_( 'formbehavior.chosen', 'select' );
	CJLib::behavior( 'bscore' );
	CjScript::_( 'form', [ 'custom' => false ] );
	CjScript::_( 'fontawesome', [ 'custom' => false ] );
}
else
{
	$wa = $this->document->getWebAssetManager();
	$wa->getRegistry()->addExtensionRegistryFile( 'com_contenthistory' );
	$wa->useScript( 'keepalive' )->useScript( 'form.validate' );
	$this->set( 'useCoreUI', true );
}

$this->hiddenFieldsets    = [];
$this->hiddenFieldsets[0] = 'basic-limited';
$this->configFieldsets    = [];
$this->configFieldsets[0] = 'editorConfig';

// Create shortcut to parameters.
$params = $this->state->get( 'params' );

$app   = Factory::getApplication();
$input = $app->input;
$assoc = Associations::isEnabled();

// This checks if the config options have ever been saved. If they haven't they will fall back to the original settings.
$params        = json_decode( $params );
$editoroptions = isset( $params->show_publishing_options );

if ( ! $editoroptions )
{
	$params->show_publishing_options = '1';
	$params->show_crossword_options  = '1';
}

// Check if the crossword uses configuration settings besides global. If so, use them.
if ( isset( $this->item->attribs['show_publishing_options'] ) && $this->item->attribs['show_publishing_options'] != '' )
{
	$params->show_publishing_options = $this->item->attribs['show_publishing_options'];
}

if ( isset( $this->item->attribs['show_crossword_options'] ) && $this->item->attribs['show_crossword_options'] != '' )
{
	$params->show_crossword_options = $this->item->attribs['show_crossword_options'];
}
?>
<div id="cj-wrapper">
    <form action="<?php echo Route::_( 'index.php?option=com_crosswords&layout=edit&id=' . (int) $this->item->id ); ?>"
          method="post" name="adminForm" id="item-form" class="form-validate">

        <div class="form-horizontal">
            <div class="row">
                <div class="span6 col-md-6">
                    <div class="control-group">
                        <label class="control-label"><?php echo $this->form->getLabel( 'keyword' ); ?></label>
                        <div class="controls"><?php echo $this->form->getInput( 'keyword' ); ?></div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $this->form->getLabel( 'question' ); ?></label>
                        <div class="controls"><?php echo $this->form->getInput( 'question' ); ?></div>
                    </div>

					<?php if ( $params->show_publishing_options == 1 ) : ?>
						<?php echo LayoutHelper::render( 'joomla.edit.publishingdata', $this ); ?>
					<?php endif; ?>

					<?php if ( $assoc ) : ?>
						<?php echo $this->loadTemplate( 'associations' ); ?>
					<?php endif; ?>

					<?php $this->show_options = $params->show_crossword_options; ?>
					<?php echo LayoutHelper::render( 'joomla.edit.params', $this ); ?>
                </div>
                <div class="span6 col-md-6">
					<?php echo LayoutHelper::render( 'joomla.edit.global', $this ); ?>
                </div>
            </div>
        </div>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return" value="<?php echo $input->getCmd( 'return' ); ?>"/>

		<?php echo HTMLHelper::_( 'form.token' ); ?>
    </form>

    <div style="display: none;">
        <input id="cjpageid" value="form" type="hidden">
        <span id="msg_field_required"><?php echo Text::_( 'MSG_FIELD_REQUIRED' ); ?></span>
    </div>
</div>
