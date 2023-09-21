<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2021 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

defined( '_JEXEC' ) or die;

$this->user_name   = $this->params->get( 'user_display_name', 'name' );
$this->user_avatar = $this->params->get( 'user_avatar', 'none' );
$sharing_services  = $this->params->get( 'sharing_services', [] );
$comment_system    = $this->params->get( 'comment_system', 'none' );

if ( count( $sharing_services ) > 0 )
{
	$document = Factory::getDocument();
	$document->addScript( '//s7.addthis.com/js/300/addthis_widget.js#async=1' );
	$document->addScriptDeclaration( 'jQuery(document).ready(function($){addthis.init();});' );
}

$layout = $this->params->get( 'ui_layout', 'bootstrap2' );
?>

<div id="cj-wrapper" class="cj-wrapper-main">
	<?php
	echo LayoutHelper::render( $layout . '.toolbar', [
		'params' => $this->params,
		'action' => $this->action,
	] );
	echo LayoutHelper::render( $layout . '.crossword_details', [
		'item'       => $this->item,
		'state'      => $this->state,
		'params'     => $this->params
	] );
	?>
</div>