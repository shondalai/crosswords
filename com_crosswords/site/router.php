<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2009 - 2018 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();


/*
 * Function to convert a system URL to a SEF URL
 */
function CrosswordsBuildRoute(&$query) {
    static $items;

    $segments	= array();
    if(isset($query['task'])) {
        $segments[] = $query['task'];
        unset($query['task']);
    }
    if(isset($query['id'])) {
        $segments[] = $query['id'];
        unset($query['id']);
    }
    if(isset($query['catid'])) {
        $segments[] = $query['catid'];
        unset($query['catid']);
    }
	unset($query['view']);
    return $segments;
}
/*
 * Function to convert a SEF URL back to a system URL
 */
function CrosswordsParseRoute($segments) {
    $vars = array();
    if(count($segments) > 0){
        $vars['task']	= $segments[0];
    }
    if(count($segments) > 1) {
        $vars['id']     = $segments[1];
        $vars['catid']  = $segments[1];
    }

    return $vars;
}
?>