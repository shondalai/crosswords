<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2021 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;

defined('JPATH_PLATFORM') or die;
?>
<a class="btn btn-secondary" type="button" data-dismiss="modal" data-bs-dismiss="modal">
	<?php echo Text::_('JCANCEL'); ?>
</a>
<button id="batch-submit-button-id" class="btn btn-success" type="submit" data-submit-task="topic.batch" onclick="this.form.task.value='topic.batch';return true;">
	<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
