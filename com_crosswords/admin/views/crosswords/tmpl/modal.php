<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

defined( '_JEXEC' ) or die;

$app = Factory::getApplication();

if ( $app->isClient( 'site' ) )
{
	Session::checkToken( 'get' ) or die( Text::_( 'JINVALID_TOKEN' ) );
}

require_once JPATH_ROOT . '/components/com_crosswords/helpers/route.php';

HTMLHelper::addIncludePath( JPATH_ROOT . '/components/com_crosswords/helpers/html' );
HTMLHelper::_( 'bootstrap.tooltip' );

if ( APP_VERSION < 4 )
{
	HTMLHelper::_( 'behavior.framework', true );
}

$function = $app->input->getCmd( 'function', 'jSelectArticle' );
$listOrder = $this->escape( $this->state->get( 'list.ordering' ) );
$listDirn = $this->escape( $this->state->get( 'list.direction' ) );
?>
<form action="<?php echo Route::_( 'index.php?option=com_crosswords&view=crosswords&layout=modal&tmpl=component&function=' . $function . '&' . Session::getFormToken()
                                    . '=1' ); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">
    <fieldset class="filter clearfix">
        <div class="btn-toolbar">
            <div class="btn-group pull-left">
                <label for="filter_search">
					<?php echo Text::_( 'JSEARCH_FILTER_LABEL' ); ?>
                </label>
            </div>
            <div class="btn-group pull-left">
                <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape( $this->state->get( 'filter.search' ) ); ?>" size="30"
                       title="<?php echo Text::_( 'COM_CONTENT_FILTER_SEARCH_DESC' ); ?>"/>
            </div>
            <div class="btn-group pull-left">
                <button type="submit" class="btn hasTooltip" title="<?php echo HTMLHelper::tooltipText( 'JSEARCH_FILTER_SUBMIT' ); ?>" data-placement="bottom">
                    <span class="icon-search"></span><?php echo '&#160;' . Text::_( 'JSEARCH_FILTER_SUBMIT' ); ?></button>
                <button type="button" class="btn hasTooltip" title="<?php echo HTMLHelper::tooltipText( 'JSEARCH_FILTER_CLEAR' ); ?>" data-placement="bottom"
                        onclick="document.getElementById('filter_search').value='';this.form.submit();">
                    <span class="icon-remove"></span><?php echo '&#160;' . Text::_( 'JSEARCH_FILTER_CLEAR' ); ?></button>
            </div>
            <div class="clearfix"></div>
        </div>
        <hr class="hr-condensed"/>
        <div class="filters pull-left">
            <select name="filter_access" class="input-medium" onchange="this.form.submit()">
                <option value=""><?php echo Text::_( 'JOPTION_SELECT_ACCESS' ); ?></option>
				<?php echo HTMLHelper::_( 'select.options', HTMLHelper::_( 'access.assetgroups' ), 'value', 'text', $this->state->get( 'filter.access' ) ); ?>
            </select>

            <select name="filter_published" class="input-medium" onchange="this.form.submit()">
                <option value=""><?php echo Text::_( 'JOPTION_SELECT_PUBLISHED' ); ?></option>
				<?php echo HTMLHelper::_( 'select.options', HTMLHelper::_( 'jgrid.publishedOptions' ), 'value', 'text', $this->state->get( 'filter.published' ), true ); ?>
            </select>

			<?php if ( $this->state->get( 'filter.forcedLanguage' ) ) : ?>
            <select name="filter_category_id" class="input-medium" onchange="this.form.submit()">
                <option value=""><?php echo Text::_( 'JOPTION_SELECT_CATEGORY' ); ?></option>
				<?php echo HTMLHelper::_( 'select.options',
					HTMLHelper::_( 'category.options', 'com_content', [ 'filter.language' => [ '*', $this->state->get( 'filter.forcedLanguage' ) ] ] ), 'value', 'text',
					$this->state->get( 'filter.category_id' ) ); ?>
            </select>
            <input type="hidden" name="forcedLanguage" value="<?php echo $this->escape( $this->state->get( 'filter.forcedLanguage' ) ); ?>"/>
            <input type="hidden" name="filter_language" value="<?php echo $this->escape( $this->state->get( 'filter.language' ) ); ?>"/>
			<?php else : ?>
            <select name="filter_category_id" class="input-medium" onchange="this.form.submit()">
                <option value=""><?php echo Text::_( 'JOPTION_SELECT_CATEGORY' ); ?></option>
				<?php echo HTMLHelper::_( 'select.options', HTMLHelper::_( 'category.options', 'com_content' ), 'value', 'text', $this->state->get( 'filter.category_id' ) ); ?>
            </select>
            <select name="filter_language" class="input-medium" onchange="this.form.submit()">
                <option value=""><?php echo Text::_( 'JOPTION_SELECT_LANGUAGE' ); ?></option>
				<?php echo HTMLHelper::_( 'select.options', HTMLHelper::_( 'contentlanguage.existing', true, true ), 'value', 'text', $this->state->get( 'filter.language' ) ); ?>
            </select>
			<?php endif; ?>
        </div>
    </fieldset>

    <table class="table table-striped table-condensed">
        <thead>
        <tr>
            <th class="title">
				<?php echo HTMLHelper::_( 'grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder ); ?>
            </th>
            <th width="15%" class="center nowrap">
				<?php echo HTMLHelper::_( 'grid.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder ); ?>
            </th>
            <th width="15%" class="center nowrap">
				<?php echo HTMLHelper::_( 'grid.sort', 'JCATEGORY', 'a.catid', $listDirn, $listOrder ); ?>
            </th>
            <th width="5%" class="center nowrap">
				<?php echo HTMLHelper::_( 'grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder ); ?>
            </th>
            <th width="5%" class="center nowrap">
				<?php echo HTMLHelper::_( 'grid.sort', 'JDATE', 'a.created', $listDirn, $listOrder ); ?>
            </th>
            <th width="1%" class="center nowrap">
				<?php echo HTMLHelper::_( 'grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder ); ?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="15">
				<?php echo $this->pagination->getListFooter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody>
		<?php foreach ( $this->items as $i => $item ) : ?>
		<?php if ( $item->language && Multilanguage::isEnabled() )
		{
			$tag = strlen( $item->language );
			if ( $tag == 5 )
			{
				$lang = substr( $item->language, 0, 2 );
			}
            elseif ( $tag == 6 )
			{
				$lang = substr( $item->language, 0, 3 );
			}
			else
			{
				$lang = "";
			}
		}
        elseif ( ! Multilanguage::isEnabled() )
		{
			$lang = "";
		}
		?>
        <tr class="row<?php echo $i % 2; ?>">
            <td>
                <a href="javascript:void(0)"
                   onclick="if (window.parent){window.parent.<?php echo $this->escape( $function ); ?>('<?php echo $item->id; ?>', '<?php echo $this->escape( addslashes( $item->title ) ); ?>', '<?php echo $this->escape( $item->catid ); ?>', null, '<?php echo $this->escape( ContentHelperRoute::getArticleRoute( $item->id, $item->catid, $item->language ) ); ?>', '<?php echo $this->escape( $lang ); ?>', null);}">
					<?php echo $this->escape( $item->title ); ?></a>
            </td>
            <td class="center">
				<?php echo $this->escape( $item->access_level ); ?>
            </td>
            <td class="center">
				<?php echo $this->escape( $item->category_title ); ?>
            </td>
            <td class="center">
				<?php if ( $item->language == '*' ): ?>
				<?php echo Text::alt( 'JALL', 'language' ); ?>
				<?php else: ?>
				<?php echo $item->language_title ? $this->escape( $item->language_title ) : Text::_( 'JUNDEFINED' ); ?>
				<?php endif; ?>
            </td>
            <td class="center nowrap">
				<?php echo HTMLHelper::_( 'date', $item->created, Text::_( 'DATE_FORMAT_LC4' ) ); ?>
            </td>
            <td class="center">
				<?php echo (int) $item->id; ?>
            </td>
        </tr>
		<?php endforeach; ?>
        </tbody>
    </table>

    <div>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php echo HTMLHelper::_( 'form.token' ); ?>
    </div>
</form>
