<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2021 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

defined('JPATH_PLATFORM') or die;

$published = $this->state->get('filter.published');

$user = Factory::getUser();
$rowClass = CF_MAJOR_VERSION == 3 ? 'control-group span6' : 'form-group col-md-6';
?>
<div class="container">
	<div class="row">
		<div class="<?php echo $rowClass;?>">
			<div class="controls">
				<?php echo LayoutHelper::render('joomla.html.batch.language', []); ?>
			</div>
		</div>
		<div class="<?php echo $rowClass;?>">
			<div class="controls">
				<?php echo LayoutHelper::render('joomla.html.batch.access', []); ?>
			</div>
		</div>
	</div>
	<div class="row">
		<?php if ($published >= 0) : ?>
		<div class="<?php echo $rowClass;?>">
			<div class="controls">
				<?php echo LayoutHelper::render('joomla.html.batch.item', ['extension' => 'com_cjforum']); ?>
			</div>
		</div>
		<?php endif; ?>
		<div class="<?php echo $rowClass;?>">
			<div class="controls">
				<?php echo LayoutHelper::render('joomla.html.batch.tag', []); ?>
			</div>
		</div>
		<?php if ($user->authorise('core.admin', 'com_cjforum')) : ?>
        <div class="<?php echo $rowClass;?>">
            <div class="controls">
				<?php echo LayoutHelper::render('joomla.html.batch.workflowstage', ['extension' => 'com_cjforum']); ?>
            </div>
        </div>
		<?php endif; ?>
	</div>
</div>
