<?php
/**
 * @version		$Id: script.php 74 2011-01-11 20:04:22Z maverick $
 * @package		CoreJoomla.croswords
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2013 corejoomla.com, Inc. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('CW_APP_NAME') or define('CW_APP_NAME', 'com_crosswords');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class com_crosswordsInstallerScript{
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent){
		// $parent is the class calling this method
		$parent->getParent()->setRedirectURL('index.php?option=com_crosswords');
	}

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent){
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_CROSSWORDS_UNINSTALL_TEXT') . '</p>';
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent){
		$db = JFactory::getDBO();
		if(method_exists($parent, 'extension_root')) {
			$sqlfile = $parent->getPath('extension_root').DS.'sql'.DS.'install.mysql.utf8.sql';
		} else {
			$sqlfile = $parent->getParent()->getPath('extension_root').DS.'sql'.DS.'install.mysql.utf8.sql';
		}
		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);
		if ($buffer !== false) {
			jimport('joomla.installer.helper');
			$queries = JInstallerHelper::splitSql($buffer);
			if (count($queries) != 0) {
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '' && $query{0} != '#') {
						$db->setQuery($query);
						if (!$db->execute()) {
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
							return false;
						}
					}
				}
			}
		}
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_CROSSWORDS_UPDATE_TEXT') . '</p>';
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent){
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		echo '<p>' . JText::_('COM_CROSSWORDS_PREFLIGHT_' . $type . '_TEXT') . '</p>';
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent){
		
		$db = JFactory::getDBO();
		$update_queries = array ();
		
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `asset_id` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0';;
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `description` MEDIUMTEXT';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `hits` INTEGER UNSIGNED NOT NULL DEFAULT 0';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `solved` INTEGER UNSIGNED NOT NULL DEFAULT 0';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `featured` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `language` VARCHAR(45) NOT NULL DEFAULT \'*\'';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `checked_out_time` DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\'';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `modified_by` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `modified` DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\'';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `publish_up` DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\'';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `publish_down` DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\'';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `access` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `ordering` INTEGER(11) UNSIGNED NOT NULL DEFAULT 0';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `created_by_alias` VARCHAR(255) ';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `attribs` VARCHAR(5120)';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `metakey` TEXT';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `metadesc` TEXT';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `metadata` TEXT';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `version` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` MODIFY COLUMN `published` TINYINT(3) NOT NULL DEFAULT 0';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords_KEYWORDS` MODIFY COLUMN `published` TINYINT(3) NOT NULL DEFAULT 0';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords_keywords` ADD COLUMN `language` VARCHAR(45) NOT NULL DEFAULT \'*\'';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords_keywords` ADD COLUMN `checked_out` INT(10) UNSIGNED NOT NULL DEFAULT 0';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords_keywords` ADD COLUMN `checked_out_time` DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\'';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords_keywords` ADD COLUMN `modified_by` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords_keywords` ADD COLUMN `modified` DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\'';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords_keywords` ADD COLUMN `access` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords_keywords` ADD COLUMN `ordering` INTEGER UNSIGNED NOT NULL DEFAULT 0';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` MODIFY COLUMN `published` TINYINT(3) NOT NULL DEFAULT 0';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords_keywords` MODIFY COLUMN `published` TINYINT(3) NOT NULL DEFAULT 0';
		
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD INDEX `idx_crosswords_created_by`(`created_by`)';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD INDEX `idx_crosswords_catid`(`catid`)';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD INDEX `idx_crosswords_published`(`published`)';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD INDEX `idx_crosswords_checkout`(`checked_out`)';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD INDEX `idx_crosswords_access`(`access`)';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD INDEX `idx_crosswords_language`(`language`)';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords_keywords` ADD INDEX `idx_crosswords_keywords_created_by`(`created_by`)';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords_keywords` ADD INDEX `idx_crosswords_keywords_catid`(`catid`)';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords_keywords` ADD INDEX `idx_crosswords_keywords_published`(`published`)';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords_keywords` ADD INDEX `idx_crosswords_keywords_checkout`(`checked_out`)';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords_keywords` ADD INDEX `idx_crosswords_keywords_access`(`access`)';
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords_keywords` ADD INDEX `idx_crosswords_keywords_language`(`language`)';
		
		foreach( $update_queries as $query ) {
			
			$db->setQuery( $query );
			try{ $db->execute(); }catch(Exception $e) {
// 				throw $e;
			}
		}
		
		/**************** MIGRATE OLD CATEGORIES ********************/
		if(!$this->migrate_categories($db)){
			
			echo 'There is an error upgrading categories. Please check categories and make sure they upgraded correctly. Report to us if you find any error.';
		}
		/**************** MIGRATE OLD CATEGORIES ********************/
		
		//ALTER TABLE `j25`.`jos_crosswords_categories` ADD COLUMN `migrate_id` INTEGER UNSIGNED NOT NULL DEFAULT 0
		
		/**************** MIGRATE OLD CATEGORIES ********************/
		
		echo "<b><font color=\"red\">Database tables successfully migrated to the latest version. Please check the configuration options once again.</font></b>";
	}
	
	private function migrate_categories($db){
		
		$query = $db->getQuery(true);
		$query->select('count(*) as row_count')->from('#__crosswords_categories')->where('migrate_id = 0');
		$db->setQuery($query);
		
		try {
				
			$count = $db->loadResult();
				
			if($count > 0){ // table exists, upgrade it
		
				$api = JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_cjlib'.DIRECTORY_SEPARATOR.'framework.php';
					
				if(!file_exists($api)) {
		
					echo '<p style="color: red"><strong>Please install CjLib component to use Community Crosswords.</strong></p>';
				} else {
		
					require_once $api;
		
					CJLib::import('corejoomla.framework.core');
					CJLib::import('corejoomla.nestedtree.core');
		
					$tree = new CjNestedTree($db, '#__crosswords_categories');
					$tree = $tree->get_tree();
						
					if(count($tree)){
		
						$basePath = JPATH_ADMINISTRATOR . '/components/com_categories';
						require_once $basePath . '/models/category.php';
						
						$config = array( 'table_path' => $basePath . '/tables');
						$catmodel = new CategoriesModelCategory($config);
						
						foreach ($tree as $category){

							if($category['migrate_id'] == 0) {
							
								if(!$this->do_migration($db, $category, $catmodel, $category['alias'], 0, 1, 0)){
									
									echo 'An error occurred while migrating categories, please contact support.';
									return false;
								}
							}
						}
						
						return true;
					}
				}
			}
		} catch (Exception $e) {}
		
		return false;
	}
	
	private function do_migration($db, &$node, $catmodel, $path, $child_id, $level, $parent_id){
		
		$catData = array(
				'id' => 0,
				'parent_id' => $parent_id,
				'level' => $level,
				'path' => $path,
				'extension' => 'com_crosswords',
				'title' => $node['title'],
				'alias' => $node['alias'],
				'description' => '',
				'published' => 1,
				'language' => '*');
		
		$status = $catmodel->save( $catData);
		$migrate_id = $catmodel->getState('category.id');

		if(!$status || !$migrate_id){
				
			JError::raiseWarning(500, JText::_('Unable to create default content category!'));
			return false;
		}
		
		$node['migrate_id'] = $migrate_id;
		
		try {
			
			$query = $db->getQuery(true);
			$query->update('#__crosswords_categories')->set('migrate_id = '.$migrate_id)->where('id = '.$node['id']);
			$db->setQuery($query);
			$db->execute();
			
			$query = $db->getQuery(true);
			$query->update('#__crosswords')->set('catid = '.$migrate_id)->where('catid = '.$node['id']);
			$db->setQuery($query);
			$db->execute();
			
			$query = $db->getQuery(true);
			$query->update('#__crosswords_keywords')->set('catid = '.$migrate_id)->where('catid = '.$node['id']);
			$db->setQuery($query);
			$db->execute();
		}catch (Exception $e){
			
			return false;
		}
		
		if(!empty($node['children'])){
			
			foreach ($node['children'] as $child) {
				
				if($child['migrate_id'] == 0) {
					
					if(!$this->do_migration($db, $child, $catmodel, $path.'/'.$child['alias'], $child['id'], $level + 1, $migrate_id)){
						
						return false;
					}
				}
			}
		}
		
		return true;
	}
}
