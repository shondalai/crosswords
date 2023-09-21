<?php
/**
 * @package     corejoomla.admin
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

defined( '_JEXEC' ) or die;

HTMLHelper::addIncludePath( JPATH_COMPONENT . '/helpers/html' );

if ( APP_VERSION < 4 )
{
	HTMLHelper::_( 'bootstrap.tooltip' );
	HTMLHelper::_( 'formbehavior.chosen', 'select' );
	HTMLHelper::_( 'behavior.tooltip' );
	HTMLHelper::_( 'behavior.multiselect' );
}

$app       = Factory::getApplication();
$user      = Factory::getUser();
$userId    = $user->get( 'id' );
$listOrder = $this->escape( $this->state->get( 'list.ordering' ) );
$listDirn  = $this->escape( $this->state->get( 'list.direction' ) );
$archived  = $this->state->get( 'filter.published' ) == 2 ? true : false;
$trashed   = $this->state->get( 'filter.published' ) == - 2 ? true : false;
$saveOrder = $listOrder == 'a.ordering';

if ( $saveOrder )
{
	$saveOrderingUrl = 'index.php?option=com_crosswords&task=crosswords.saveOrderAjax&tmpl=component';
	HTMLHelper::_( 'sortablelist.sortable', 'crosswordList', 'adminForm', strtolower( $listDirn ), $saveOrderingUrl );
}

$sortFields = $this->getSortFields();
$assoc      = Associations::isEnabled();
?>
<script type="text/javascript">
  Joomla.orderTable = function () {
    table = document.getElementById('sortTable')
    direction = document.getElementById('directionTable')
    order = table.options[table.selectedIndex].value
    if (order != '<?php echo $listOrder; ?>') {
      dirn = 'asc'
    } else {
      dirn = direction.options[direction.selectedIndex].value
    }
    Joomla.tableOrdering(order, dirn, '')
  }
</script>

<form action="<?php echo Route::_( 'index.php?option=com_crosswords&view=crosswords' ); ?>" method="post" name="adminForm" id="adminForm">
	<?php if ( ! empty( $this->sidebar ) ) : ?>
    <div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
		<?php else : ?>
        <div id="j-main-container">
			<?php endif; ?>
			<?php if ( APP_VERSION >= 3 ): ?>
				<?php echo LayoutHelper::render( 'joomla.searchtools.default', [ 'view' => $this ] ); ?>
			<?php else : ?>
                <div class="filter-select fltrt">
                    <select name="filter_published" class="inputbox" onchange="this.form.submit()">
                        <option value=""><?php echo Text::_( 'JOPTION_SELECT_PUBLISHED' ); ?></option>
						<?php echo HTMLHelper::_( 'select.options', HTMLHelper::_( 'jgrid.publishedOptions' ), 'value', 'text', $this->state->get( 'filter.published' ), true ); ?>
                    </select>

                    <select name="filter_category_id" class="inputbox" onchange="this.form.submit()">
                        <option value=""><?php echo Text::_( 'JOPTION_SELECT_CATEGORY' ); ?></option>
						<?php echo HTMLHelper::_( 'select.options', HTMLHelper::_( 'category.options', 'com_crosswords' ), 'value', 'text', $this->state->get( 'filter.category_id' ) ); ?>
                    </select>

                    <select name="filter_level" class="inputbox" onchange="this.form.submit()">
                        <option value=""><?php echo Text::_( 'JOPTION_SELECT_MAX_LEVELS' ); ?></option>
						<?php echo HTMLHelper::_( 'select.options', $this->f_levels, 'value', 'text', $this->state->get( 'filter.level' ) ); ?>
                    </select>

                    <select name="filter_access" class="inputbox" onchange="this.form.submit()">
                        <option value=""><?php echo Text::_( 'JOPTION_SELECT_ACCESS' ); ?></option>
						<?php echo HTMLHelper::_( 'select.options', HTMLHelper::_( 'access.assetgroups' ), 'value', 'text', $this->state->get( 'filter.access' ) ); ?>
                    </select>

                    <select name="filter_author_id" class="inputbox" onchange="this.form.submit()">
                        <option value=""><?php echo Text::_( 'JOPTION_SELECT_AUTHOR' ); ?></option>
						<?php echo HTMLHelper::_( 'select.options', $this->authors, 'value', 'text', $this->state->get( 'filter.author_id' ) ); ?>
                    </select>

                    <select name="filter_language" class="inputbox" onchange="this.form.submit()">
                        <option value=""><?php echo Text::_( 'JOPTION_SELECT_LANGUAGE' ); ?></option>
						<?php echo HTMLHelper::_( 'select.options', HTMLHelper::_( 'contentlanguage.existing', true, true ), 'value', 'text', $this->state->get( 'filter.language' ) ); ?>
                    </select>
                </div>
			<?php endif; ?>

			<?php if ( empty( $this->items ) ) : ?>
                <div class="alert alert-no-items">
					<?php echo Text::_( 'JGLOBAL_NO_MATCHING_RESULTS' ); ?>
                </div>
			<?php else : ?>
                <table class="table table-striped" id="crosswordList">
                    <thead>
                    <tr>
                        <th width="1%" class="nowrap center hidden-phone">
							<?php echo HTMLHelper::_( 'searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2' ); ?>
                        </th>
                        <th width="1%" class="hidden-phone">
							<?php echo HTMLHelper::_( 'grid.checkall' ); ?>
                        </th>
                        <th width="1%" style="min-width:55px" class="nowrap center">
							<?php echo HTMLHelper::_( 'searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder ); ?>
                        </th>
                        <th>
							<?php echo HTMLHelper::_( 'searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder ); ?>
                        </th>
                        <th width="10%">
							<?php echo HTMLHelper::_( 'searchtools.sort', 'COM_CROSSWORDS_HITS', 'a.hits', $listDirn, $listOrder ); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_( 'searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder ); ?>
                        </th>
						<?php if ( $assoc ) : ?>
                            <th width="5%" class="nowrap hidden-phone">
								<?php echo HTMLHelper::_( 'searchtools.sort', 'COM_CROSSWORDS_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder ); ?>
                            </th>
						<?php endif; ?>
                        <th width="10%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_( 'searchtools.sort', 'JAUTHOR', 'a.created_by', $listDirn, $listOrder ); ?>
                        </th>
                        <th width="5%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_( 'searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder ); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_( 'searchtools.sort', 'JDATE', 'a.created', $listDirn, $listOrder ); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_( 'searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder ); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
					<?php foreach ( $this->items as $i => $item ) :
						$item->max_ordering = 0; //??
						$ordering = ( $listOrder == 'a.ordering' );
						$canCreate = $user->authorise( 'core.create', 'com_crosswords.category.' . $item->catid );
						$canEdit = $user->authorise( 'core.edit', 'com_crosswords.crossword.' . $item->id );
						$canCheckin = $user->authorise( 'core.manage', 'com_checkin' ) || $item->checked_out == $userId || $item->checked_out == 0;
						$canEditOwn = $user->authorise( 'core.edit.own', 'com_crosswords.crossword.' . $item->id ) && $item->created_by == $userId;
						$canChange = $user->authorise( 'core.edit.state', 'com_crosswords.crossword.' . $item->id ) && $canCheckin;
						?>
                        <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">
                            <td class="order nowrap center hidden-phone">
								<?php
								$iconClass = '';
								if ( ! $canChange )
								{
									$iconClass = ' inactive';
								}
                                elseif ( ! $saveOrder )
								{
									$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::tooltipText( 'JORDERINGDISABLED' );
								}
								?>
                                <span class="sortable-handler<?php echo $iconClass ?>">
								<i class="icon-menu"></i>
							</span>
								<?php if ( $canChange && $saveOrder ) : ?>
                                    <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
								<?php endif; ?>
                            </td>
                            <td class="center hidden-phone">
								<?php echo HTMLHelper::_( 'grid.id', $i, $item->id ); ?>
                            </td>
                            <td class="center">
                                <div class="btn-group">
									<?php echo HTMLHelper::_( 'jgrid.published', $item->published, $i, 'crosswords.', $canChange, 'cb', $item->publish_up, $item->publish_down ); ?>
                                </div>
                            </td>
                            <td class="has-context">
                                <div class="pull-left">
									<?php if ( $item->checked_out ) : ?>
										<?php echo HTMLHelper::_( 'jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'crosswords.', $canCheckin ); ?>
									<?php endif; ?>
									<?php if ( $item->language == '*' ): ?>
										<?php $language = Text::alt( 'JALL', 'language' ); ?>
									<?php else: ?>
										<?php $language = $item->language_title ? $this->escape( $item->language_title ) : Text::_( 'JUNDEFINED' ); ?>
									<?php endif; ?>
									<?php if ( $canEdit || $canEditOwn ) : ?>
                                        <a href="<?php echo Route::_( 'index.php?option=com_crosswords&task=crossword.edit&id=' . $item->id ); ?>"
                                           title="<?php echo Text::_( 'JACTION_EDIT' ); ?>">
											<?php echo $this->escape( $item->title ); ?></a>
									<?php else : ?>
                                        <span title="<?php echo Text::sprintf( 'JFIELD_ALIAS_LABEL',
											$this->escape( $item->alias ) ); ?>"><?php echo $this->escape( $item->title ); ?></span>
									<?php endif; ?>
                                    <div class="small">
										<?php echo Text::_( 'JCATEGORY' ) . ": " . $this->escape( $item->category_title ); ?>
                                    </div>
                                </div>
                            </td>
                            <td>
								<?php echo (int) $item->hits; ?>
                            </td>
                            <td class="small hidden-phone">
								<?php echo $this->escape( $item->access_level ); ?>
                            </td>
							<?php if ( $assoc ) : ?>
                                <td class="hidden-phone">
									<?php if ( $item->association ) : ?>
										<?php echo HTMLHelper::_( 'crosswordsadministrator.association', $item->id ); ?>
									<?php endif; ?>
                                </td>
							<?php endif; ?>
                            <td class="small hidden-phone">
								<?php if ( $item->created_by_alias ) : ?>
                                    <a href="<?php echo Route::_( 'index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by ); ?>"
                                       title="<?php echo Text::_( 'JAUTHOR' ); ?>">
										<?php echo $this->escape( $item->author_name ); ?></a>
                                    <p class="smallsub"> <?php echo Text::sprintf( 'JGLOBAL_LIST_ALIAS', $this->escape( $item->created_by_alias ) ); ?></p>
								<?php else : ?>
                                    <a href="<?php echo Route::_( 'index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by ); ?>"
                                       title="<?php echo Text::_( 'JAUTHOR' ); ?>">
										<?php echo $this->escape( $item->author_name ); ?></a>
								<?php endif; ?>
                            </td>
                            <td class="small hidden-phone">
								<?php if ( $item->language == '*' ): ?>
									<?php echo Text::alt( 'JALL', 'language' ); ?>
								<?php else: ?>
									<?php echo $item->language_title ? $this->escape( $item->language_title ) : Text::_( 'JUNDEFINED' ); ?>
								<?php endif; ?>
                            </td>
                            <td class="nowrap small hidden-phone">
								<?php echo HTMLHelper::_( 'date', $item->created, Text::_( 'DATE_FORMAT_LC4' ) ); ?>
                            </td>
                            <td class="center hidden-phone">
								<?php echo (int) $item->id; ?>
                            </td>
                        </tr>
					<?php endforeach; ?>
                    </tbody>
                </table>
			<?php endif; ?>
			<?php
			echo $this->pagination->getListFooter();
			//Load the batch processing form.
			if ( $user->authorise( 'core.create', 'com_crosswords' ) && $user->authorise( 'core.edit', 'com_crosswords' )
			     && $user->authorise( 'core.edit.state', 'com_crosswords' ) )
			{
				echo HTMLHelper::_( 'bootstrap.renderModal', 'collapseModal',
					[ 'title' => Text::_( 'COM_CROSSWORDS_BATCH_OPTIONS' ), 'footer' => $this->loadTemplate( 'batch_footer' ) ],
					$this->loadTemplate( 'batch_body' ) );
			}
			?>

            <input type="hidden" name="task" value=""/>
            <input type="hidden" name="boxchecked" value="0"/>
			<?php echo HTMLHelper::_( 'form.token' ); ?>
        </div>
</form>
