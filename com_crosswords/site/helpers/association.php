<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2021 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Component\Categories\Administrator\Helper\CategoryAssociationHelper;

JLoader::register('CrosswordsHelper', JPATH_ADMINISTRATOR . '/components/com_crosswords/helpers/crosswords.php');
$version = new JVersion();

if (version_compare($version->getShortVersion(), '4.0', 'le')) {
    JLoader::register('CategoryHelperAssociation', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/association.php');
    
    abstract class CrosswordsHelperAssociation extends CategoryHelperAssociation
    {
        public static function getAssociations ($id = 0, $view = null)
        {
            jimport('helper.route', JPATH_COMPONENT_SITE);
            
            $app = JFactory::getApplication();
            $jinput = $app->input;
            $view = is_null($view) ? $jinput->get('view') : $view;
            $id = empty($id) ? $jinput->getInt('id') : $id;
            
            if ($view == 'crosswords')
            {
                if ($id)
                {
                    $associations = JLanguageAssociations::getAssociations('com_crosswords', '#__crosswords', 'com_crosswords.item', $id);
                    
                    $return = array();
                    
                    foreach ($associations as $tag => $item)
                    {
                        $return[$tag] = CrosswordsHelperRoute::getCrosswordRoute($item->id, $item->catid, $item->language);
                    }
                    
                    return $return;
                }
            }
            
            if ($view == 'category' || $view == 'categories')
            {
                return self::getCategoryAssociations($id, 'com_crosswords');
            }
            
            return array();
        }
    }
} else {
    
    /**
     * Crosswords Component Association Helper
     */
    abstract class CrosswordsHelperAssociation extends CategoryAssociationHelper
    {
        /**
         * Method to get the associations for a given item
         *
         * @param   integer  $id      Id of the item
         * @param   string   $view    Name of the view
         * @param   string   $layout  View layout
         *
         * @return  array   Array of associations for the item
         */
        public static function getAssociations($id = 0, $view = null, $layout = null)
        {
            $jinput    = Factory::getApplication()->input;
            $view      = $view ?? $jinput->get('view');
            $component = $jinput->getCmd('option');
            $id        = empty($id) ? $jinput->getInt('id') : $id;
            
            if ($layout === null && $jinput->get('view') == $view && $component == 'com_crosswords')
            {
                $layout = $jinput->get('layout', '', 'string');
            }
            
            if ($view === 'crosswords')
            {
                if ($id)
                {
                    $user      = Factory::getUser();
                    $groups    = implode(',', $user->getAuthorisedViewLevels());
                    $db        = Factory::getDbo();
                    $advClause = array();
                    
                    // Filter by user groups
                    $advClause[] = 'c2.access IN (' . $groups . ')';
                    
                    // Filter by current language
                    $advClause[] = 'c2.language != ' . $db->quote(Factory::getLanguage()->getTag());
                    
                    if (!$user->authorise('core.edit.state', 'com_crosswords') && !$user->authorise('core.edit', 'com_crosswords'))
                    {
                        // Filter by start and end dates.
                        $date = Factory::getDate();
                        
                        $nowDate = $db->quote($date->toSql());
                        
                        $advClause[] = '(c2.publish_up IS NULL OR c2.publish_up <= ' . $nowDate . ')';
                        $advClause[] = '(c2.publish_down IS NULL OR c2.publish_down >= ' . $nowDate . ')';
                        
                        // Filter by published
                        $advClause[] = 'c2.published = 1';
                    }
                    
                    $associations = Associations::getAssociations(
                        'com_crosswords',
                        '#__crosswords',
                        'com_crosswords.item',
                        $id,
                        'id',
                        'alias',
                        'catid',
                        $advClause
                        );
                    
                    $return = array();
                    
                    foreach ($associations as $tag => $item)
                    {
                        $return[$tag] = CrosswordsHelperRoute::getCrosswordRoute($item->id, (int) $item->catid, $item->language, $layout);
                    }
                    
                    return $return;
                }
            }
            
            if ($view === 'category' || $view === 'categories')
            {
                return self::getCategoryAssociations($id, 'com_crosswords', $layout);
            }
            
            return array();
        }
        
        /**
         * Method to display in frontend the associations for a given article
         *
         * @param   integer  $id  Id of the article
         *
         * @return  array  An array containing the association URL and the related language object
         */
        public static function displayAssociations($id)
        {
            $return = array();
            
            if ($associations = self::getAssociations($id, 'crossword'))
            {
                $levels    = Factory::getUser()->getAuthorisedViewLevels();
                $languages = LanguageHelper::getLanguages();
                
                foreach ($languages as $language)
                {
                    // Do not display language when no association
                    if (empty($associations[$language->lang_code]))
                    {
                        continue;
                    }
                    
                    // Do not display language without frontend UI
                    if (!array_key_exists($language->lang_code, LanguageHelper::getInstalledLanguages(0)))
                    {
                        continue;
                    }
                    
                    // Do not display language without specific home menu
                    if (!array_key_exists($language->lang_code, Multilanguage::getSiteHomePages()))
                    {
                        continue;
                    }
                    
                    // Do not display language without authorized access level
                    if (isset($language->access) && $language->access && !in_array($language->access, $levels))
                    {
                        continue;
                    }
                    
                    $return[$language->lang_code] = array('item' => $associations[$language->lang_code], 'language' => $language);
                }
            }
            
            return $return;
        }
    }
}
