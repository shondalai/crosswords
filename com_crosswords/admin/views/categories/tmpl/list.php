<?php
/**
 * @version        $Id: list.php 01 2011-01-11 11:37:09Z maverick $
 * @package        CoreJoomla.Crosswords
 * @subpackage     Components
 * @copyright      Copyright (C) 2009 - 2012 corejoomla.com. All rights reserved.
 * @author         Maverick
 * @link           http://www.corejoomla.com/
 * @license        License GNU General Public License version 2 or later
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<form name="adminForm" id="adminForm" action="index.php?option=<?php echo CW_APP_NAME; ?>&view=categories" method="post">
	<?php
	if ( count( $this->categories ) > 0 )
	{
		?>
        <table class="adminlist">
            <thead>
            <tr>
                <th width="20px">#</th>
                <th><?php echo Text::_( 'LBL_CATEGORY' ); ?></th>
                <th width="50px"><?php echo Text::_( 'LBL_CROSSWORDS' ); ?></th>
                <th width="25px"></th>
                <th width="25px"></th>
                <th width="50px"><?php echo Text::_( 'LBL_EDIT' ); ?></th>
                <th width="50px"><?php echo Text::_( 'LBL_DELETE' ); ?></th>
                <th width="50px">ID</th>
            </tr>
            </thead>
            <tbody>
			<?php
			$db      = Factory::getDbo();
			$tree    = new CjNestedTree( $db, T_CROSSWORDS_CATEGORIES );
			$content = '';
			static $row_num = 0;

			$base_uri      = 'index.php?option=' . CW_APP_NAME . '&view=categories';
			$fields        = [];
			$img_move_up   = '<img src="' . ( Uri::base( true ) . '/components/' . CW_APP_NAME . '/assets/images/move_up.png' ) . '" alt="' . Text::_( 'TXT_MOVE_UP' ) . '"/>';
			$img_move_down = '<img src="' . ( Uri::base( true ) . '/components/' . CW_APP_NAME . '/assets/images/move_down.png' ) . '" alt="' . Text::_( 'TXT_MOVE_DOWN' ) . '"/>';

			$fields[] = [
				'header' => Text::_( 'LBL_CATEGORY' ),
				'name'   => 'title',
				'type'   => 'category',
				'align'  => 'left',
				'src'    => $base_uri . '&task=edit',
				'id'     => true,
				'value'  => null,
			];
			$fields[] = [ 'header' => Text::_( 'LBL_CROSSWORDS' ), 'name' => 'crosswords', 'type' => 'text', 'align' => 'center' ];
			$fields[] = [ 'header' => '', 'name' => null, 'type' => 'up', 'align' => 'center', 'src' => $base_uri . '&task=move_up', 'id' => true, 'value' => $img_move_up ];
			$fields[] = [ 'header' => '', 'name' => null, 'type' => 'down', 'align' => 'center', 'src' => $base_uri . '&task=move_down', 'id' => true, 'value' => $img_move_down ];
			$fields[] = [
				'header' => Text::_( 'LBL_EDIT' ),
				'name'   => null,
				'type'   => 'link',
				'align'  => 'center',
				'src'    => $base_uri . '&task=edit',
				'id'     => true,
				'value'  => null,
			];
			$fields[] = [
				'header' => Text::_( 'LBL_DELETE' ),
				'name'   => null,
				'type'   => 'link',
				'align'  => 'center',
				'src'    => $base_uri . '&task=delete',
				'id'     => true,
				'value'  => null,
			];
			$fields[] = [ 'header' => Text::_( 'ID' ), 'name' => 'id', 'type' => 'text', 'align' => 'center' ];

			echo $tree->get_tree_table( $content, $this->categories, $fields );
			?>
            </tbody>
        </table>
		<?php
	}
	else
	{
		echo 'No categories found';
	}
	?>
    <input type="hidden" name="task" value="add">
</form>
