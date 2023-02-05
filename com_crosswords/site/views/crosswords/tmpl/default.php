<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2021 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die;

$layout = $this->params->get( 'ui_layout', 'bootstrap2' );
?>

<div id="cj-wrapper" class="cj-wrapper-main">
	<?php
	echo JLayoutHelper::render( $layout . '.toolbar', [
		'params'     => $this->params,
		'action'     => $this->action,
	] );
	echo JLayoutHelper::render( $layout . '.crosswords_list', [
		'items'      => $this->items,
		'state'      => $this->state,
		'params'     => $this->params,
		'pagination' => $this->pagination
	] );
	?>
</div>