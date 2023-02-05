<?php

/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2021 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die;

$data          = $displayData;
$params        = $displayData['params'];
$user          = JFactory::getUser();
$crosswordsUri = CrosswordsHelperRoute::getCrosswordsRoute();
?>
<nav class="navbar navbar-expand-lg bg-light">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#crosswordsNavbar"
                aria-controls="crosswordsNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="crosswordsNavbar">
            <a class="navbar-brand" href="<?php echo JRoute::_( $crosswordsUri ) ?>">
				<?php echo JText::_( 'COM_CROSSWORDS_HOME' ); ?>
            </a>
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						<?php echo JText::_( 'COM_CROSSWORDS_DISCOVER' ); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?php echo JRoute::_( CrosswordsHelperRoute::getCrosswordsRoute() ) ?>">
                                <i class="fa fa-leaf"></i> <?php echo JText::_( 'COM_CROSSWORDS_LATEST_CROSSWORDS' ); ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="filterCrosswords('hits', 'desc', 0); return false;">
                                <i class="fa fa-fire"></i> <?php echo JText::_( 'COM_CROSSWORDS_POPULAR_CROSSWORDS' ); ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="filterCrosswords('', '', 1); return false;">
                                <i class="fa fa-check"></i> <?php echo JText::_( 'COM_CROSSWORDS_SOLVED_CROSSWORDS' ); ?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

			<?php if ( ! $user->guest ): ?>
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<?php echo JText::_( 'COM_CROSSWORDS_ACCOUNT' ); ?>
                        </a>
                        <ul class="dropdown-menu">
							<?php if ( $user->authorise( 'core.create', 'com_crosswords' ) ): ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo JRoute::_( CrosswordsHelperRoute::getFormRoute() ); ?>">
                                        <i class="fa fa-th"></i> <?php echo JText::_( 'COM_CROSSWORDS_CREATE_CROSSWORD' ); ?>
                                    </a>
                                </li>
							<?php endif; ?>

							<?php if ( $user->authorise( 'core.keywords', 'com_crosswords' ) ): ?>
                                <li>
                                    <a class="dropdown-item" href="#keyword_form" role="button" data-bs-toggle="modal">
                                        <i class="fa fa-th-list"></i> <?php echo JText::_( 'COM_CROSSWORDS_SUBMIT_KEYWORD' ); ?>
                                    </a>
                                </li>
							<?php endif; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>

							<?php if ( $user->authorise( 'core.create', 'com_crosswords' ) ): ?>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa fa-edit"></i> <?php echo JText::_( 'COM_CROSSWORDS_MY_CROSSWORDS' ); ?>
                                    </a>
                                </li>
							<?php endif; ?>

                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fa fa-user"></i> <?php echo JText::_( 'COM_CROSSWORDS_MY_RESPONSES' ); ?>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
			<?php endif; ?>
        </div>
    </div>
</nav>

<?php
if ( $user->authorise( 'core.keywords', 'com_crosswords' ) )
{
	?>
    <div id="keyword_form" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="myModalLabel" class="modal-title"><?php echo JText::_( 'COM_CROSSWORDS_SUBMIT_KEYWORD' ); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info"><?php echo JText::_( 'COM_CROSSWORDS_SUBMIT_QUESTION_HELP' ); ?></div>
                    <div class="alert alert-error hide" id="submit-keyword-error"></div>
                    <div class="alert alert-success hide" id="submit-keyword-message"></div>

                    <form class="form-horizontal" id="form-submit-keyword" method="post" action="<?php echo JRoute::_( $crosswordsUri . '&task=keyword.save' ) ?>">
                        <div class="mb-3">
                            <label class="form-label" for="keyword"><?php echo JText::_( 'COM_CROSSWORDS_KEYWORD' ); ?></label>
                            <input type="text" id="keyword" name="keyword" class="form-control required" placeholder="<?php echo JText::_( 'COM_CROSSWORDS_KEYWORD' ); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="question"><?php echo JText::_( 'COM_CROSSWORDS_QUESTION' ); ?></label>
                            <input type="text" id="question" name="question" class="form-control required" placeholder="<?php echo JText::_( 'COM_CROSSWORDS_QUESTION' ); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="category"><?php echo JText::_( 'COM_CROSSWORDS_CATEGORY' ); ?></label>
							<?php
							$categories = JHTML::_( 'category.options', 'com_crosswords' );
							echo JHTML::_( 'select.genericlist', $categories, 'category', 'size="1" class="form-select"' );
							?>
                        </div>

						<?php echo JHtml::_( 'form.token' ); ?>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal" aria-hidden="true"><?php echo JText::_( 'COM_CROSSWORDS_CLOSE' ); ?></button>
                    <button class="btn btn-primary" id="save-keyword" type="button"><?php echo JText::_( 'JSUBMIT' ); ?></button>
                </div>
            </div>
        </div>
    </div>
	<?php
}
?>

<script type="text/javascript">
  <!--
  function filterCrosswords (order, direction, solved) {
    document.toolbarFilterForm.filter_order.value = order
    document.toolbarFilterForm.filter_order_Dir.value = direction
    document.toolbarFilterForm.filter_solved.value = solved

    document.toolbarFilterForm.submit()
  }

  //-->
</script>