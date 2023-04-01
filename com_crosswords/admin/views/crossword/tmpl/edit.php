<?php
/**
 * @version		$Id: view.html.php 01 2014-01-26 11:37:09Z maverick $
 * @package		CoreJoomla.Polls
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

if ( APP_VERSION < 4 )
{
	JHtml::_( 'behavior.formvalidator' );
	JHtml::_( 'behavior.keepalive' );
	JHtml::_( 'formbehavior.chosen', 'select' );
	CJLib::behavior( 'bscore' );
	CjScript::_( 'form', [ 'custom' => false ] );
	CjScript::_( 'fontawesome', [ 'custom' => false ] );
} else {
	$wa = $this->document->getWebAssetManager();
	$wa->getRegistry()->addExtensionRegistryFile('com_contenthistory');
	$wa->useScript('keepalive')->useScript('form.validate');
	$this->set('useCoreUI', true);
}

$this->hiddenFieldsets = array();
$this->hiddenFieldsets[0] = 'basic-limited';
$this->configFieldsets = array();
$this->configFieldsets[0] = 'editorConfig';

// Create shortcut to parameters.
$params = $this->state->get('params');
$editor = $this->params->get('dafault_editor', 'wysiwygbb');

$app = JFactory::getApplication();
$input = $app->input;
$assoc = JLanguageAssociations::isEnabled();

// This checks if the config options have ever been saved. If they haven't they will fall back to the original settings.
$params = json_decode($params);
$editoroptions = isset($params->show_publishing_options);

if (!$editoroptions)
{
	$params->show_publishing_options = '1';
	$params->show_crossword_options = '1';
}

// Check if the crossword uses configuration settings besides global. If so, use them.
if (isset($this->item->attribs['show_publishing_options']) && $this->item->attribs['show_publishing_options'] != '')
{
	$params->show_publishing_options = $this->item->attribs['show_publishing_options'];
}

if (isset($this->item->attribs['show_crossword_options']) && $this->item->attribs['show_crossword_options'] != '')
{
	$params->show_crossword_options = $this->item->attribs['show_crossword_options'];
}
?>
<div id="cj-wrapper">
	<form action="<?php echo JRoute::_('index.php?option=com_crosswords&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
		
		<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
		
		<div class="form-horizontal">
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'answers')); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'answers', JText::_('COM_CROSSWORDS_FIELDSET_CROSSWORD_CONTENT', true)); ?>
			<div class="row-fluid">
				<div class="span9">
					<?php echo $this->form->getLabel('description'); ?>
					<?php if($editor == 'wysiwygbb'):?>
					<?php echo CJFunctions::load_editor($editor, 'jform_description', 'jform[description]', $this->item->description, 10, 40, '100%', '250px', '', 'width: 100%; height: 250px;', true);?>
					<?php else:?>
					<?php echo $this->form->getInput('description'); ?>
					<?php endif;?>
				</div>
				<div class="span3">
					<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
			
			<?php // Do not show the publishing options if the edit form is configured not to. ?>
			<?php if ($params->show_publishing_options == 1) : ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_CROSSWORDS_FIELDSET_PUBLISHING', true)); ?>
				<div class="row-fluid form-horizontal-desktop">
					<div class="span6">
						<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
					</div>
					<div class="span6">
						<?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
					</div>
				</div>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>
		
			<?php if ($assoc) : ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'associations', JText::_('JGLOBAL_FIELDSET_ASSOCIATIONS', true)); ?>
					<?php echo $this->loadTemplate('associations'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>
		
			<?php $this->show_options = $params->show_crossword_options; ?>
			<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>
		
			<?php if ($this->canDo->get('core.admin')) : ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_CROSSWORDS_FIELDSET_RULES', true)); ?>
					<?php echo $this->form->getInput('rules'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>
		
			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		</div>
	
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
			
		<?php echo JHtml::_('form.token'); ?>
	</form>

	<div style="display: none;">
		<input id="cjpageid" value="form" type="hidden">
		<span id="msg_field_required"><?php echo JText::_('MSG_FIELD_REQUIRED');?></span>
	</div>
</div>
