<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2021 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Layout\LayoutHelper;

defined( '_JEXEC' ) or die;

$layout = $this->params->get( 'ui_layout', 'bootstrap2' );
?>

<div id="cj-wrapper" class="cj-wrapper-main">
	<?php
	echo LayoutHelper::render( $layout . '.toolbar', [
		'params'     => $this->params,
		'action'     => isset($this->action) ?? '',
	] );
	echo LayoutHelper::render( $layout . '.crosswords_list', [
		'items'      => $this->items,
		'state'      => $this->state,
		'params'     => $this->params,
		'pagination' => $this->pagination
	] );
	?>
</div>