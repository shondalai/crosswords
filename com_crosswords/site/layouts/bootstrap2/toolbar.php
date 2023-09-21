<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2021 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined( '_JEXEC' ) or die;

$data     = $displayData;
$params   = $displayData['params'];
$category = isset( $displayData['category'] ) ? $displayData['category'] : null;
$action   = isset( $displayData['action'] ) ? $displayData['action'] : '';
$user     = Factory::getUser();

$itemid      = CJFunctions::get_active_menu_id();
$home_itemid = CJFunctions::get_active_menu_id( true, 'index.php?option=' . CW_APP_NAME . '&view=crosswords' );
$user_itemid = CJFunctions::get_active_menu_id( true, 'index.php?option=' . CW_APP_NAME . '&view=users' );

$base_uri   = 'index.php?option=' . CW_APP_NAME . '&view=crosswords';
$catparam   = ! empty( $category ) ? '&id=' . $category->id . ':' . $category->alias : '';
$categories = null;

if ( $user->authorise( 'core.keywords', CW_APP_NAME ) )
{

	$categories = HTMLHelper::_( 'category.categories', CW_APP_NAME );
	foreach ( $categories as $id => $category )
	{

		if ( $category->value == '1' || ! $user->authorise( 'core.create', CW_APP_NAME . '.category.' . $category->value ) )
		{

			unset( $categories[$id] );
		}
	}

	$nocat = new CMSObject();
	$nocat->set( 'text', Text::_( 'COM_CROSSWORDS_CHOOSE_CATEGORY' ) );
	$nocat->set( 'value', '0' );
	$nocat->set( 'disable', false );

	array_unshift( $categories, $nocat );
}

if ( $params->get( 'display_toolbar', 1 ) == 1 )
{
	?>

    <div class="navbar">
        <div class="navbar-inner">
            <div class="header-container">
                <a class="btn btn-navbar" data-toggle="collapse" data-target=".cw-nav-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>

                <a class="brand" href="<?php echo Route::_( 'index.php?option=' . CW_APP_NAME . '&view=crosswords' . $itemid ); ?>">
					<?php echo Text::_( 'COM_CROSSWORDS_HOME' ); ?>
                </a>

                <div class="nav-collapse collapse cw-nav-collapse" style="overflow: visible">
                    <ul class="nav">
                        <li class="dropdown<?php echo in_array( $action, [ 'latest', 'popular', 'solved' ] ) ? ' active' : ''; ?>">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
								<?php echo Text::_( 'COM_CROSSWORDS_DISCOVER' ); ?> <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="nav-header"><?php echo Text::_( 'COM_CROSSWORDS_CROSSWORDS' ); ?></li>
                                <li<?php echo $action == 'latest' ? ' class="active"' : ''; ?>>
                                    <a href="<?php echo Route::_( $base_uri . '&task=latest' . $catparam . $home_itemid ); ?>">
                                        <i class="icon-leaf"></i> <?php echo Text::_( 'COM_CROSSWORDS_LATEST_CROSSWORDS' ); ?>
                                    </a>
                                </li>
                                <li<?php echo $action == 'popular' ? ' class="active"' : ''; ?>>
                                    <a href="<?php echo Route::_( $base_uri . '&task=popular' . $catparam . $home_itemid ); ?>">
                                        <i class="icon-fire"></i> <?php echo Text::_( 'COM_CROSSWORDS_POPULAR_CROSSWORDS' ); ?>
                                    </a>
                                </li>
                                <li<?php echo $action == 'solved' ? ' class="active"' : ''; ?>>
                                    <a href="<?php echo Route::_( $base_uri . '&task=solved' . $catparam . $home_itemid ); ?>">
                                        <i class="icon-check"></i> <?php echo Text::_( 'COM_CROSSWORDS_SOLVED_CROSSWORDS' ); ?>
                                    </a>
                                </li>
                            </ul>
                        </li>

						<?php if ( $user->authorise( 'core.create', CW_APP_NAME ) ): ?>
                            <li class="dropdown<?php echo in_array( $action, [ 'form', 'keyword_form', 'edit' ] ) ? ' active' : ''; ?>">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
									<?php echo Text::_( 'COM_CROSSWORDS_CREATE' ); ?> <b class="caret"></b>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="nav-header"><?php echo Text::_( 'COM_CROSSWORDS_SUBMIT_CONTENT' ); ?></li>
                                    <li<?php echo $action == 'form' ? ' class="active"' : ''; ?>>
                                        <a href="<?php echo Route::_( $base_uri . '&task=form' . $home_itemid ); ?>">
                                            <i class="icon-th"></i> <?php echo Text::_( 'COM_CROSSWORDS_CREATE_CROSSWORD' ); ?>
                                        </a>
                                    </li>
                                    <li<?php echo $action == 'keyword_form' ? ' class="active"' : ''; ?>>
                                        <a href="#keyword_form" role="button" data-toggle="modal">
                                            <i class="icon-th-list"></i> <?php echo Text::_( 'COM_CROSSWORDS_SUBMIT_KEYWORD' ); ?>
                                        </a>
                                    </li>
                                </ul>
                            </li>
						<?php endif; ?>

						<?php if ( ! $user->guest ): ?>
                            <li class="dropdown<?php echo in_array( $action, [ 'mycrosswords', 'myresponses' ] ) ? ' active' : ''; ?>">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
									<?php echo Text::_( 'COM_CROSSWORDS_MY_STUFF' ); ?> <b class="caret"></b>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="nav-header"><?php echo Text::_( 'COM_CROSSWORDS_CROSSWORDS' ); ?></li>
                                    <li<?php echo $action == 'mycrosswords' ? ' class="active"' : ''; ?>>
                                        <a href="<?php echo Route::_( $base_uri . '&task=mycrosswords' . $catparam . $home_itemid ); ?>">
                                            <i class="icon-pencil"></i> <?php echo Text::_( 'COM_CROSSWORDS_MY_CROSSWORDS' ); ?>
                                        </a>
                                    </li>
                                    <li<?php echo $action == 'myresponses' ? ' class="active"' : ''; ?>>
                                        <a href="<?php echo Route::_( $base_uri . '&task=myresponses' . $catparam . $home_itemid ); ?>">
                                            <i class="icon-user"></i> <?php echo Text::_( 'COM_CROSSWORDS_MY_RESPONSES' ); ?>
                                        </a>
                                    </li>
                                </ul>
                            </li>
						<?php endif; ?>
                    </ul>

                    <ul class="nav pull-right">
						<?php if ( $user->guest ): ?>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="icon-user"></i> <b class="caret"></b>
                                </a>

                                <ul class="dropdown-menu">
                                    <li class="nav-header"><?php echo Text::_( 'JLOGIN' ); ?></li>
                                    <li class="padding-10">
                                        <form action="<?php echo Route::_( 'index.php', true, $params->get( 'usesecure' ) ); ?>" method="post" id="login-form"
                                              class="form-horizontal">
                                            <div class="input-prepend">
                                                <span class="add-on"><i class="icon-user"></i></span>
                                                <input type="text" name="username" id="inputUsername" class="input-fix margin-bottom-10"
                                                       placeholder="<?php echo Text::_( 'JGLOBAL_USERNAME' ); ?>"/>
                                            </div>

                                            <div class="input-prepend">
                                                <span class="add-on"><i class="icon-lock"></i></span>
                                                <input type="password" name="password" class="input-fix margin-bottom-10" id="inputPassword"
                                                       placeholder="<?php echo Text::_( 'JGLOBAL_PASSWORD' ); ?>"/>
                                            </div>

											<?php if ( PluginHelper::isEnabled( 'system', 'remember' ) ) : ?>
                                                <label class="checkbox"><input type="checkbox"/> <?php echo Text::_( 'COM_CJLIB_REMEMBER_ME' ); ?></label>
											<?php endif; ?>

                                            <input type="hidden" name="option" value="com_users"/>
                                            <input type="hidden" name="task" value="user.login"/>
                                            <input type="hidden" name="return" value="<?php echo base64_encode( Uri::current() ); ?>"/>
											<?php echo HTMLHelper::_( 'form.token' ); ?>
                                            <button type="button" class="btn" data-dismiss="modal"><?php echo Text::_( 'JCANCEL' ); ?></button>
                                            <button class="btn btn-primary" type="submit"><?php echo Text::_( 'JLOGIN' ); ?></button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
						<?php else: ?>
                            <li>
                                <a class="tooltip-hover" href="#" onclick="document.cw_logout_form.submit();" title="<?php echo Text::_( 'JLOGOUT' ); ?>">
                                    <i class="icon-lock"></i> <span class="visible-phone"><?php echo Text::_( 'JLOGOUT' ); ?></span>
                                </a>
                                <form id="cw_logout_form" name="cw_logout_form"
                                      action="<?php echo Route::_( 'index.php', true, $params->get( 'usesecure' ) ); ?>"
                                      method="post" style="display: none;">
                                    <input type="hidden" name="option" value="com_users"/>
                                    <input type="hidden" name="task" value="user.logout"/>
                                    <input type="hidden" name="return" value="<?php echo base64_encode( Uri::current() ); ?>"/>
									<?php echo HTMLHelper::_( 'form.token' ); ?>
                                </form>
                            </li>
						<?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

	<?php
}

