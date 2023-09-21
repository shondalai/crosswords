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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('CW_APP_NAME') or define('CW_APP_NAME', 'com_crosswords');

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
		echo '<p>' . Text::_('COM_CROSSWORDS_UNINSTALL_TEXT') . '</p>';
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent){
		$db = Factory::getDBO();
		if (method_exists($parent, 'extension_root'))
		{
			$sqlfile = $parent->getPath('extension_root') . '/sql/install.mysql.utf8.sql';
		}
		else
		{
			$sqlfile = $parent->getParent()->getPath('extension_root') . '/sql/install.mysql.utf8.sql';
		}
		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);
		if ($buffer !== false)
		{
			jimport('joomla.installer.helper');
			$queries = $db->splitSql($buffer);
			if (count($queries) != 0)
			{
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '' && $query[0] != '#')
					{
						$db->setQuery($query);
						if (! $db->execute())
						{
							// 							return false;
						}
					}
				}
			}
		}

		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_CROSSWORDS_UPDATE_TEXT') . '</p>';
		$parent->getParent()->setRedirectURL('index.php?option=com_crosswords&view=dashboard');
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent){
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		echo '<p>' . Text::_('COM_CROSSWORDS_PREFLIGHT_' . $type . '_TEXT') . '</p>';
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent){
		
		$db = Factory::getDbo();
		$update_queries = array ();
		
		$update_queries[] = 'ALTER IGNORE TABLE `#__crosswords` ADD COLUMN `asset_id` INTEGER(10) UNSIGNED NOT NULL DEFAULT 0';
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
		
		echo "<b><font color=\"red\">Database tables successfully migrated to the latest version. Please check the configuration options once again.</font></b>";
	}
}
