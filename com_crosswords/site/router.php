<?php
/**
 * Joomla! 1.5 component Crosswords
 *
 * @version $Id: router.php 2010-10-16 12:32:21 svn $
 * @author Maverick
 * @package Joomla
 * @subpackage Crosswords
 * @license GNU/GPL
 *
 * Crosswords is a Joomla component to generate crosswords with Community touch.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

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