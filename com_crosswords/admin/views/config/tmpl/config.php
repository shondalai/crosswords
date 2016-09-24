<?php
/**
 * @version		$Id: config.php 01 2011-01-11 11:37:09Z maverick $
 * @package		CoreJoomla16.Crosswords
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2011 corejoomla.com, Inc. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');

$themes = CJFunctions::get_ui_theme_names();
$supported_avatars = CJFunctions::get_supported_avatars();
$point_systems = CJFunctions::get_supported_point_systems();

// Tab General
$general_settings = array(
	'title'=>'GENERAL_SETTINGS',
	'elements'=>array(
		array('name'=>CW_DEFAULT_TEMPLATE, 'type'=>'select', 'values'=>array('default'), 'labels'=>array('Default')),
		array('name'=>CW_DEFAULT_THEME, 'type'=>'select', 'values'=>array_merge((array)'default', $themes), 'labels'=>array_merge((array)JText::_('LBL_GLOBAL'), $themes)),
		array('name'=>CW_DEFAULT_EDITOR, 'type'=>'select', 'values'=>array('default', 'bbcode'), 'labels'=>array('Joomla Default', 'BBCode Editor')),
		array('name'=>CW_ENABLE_CURSOR_AUTOMOVE, 'type'=>'select', 'values'=>array('1','0'), 'labels'=>array('LBL_YES','LBL_NO')),
	)
);

$display_settings = array(
	'title'=>'DISPLAY_SETTINGS',
	'elements'=>array(
		array('name'=>CW_USER_NAME, 'type'=>'select', 'values'=>array('name','username'), 'labels'=>array('LBL_NAME','LBL_USERNAME')),
		array('name'=>CW_USER_AVTAR, 'type'=>'select', 'values'=>array_keys($supported_avatars), 'labels'=>array_values($supported_avatars)),
		array('name'=>CW_LIST_LIMIT, 'type'=>'text'),
		array('name'=>CW_AVATAR_SIZE, 'type'=>'text'),
		array('name'=>CW_SHOW_AVATAR_IN_LISTING, 'type'=>'select', 'values'=>array('1','0'), 'labels'=>array('LBL_YES','LBL_NO')),
		array('name'=>CW_TOOLBAR_BUTTONS, 'type'=>'text'),
		array('name'=>CW_ENABLE_CATEGORY_BOX, 'type'=>'select', 'values'=>array('1','0'), 'labels'=>array('LBL_YES','LBL_NO')),
		array('name'=>CW_CLEAN_HOME_PAGE, 'type'=>'select', 'values'=>array('1','0'), 'labels'=>array('LBL_YES','LBL_NO')),
		array('name'=>CW_ENABLE_POWERED_BY, 'type'=>'select', 'values'=>array('1','0'), 'labels'=>array('LBL_YES','LBL_NO'))
	)
);

$notification_settings = array(
	'title'=>'EMAIL_NOTIFICATION_SETTINGS',
	'elements'=>array(
		array('name'=>CW_NOTIF_SENDER_NAME, 'type'=>'text'),
		array('name'=>CW_NOTIF_SENDER_EMAIL, 'type'=>'text'),
		array('name'=>CW_NOTIF_SOLVED_CROSSWORD, 'type'=>'select', 'values'=>array('1','0'), 'labels'=>array('LBL_YES','LBL_NO')),
		array('name'=>CW_NOTIF_ACCEPTED_KEYWORD, 'type'=>'select', 'values'=>array('1','0'), 'labels'=>array('LBL_YES','LBL_NO'))
	)
);

$admin_notification_settings = array(
	'title'=>'EMAIL_ADMIN_NOTIFICATION_SETTINGS',
	'elements'=>array(
		array('name'=>CW_NOTIF_ADMIN_EMAIL, 'type'=>'text'),
		array('name'=>CW_NOTIF_ADMIN_NEW_KEYWORD, 'type'=>'select', 'values'=>array('1','0'), 'labels'=>array('LBL_YES','LBL_NO')),
		array('name'=>CW_NOTIF_ADMIN_NEW_CROSSWORD, 'type'=>'select', 'values'=>array('1','0'), 'labels'=>array('LBL_YES','LBL_NO'))
	)
);

$sharing_settings = array(
	'title'=>'LBL_SHARING_SETTINGS',
	'elements'=>array(
		array('name'=>CW_SHARING_SERVICES, 'type'=>'vcheckboxes', 'values'=>array('fblike','tweet','googleplus', 'addthis'), 'labels'=>array('LBL_FACEBOOK_LIKE','LBL_TWITTER_TWEET', 'LBL_GOOGLE_PLUS', 'LBL_ADD_THIS')),
	)
);

$activity_settings = array(
	'title'=>'ACTIVITY_STREAM_SETTINGS',
	'elements'=>array(
		array('name'=>CW_ACTIVITY_STREAM_TYPE, 'type'=>'select', 'values'=>array('none','jomsocial','touch'), 'labels'=>array('OPTION_NONE','OPTION_JOMSOCIAL','OPTION_MIGHTY_TOUCH')),
		array('name'=>CW_STREAM_NEW_CROSSWORD, 'type'=>'select', 'values'=>array('1','0'), 'labels'=>array('LBL_YES','LBL_NO')),
		array('name'=>CW_STREAM_SOLVED_CROSSWORD, 'type'=>'select', 'values'=>array('1','0'), 'labels'=>array('LBL_YES','LBL_NO'))
	)
);

$points_system_settings = array(
	'title'=>'POINTS_SYSTEM_SETTINGS',
	'elements'=>array(
		array('name'=>CW_POINTS_SYSTEM, 'type'=>'select', 'values'=>array_keys($point_systems), 'labels'=>array_values($point_systems)),
		array('name'=>CW_TOUCH_POINTS_NEW_KEYWORD, 'type'=>'text'),
		array('name'=>CW_TOUCH_POINTS_NEW_CROSSWORD, 'type'=>'text'),
		array('name'=>CW_TOUCH_POINTS_SOLVED_CROSSWORD, 'type'=>'text')
	)
);

$comment_system_settings = array(
	'title'=>'COMMENT_SYSTEM_SETTINGS',
	'elements'=>array(
		array('name'=>CW_COMMENT_SYSTEM, 'type'=>'select', 'values'=>array('none','jcomment','jacomment','jomcomment'), 'labels'=>array('OPTION_NONE','OPTION_JCOMMENT','OPTION_JACOMMENT','OPTION_JOMCOMMENT'))
	)
);

$permission_settings = array(
	'title'=>'PERMISSION_SETTINGS', 
	'elements'=>array(
		array('name'=>CW_PERMISSION_ACCESS, 'type'=>'permissions'),
		array('name'=>CW_PERMISSION_CREATE, 'type'=>'permissions'),
		array('name'=>CW_PERMISSION_SUBMIT_WORDS, 'type'=>'permissions'),
		array('name'=>CW_PERMISSION_SOLVE, 'type'=>'permissions'),
		array('name'=>CW_PERMISSION_WYSIWYG, 'type'=>'permissions'),
		array('name'=>CW_PERMISSION_MODERATE, 'type'=>'permissions')
	)
);

$tab_group_general = array('name'=>'general_settings', 'title'=>'TAB_GENERAL', 'groups'=>array($general_settings, $display_settings));
$tab_notification = array('name'=>'notification_settings', 'title'=>'TAB_NOTIFICATION', 'groups'=>array($notification_settings, $admin_notification_settings)); 
$tab_third_party = array('name'=>'thirdparty_settings', 'title'=>'TAB_THIRD_PARTY', 'groups'=>array($activity_settings, $points_system_settings, $comment_system_settings, $sharing_settings));
$tab_permissions = array('name'=>'permission_settings', 'title'=>'TAB_PERMISSIONS', 'groups'=>array($permission_settings));

$configuration = array($tab_group_general, $tab_notification, $tab_third_party);
if(APP_VERSION == '1.5'){
	$configuration[] = $tab_permissions;
}

$config = CrosswordsHelper::getConfig();

$document = JFactory::getDocument();
$document->addScriptDeclaration('function resetPermissionOptions(select){ selectBox = document.getElementById(select); selectBox.selectedIndex = -1; }');
$document->addScriptDeclaration('jQuery(document).ready(function($){jQuery("#config-document").tabs();});');
?>
<form action="<?php echo JRoute::_('index.php?option='.CW_APP_NAME.'&view=config&task=save');?>" method="post" name="adminForm">
<div id="config-document">
	<ul>
		<?php foreach ($configuration as $tab):?>
		<li><a href="#<?php echo $tab['name'];?>"><?php echo JText::_($tab['title']);?></a></li>
		<?php endforeach;;?>
	</ul>
	<?php 
	foreach ($configuration as $tab){
		echo '<div id="'.$tab['name'].'">';
		foreach ($tab['groups'] as $group){
			echo '<fieldset><legend>'.JText::_($group['title']).'</legend><table class="admintable">';
			foreach ($group['elements'] as $element){
				switch ($element['type']){
					case 'text':
						echo '<tr><td class="formelement">';
						echo '<label for="'.$element['name'].'"><span class="editlinktip hasTip" title="'.JText::_('LBL_'.$element['name'].'_DESC').'">'.JText::_('LBL_'.$element['name']).'</span></label>';
						echo '</td><td>';
						echo '<input type="text" id="'.$element['name'].'" name="'.$element['name'].'" size="25" value="'.$config[$element['name']].'">';
						echo '</td></tr>';
						break;
					case 'textarea':
						echo '<tr><td class="formelement">';
						echo '<label for="'.$element['name'].'"><span class="editlinktip hasTip" title="'.JText::_('LBL_'.$element['name'].'_DESC').'">'.JText::_('LBL_'.$element['name']).'</span></label>';
						echo '</td><td>';
						echo '<textarea cols="40" rows="4" id="'.$element['name'].'" name="'.$element['name'].'" size="25">'.$config[$element['name']].'</textarea>';
						echo '</td></tr>';
						break;
					case 'password':
						echo '<tr><td class="formelement">';
						echo '<label for="'.$element['name'].'"><span class="editlinktip hasTip" title="'.JText::_('LBL_'.$element['name'].'_DESC').'">'.JText::_('LBL_'.$element['name']).'</span></label>';
						echo '</td><td>';
						echo '<input type="password" id="'.$element['name'].'" name="'.$element['name'].'" size="25" value="*****">';
						echo '</td></tr>';
						break;
					case 'select':
						echo '<tr><td class="formelement">';
						echo '<label for="'.$element['name'].'"><span class="editlinktip hasTip" title="'.JText::_('LBL_'.$element['name'].'_DESC').'">'.JText::_('LBL_'.$element['name']).'</span></label>';
						echo '</td><td>';
						echo '<select id="'.$element['name'].'" name="'.$element['name'].'" size="1">';
						foreach ($element['values'] as $i=>$value){
							echo '<option value="'.$value.'"'.($config[$element['name']] == $value ? ' selected="selected"':'').'>'.JText::_($element['labels'][$i]).'</option>';
						}
						echo '</select></td></tr>';
						break;
					case 'checkbox':
						echo '<tr><td>';
						echo '<input type="checkbox" id="'.$element['name'].'" name="'.$element['name'].'" size="25" value="1"'.($config[$element['name']] == '1' ? ' checked="checked"':'').'>';
						echo '</td><td class="formelement">';
						echo '<label for="'.$element['name'].'"><span class="editlinktip hasTip" title="'.JText::_('LBL_'.$element['name'].'_DESC').'">'.JText::_('LBL_'.$element['name']).'</span></label>';
						echo '</td></tr>';
						break;
					case 'vcheckboxes':
						echo '<tr><td class="formelement" nowrap="nowrap">';
						echo '<label for="'.$element['name'].'"><span class="editlinktip hasTip" title="'.JText::_('LBL_'.$element['name'].'_DESC').'">'.JText::_('LBL_'.$element['name']).'</span></label>';
						echo '&nbsp;&nbsp;&nbsp;</td>';
						foreach ($element['values'] as $i=>$value){
							if($i == 0){
								echo '<td class="formelement" nowrap="nowrap">';
							}else{
								echo '<tr><td></td><td class="formelement" nowrap="nowrap">';
							}
							echo '<input type="checkbox" id="'.$element['name'].$i.'" name="'.$element['name'].'[]" value="'.$value.'"'.(strpos($config[$element['name']], $value) !== false ? ' checked="checked"':'').'>';
							echo '<label for="'.$element['name'].$i.'"><span class="editlinktip hasTip" title="'.JText::_($element['labels'][$i]).'">'.JText::_($element['labels'][$i]).'</span></label>';
						}
						echo '</td></tr>';
						break; 
					case 'permissions':
						echo '<tr><td class="formelement">';
						echo '<label for="'.$element['name'].'"><span class="editlinktip hasTip" title="'.JText::_('LBL_'.$element['name'].'_DESC').'">'.JText::_('LBL_'.$element['name']).'</span></label>';
						echo '</td><td>';
						echo CrosswordsHelper::usersGroups($element['name'],$element['name'].'[]',explode(',', $config[$element['name']]));
						echo '</td></tr>';
						break;
				}
			}
			echo '</table></fieldset>';
		}
		echo '</div>';
	}
	?>
</div>
<input type="hidden" name="option" value="<?php echo CW_APP_NAME;?>" />
<input type="hidden" name="task" value="save" />
<input type="hidden" name="view" value="config" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>