if ( $user->authorise( 'core.keywords', CW_APP_NAME ) )
{
	?>
    <div id="keyword_form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3 id="myModalLabel"><?php echo Text::_( 'COM_CROSSWORDS_SUBMIT_KEYWORD' ); ?></h3>
        </div>
        <div class="modal-body">
            <div class="alert alert-info"><?php echo Text::_( 'COM_CROSSWORDS_SUBMIT_QUESTION_HELP' ); ?></div>
            <div class="alert alert-error hide" id="submit-keyword-error"></div>
            <div class="alert alert-success hide" id="submit-keyword-message"></div>
            <form class="form-horizontal" id="form-submit-keyword" method="post"
                  action="<?php echo Route::_( 'index.php?option=' . CW_APP_NAME . '&view=crosswords&task=save_keyword' . $home_itemid ) ?>">
                <div class="control-group">
                    <label class="control-label" for="keyword"><?php echo Text::_( 'COM_CROSSWORDS_KEYWORD' ); ?></label>
                    <div class="controls">
                        <input type="text" id="keyword" name="keyword" class="input-xlarge required" placeholder="<?php echo Text::_( 'COM_CROSSWORDS_KEYWORD' ); ?>">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="question"><?php echo Text::_( 'COM_CROSSWORDS_QUESTION' ); ?></label>
                    <div class="controls">
                        <input type="text" id="question" name="question" class="input-xlarge required" placeholder="<?php echo Text::_( 'COM_CROSSWORDS_QUESTION' ); ?>">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="category"><?php echo Text::_( 'COM_CROSSWORDS_CATEGORY' ); ?></label>
                    <div class="controls">
						<?php echo HTMLHelper::_( 'select.genericlist', $categories, 'category' ); ?>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo Text::_( 'COM_CROSSWORDS_CLOSE' ); ?></button>
            <button class="btn btn-primary" id="save-keyword"><?php echo Text::_( 'JSUBMIT' ); ?></button>
        </div>
    </div>
	<?php
}
?>