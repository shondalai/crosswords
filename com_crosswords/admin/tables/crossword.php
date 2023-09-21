<?php
/**
 * @package     Crosswords
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2023 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( 'JPATH_PLATFORM' ) or die();

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Tag\TaggableTableTrait;
use Joomla\Database\DatabaseDriver;
use Joomla\String\StringHelper;

class CrosswordsTableCrosswordBase extends Table {

	private $_aliasNum = 0;
	public $_moderate = false;
	protected $_jsonEncode = [ 'params', 'metadata', 'attribs' ];

	public function __construct( DatabaseDriver $db ) {
		parent::__construct( '#__crosswords', 'id', $db );
		parent::setColumnAlias( 'published', 'state' );

		if ( APP_VERSION < 4 )
		{
			JTableObserverTags::createObserver( $this, [ 'typeAlias' => 'com_crosswords.crossword' ] );
			JTableObserverContenthistory::createObserver( $this, [ 'typeAlias' => 'com_crosswords.crossword' ] );
		}
		else
		{
			$this->typeAlias = 'com_crosswords.crossword';
		}
	}

	protected function _getAssetName() {
		$k = $this->_tbl_key;

		return 'com_crosswords.crossword.' . (int) $this->$k;
	}

	protected function _getAssetTitle() {
		return $this->title;
	}

	protected function _getAssetParentId( Table $table = null, $id = null ) {
		$assetId = null;

		// This is a crossword under a category.
		if ( $this->catid )
		{
			// Build the query to get the asset id for the parent category.
			$query = $this->_db->getQuery( true )
			                   ->select( $this->_db->quoteName( 'asset_id' ) )
			                   ->from( $this->_db->quoteName( '#__categories' ) )
			                   ->where( $this->_db->quoteName( 'id' ) . ' = ' . (int) $this->catid );

			// Get the asset id from the database.
			$this->_db->setQuery( $query );

			if ( $result = $this->_db->loadResult() )
			{
				$assetId = (int) $result;
			}
		}

		// Return the asset id.
		if ( $assetId )
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId( $table, $id );
		}
	}

	public function bind( $array, $ignore = '' ) {
		return parent::bind( $array, $ignore );
	}

	public function check() {
		if ( trim( $this->title ) == '' )
		{
			throw new Exception( Text::_( 'COM_CROSSWORDS_WARNING_PROVIDE_VALID_NAME' ), 500 );

			return false;
		}

		if ( trim( $this->alias ) == '' )
		{
			$this->alias = $this->title;
		}

		$this->alias = ApplicationHelper::stringURLSafe( $this->alias, $this->language );

		if ( trim( str_replace( '-', '', $this->alias ) ) == '' )
		{
			$this->alias = Factory::getDate()->format( 'Y-m-d-H-i-s' );
		}

		// Check the publish down date is not earlier than publish up.
		if ( $this->publish_down > $this->_db->getNullDate() && $this->publish_down < $this->publish_up )
		{
			// Swap the dates.
			$temp               = $this->publish_up;
			$this->publish_up   = $this->publish_down;
			$this->publish_down = $temp;
		}

		// Clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if ( ! empty( $this->metakey ) )
		{
			// Only process if not empty

			// Array of characters to remove
			$bad_characters = [ "\n", "\r", "\"", "<", ">" ];

			// Remove bad characters
			$after_clean = StringHelper::str_ireplace( $bad_characters, "", $this->metakey );

			// Create array using commas as delimiter
			$keys = explode( ',', $after_clean );

			$clean_keys = [];

			foreach ( $keys as $key )
			{
				if ( trim( $key ) )
				{
					// Ignore blank keywords
					$clean_keys[] = trim( $key );
				}
			}
			// Put array back together delimited by ", "
			$this->metakey = implode( ", ", $clean_keys );
		}

		return true;
	}

	public function store( $updateNulls = false ) {
		// Verify that the alias is unique
		$table = Table::getInstance( 'Crossword', 'CrosswordsTable' );
		$alias = $this->_aliasNum ? $this->alias . '_' . $this->_aliasNum : $this->alias;

		if ( $table->load( [ 'alias' => $alias, 'catid' => $this->catid ] ) && ( $table->id != $this->id || $this->id == 0 ) )
		{
			$this->_aliasNum ++;

			return $this->store( $updateNulls );
		}

		$this->alias = $alias;

		return parent::store( $updateNulls );
	}

	/**
	 * Get the type alias for UCM features
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   4.0.0
	 */
	public function getTypeAlias() {
		return $this->typeAlias;
	}

}

if ( interface_exists( '\Joomla\CMS\Tag\TaggableTableInterface' ) )
{
	class CrosswordsTableCrossword extends CrosswordsTableCrosswordBase implements TaggableTableInterface {

		use TaggableTableTrait;

		public function __construct( DatabaseDriver $db ) {
			parent::__construct( $db );
		}

	}
}
else
{
	class CrosswordsTableCrossword extends CrosswordsTableCrosswordBase {

		public function __construct( DatabaseDriver $db ) {
			parent::__construct( $db );
		}

	}
}