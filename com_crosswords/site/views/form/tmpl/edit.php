<?php
/**
 * @package     Crosswords
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2023 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Layout\LayoutHelper;

defined( '_JEXEC' ) or die;

$layout = $this->params->get( 'ui_layout', 'bootstrap2' );
$helper = $this->get( 'useCoreUI', false ) ? 'uitab' : 'bootstrap';
?>
<div id="cj-wrapper" class="cj-wrapper-main">
	<?php
	echo LayoutHelper::render( $layout . '.toolbar', [ 'params' => $this->params ] );
	echo LayoutHelper::render( $layout . '.crossword_form', [ 'item' => $this->item, 'form' => $this->form, 'params' => $this->params, 'helper' => $helper ] );
	?>
</div>
