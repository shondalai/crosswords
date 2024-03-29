<?php
/**
 * @version        $Id: crosswords.php 01 2011-08-13 11:37:09Z maverick $
 * @package        CoreJoomla.Crosswords
 * @subpackage     Components
 * @copyright      Copyright (C) 2009 - 2011 corejoomla.com. All rights reserved.
 * @author         Maverick
 * @link           http://www.corejoomla.com/
 * @license        License GNU General Public License version 2 or later
 */
defined( '_JEXEC' ) or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

class CrosswordsModelCrosswords extends ListModel {

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     \JController
	 * @since   4.0.0
	 */
	public function __construct( $config = [] ) {
		if ( empty( $config['filter_fields'] ) )
		{
			$config['filter_fields'] = [
				'id',
				'a.id',
				'title',
				'a.title',
				'alias',
				'a.alias',
				'checked_out',
				'a.checked_out',
				'checked_out_time',
				'a.checked_out_time',
				'catid',
				'a.catid',
				'category_title',
				'published',
				'a.published',
				'access',
				'a.access',
				'access_level',
				'created',
				'a.created',
				'created_by',
				'a.created_by',
				'ordering',
				'a.ordering',
				'featured',
				'a.featured',
				'language',
				'a.language',
				'hits',
				'a.hits',
				'publish_up',
				'a.publish_up',
				'publish_down',
				'a.publish_down',
				'filter_tag',
			];
		}

		parent::__construct( $config );
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function populateState( $ordering = 'ordering', $direction = 'ASC' ) {
		$app = Factory::getApplication();

		// List state information
		$value = $app->input->get( 'limit', $app->get( 'list_limit', 0 ), 'uint' );
		$this->setState( 'list.limit', $value );

		$value = $app->input->get( 'limitstart', 0, 'uint' );
		$this->setState( 'list.start', $value );

		$value = $app->input->get( 'filter_tag', 0, 'uint' );
		$this->setState( 'filter.tag', $value );

		$orderCol = $app->input->get( 'filter_order', 'a.ordering' );

		if ( ! in_array( $orderCol, $this->filter_fields ) )
		{
			$orderCol = 'a.ordering';
		}

		$this->setState( 'list.ordering', $orderCol );

		$listOrder = $app->input->get( 'filter_order_Dir', 'ASC' );

		if ( ! in_array( strtoupper( $listOrder ), [ 'ASC', 'DESC', '' ] ) )
		{
			$listOrder = 'ASC';
		}

		$this->setState( 'list.direction', $listOrder );

		$params = $app->getParams();
		$this->setState( 'params', $params );

		$user = Factory::getUser();

		if ( ( ! $user->authorise( 'core.edit.state', 'com_crosswords' ) ) && ( ! $user->authorise( 'core.edit', 'com_crosswords' ) ) )
		{
			// Filter on published for those who do not have edit or edit.state rights.
			$this->setState( 'filter.published', 1 );
		}

		$this->setState( 'filter.language', Multilanguage::isEnabled() );

		// Process show_noauth parameter
		if ( ( ! $params->get( 'show_noauth' ) ) || ( ! ComponentHelper::getParams( 'com_crosswords' )->get( 'show_noauth' ) ) )
		{
			$this->setState( 'filter.access', true );
		}
		else
		{
			$this->setState( 'filter.access', false );
		}

		$this->setState( 'layout', $app->input->getString( 'layout' ) );
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   4.0.0
	 */
	protected function getStoreId( $id = '' ) {
		// Compile the store id.
		$id .= ':' . serialize( $this->getState( 'filter.published' ) );
		$id .= ':' . $this->getState( 'filter.access' );
		$id .= ':' . $this->getState( 'filter.featured' );
		$id .= ':' . serialize( $this->getState( 'filter.crossword_id' ) );
		$id .= ':' . $this->getState( 'filter.crossword_id.include' );
		$id .= ':' . serialize( $this->getState( 'filter.category_id' ) );
		$id .= ':' . $this->getState( 'filter.category_id.include' );
		$id .= ':' . serialize( $this->getState( 'filter.author_id' ) );
		$id .= ':' . $this->getState( 'filter.author_id.include' );
		$id .= ':' . serialize( $this->getState( 'filter.author_alias' ) );
		$id .= ':' . $this->getState( 'filter.author_alias.include' );
		$id .= ':' . $this->getState( 'filter.date_filtering' );
		$id .= ':' . $this->getState( 'filter.date_field' );
		$id .= ':' . $this->getState( 'filter.start_date_range' );
		$id .= ':' . $this->getState( 'filter.end_date_range' );
		$id .= ':' . $this->getState( 'filter.relative_date' );
		$id .= ':' . serialize( $this->getState( 'filter.tag' ) );

		return parent::getStoreId( $id );
	}

	/**
	 * Get the master query for retrieving a list of crosswords subject to the model state.
	 *
	 * @return  \Joomla\Database\DatabaseQuery
	 *
	 * @since   4.0.0
	 */
	protected function getListQuery() {
		$user = Factory::getUser();

		// Create a new query object.
		$db = $this->getDbo();

		/** @var \Joomla\Database\DatabaseQuery $query */
		$query = $db->getQuery( true );

		$nowDate = Factory::getDate()->toSql();

		$conditionArchived    = 2;
		$conditionUnpublished = 0;

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				[
					$db->quoteName( 'a.id' ),
					$db->quoteName( 'a.title' ),
					$db->quoteName( 'a.alias' ),
					$db->quoteName( 'a.description' ),
					$db->quoteName( 'a.checked_out' ),
					$db->quoteName( 'a.checked_out_time' ),
					$db->quoteName( 'a.catid' ),
					$db->quoteName( 'a.created' ),
					$db->quoteName( 'a.created_by' ),
					$db->quoteName( 'a.created_by_alias' ),
					$db->quoteName( 'a.modified' ),
					$db->quoteName( 'a.modified_by' ),
					// Use created if publish_up is null
					'CASE WHEN ' . $db->quoteName( 'a.publish_up' ) . ' IS NULL THEN ' . $db->quoteName( 'a.created' )
					. ' ELSE ' . $db->quoteName( 'a.publish_up' ) . ' END AS ' . $db->quoteName( 'publish_up' ),
					$db->quoteName( 'a.publish_down' ),
					$db->quoteName( 'a.attribs' ),
					$db->quoteName( 'a.metadata' ),
					$db->quoteName( 'a.metakey' ),
					$db->quoteName( 'a.metadesc' ),
					$db->quoteName( 'a.access' ),
					$db->quoteName( 'a.hits' ),
					$db->quoteName( 'a.featured' ),
					$db->quoteName( 'a.language' ),
					$db->quoteName( 'a.ordering' ),
				]
			)
		)
		      ->select(
			      [
				      // Published/archived crossword in archived category is treated as archived crossword. If category is not published then force 0.
				      'CASE WHEN ' . $db->quoteName( 'c.published' ) . ' = 2 AND ' . $db->quoteName( 'a.published' ) . ' > 0 THEN ' . $conditionArchived
				      . ' WHEN ' . $db->quoteName( 'c.published' ) . ' != 1 THEN ' . $conditionUnpublished
				      . ' ELSE ' . $db->quoteName( 'a.published' ) . ' END AS ' . $db->quoteName( 'published' ),
				      $db->quoteName( 'c.title', 'category_title' ),
				      $db->quoteName( 'c.path', 'category_route' ),
				      $db->quoteName( 'c.access', 'category_access' ),
				      $db->quoteName( 'c.alias', 'category_alias' ),
				      $db->quoteName( 'c.language', 'category_language' ),
				      $db->quoteName( 'c.published' ),
				      $db->quoteName( 'c.published', 'parents_published' ),
				      $db->quoteName( 'c.lft' ),
				      'CASE WHEN ' . $db->quoteName( 'a.created_by_alias' ) . ' > ' . $db->quote( ' ' ) . ' THEN ' . $db->quoteName( 'a.created_by_alias' )
				      . ' ELSE ' . $db->quoteName( 'ua.name' ) . ' END AS ' . $db->quoteName( 'author' ),
				      $db->quoteName( 'ua.email', 'author_email' ),
				      $db->quoteName( 'uam.name', 'modified_by_name' ),
				      $db->quoteName( 'parent.title', 'parent_title' ),
				      $db->quoteName( 'parent.id', 'parent_id' ),
				      $db->quoteName( 'parent.path', 'parent_route' ),
				      $db->quoteName( 'parent.alias', 'parent_alias' ),
				      $db->quoteName( 'parent.language', 'parent_language' ),
			      ]
		      )
		      ->from( $db->quoteName( '#__crosswords', 'a' ) )
		      ->join( 'LEFT', $db->quoteName( '#__categories', 'c' ), $db->quoteName( 'c.id' ) . ' = ' . $db->quoteName( 'a.catid' ) )
		      ->join( 'LEFT', $db->quoteName( '#__users', 'ua' ), $db->quoteName( 'ua.id' ) . ' = ' . $db->quoteName( 'a.created_by' ) )
		      ->join( 'LEFT', $db->quoteName( '#__users', 'uam' ), $db->quoteName( 'uam.id' ) . ' = ' . $db->quoteName( 'a.modified_by' ) )
		      ->join( 'LEFT', $db->quoteName( '#__categories', 'parent' ), $db->quoteName( 'parent.id' ) . ' = ' . $db->quoteName( 'c.parent_id' ) );

		$params = $this->getState( 'params' );

		// Filter by access level.
		if ( $this->getState( 'filter.access', true ) )
		{
			$groups = $this->getState( 'filter.viewlevels', $user->getAuthorisedViewLevels() );
			$query->whereIn( $db->quoteName( 'a.access' ), $groups );
			$query->whereIn( $db->quoteName( 'c.access' ), $groups );
		}

		// Filter by published state
		$condition = $this->getState( 'filter.published' );

		if ( is_numeric( $condition ) && $condition == 2 )
		{
			/**
			 * If category is archived then crossword has to be published or archived.
			 * Or category is published then crossword has to be archived.
			 */
			$query->where( '((' . $db->quoteName( 'c.published' ) . ' = 2 AND ' . $db->quoteName( 'a.published' ) . ' > :conditionUnpublished)'
			               . ' OR (' . $db->quoteName( 'c.published' ) . ' = 1 AND ' . $db->quoteName( 'a.published' ) . ' = :conditionArchived))' )
			      ->bind( ':conditionUnpublished', $conditionUnpublished, ParameterType::INTEGER )
			      ->bind( ':conditionArchived', $conditionArchived, ParameterType::INTEGER );
		}
		elseif ( is_numeric( $condition ) )
		{
			$condition = (int) $condition;

			// Category has to be published
			$query->where( $db->quoteName( 'c.published' ) . ' = 1 AND ' . $db->quoteName( 'a.published' ) . ' = :condition' )
			      ->bind( ':condition', $condition, ParameterType::INTEGER );
		}
		elseif ( is_array( $condition ) )
		{
			// Category has to be published
			$query->where(
				$db->quoteName( 'c.published' ) . ' = 1 AND ' . $db->quoteName( 'a.published' )
				. ' IN (' . implode( ',', $query->bindArray( $condition ) ) . ')'
			);
		}

		// Filter by featured state
		$featured = $this->getState( 'filter.featured' );

		switch ( $featured )
		{
			case 'hide':
				$query->where( $db->quoteName( 'a.featured' ) . ' = 0' );
				break;

			case 'only':
				$query->where( $db->quoteName( 'a.featured' ) . ' = 1' );
				break;

			case 'show':
			default:
				// Normally we do not discriminate between featured/unfeatured items.
				break;
		}

		// Filter by a single or group of crosswords.
		$crosswordId = $this->getState( 'filter.crossword_id' );

		if ( is_numeric( $crosswordId ) )
		{
			$crosswordId = (int) $crosswordId;
			$type        = $this->getState( 'filter.crossword_id.include', true ) ? ' = ' : ' <> ';
			$query->where( $db->quoteName( 'a.id' ) . $type . ':crosswordId' )
			      ->bind( ':crosswordId', $crosswordId, ParameterType::INTEGER );
		}
		elseif ( is_array( $crosswordId ) )
		{
			$crosswordId = ArrayHelper::toInteger( $crosswordId );

			if ( $this->getState( 'filter.crossword_id.include', true ) )
			{
				$query->whereIn( $db->quoteName( 'a.id' ), $crosswordId );
			}
			else
			{
				$query->whereNotIn( $db->quoteName( 'a.id' ), $crosswordId );
			}
		}

		// Filter by a single or group of categories
		$categoryId = $this->getState( 'filter.category_id' );

		if ( is_numeric( $categoryId ) )
		{
			$type = $this->getState( 'filter.category_id.include', true ) ? ' = ' : ' <> ';

			// Add subcategory check
			$includeSubcategories = $this->getState( 'filter.subcategories', false );

			if ( $includeSubcategories )
			{
				$categoryId = (int) $categoryId;
				$levels     = (int) $this->getState( 'filter.max_category_levels', 1 );

				// Create a subquery for the subcategory list
				$subQuery = $db->getQuery( true )
				               ->select( $db->quoteName( 'sub.id' ) )
				               ->from( $db->quoteName( '#__categories', 'sub' ) )
				               ->join(
					               'INNER',
					               $db->quoteName( '#__categories', 'this' ),
					               $db->quoteName( 'sub.lft' ) . ' > ' . $db->quoteName( 'this.lft' )
					               . ' AND ' . $db->quoteName( 'sub.rgt' ) . ' < ' . $db->quoteName( 'this.rgt' )
				               )
				               ->where( $db->quoteName( 'this.id' ) . ' = :subCategoryId' );

				$query->bind( ':subCategoryId', $categoryId, ParameterType::INTEGER );

				if ( $levels >= 0 )
				{
					$subQuery->where( $db->quoteName( 'sub.level' ) . ' <= ' . $db->quoteName( 'this.level' ) . ' + :levels' );
					$query->bind( ':levels', $levels, ParameterType::INTEGER );
				}

				// Add the subquery to the main query
				$query->where(
					'(' . $db->quoteName( 'a.catid' ) . $type . ':categoryId OR ' . $db->quoteName( 'a.catid' ) . ' IN (' . $subQuery . '))'
				);
				$query->bind( ':categoryId', $categoryId, ParameterType::INTEGER );
			}
			else
			{
				$query->where( $db->quoteName( 'a.catid' ) . $type . ':categoryId' );
				$query->bind( ':categoryId', $categoryId, ParameterType::INTEGER );
			}
		}
		elseif ( is_array( $categoryId ) && ( count( $categoryId ) > 0 ) )
		{
			$categoryId = ArrayHelper::toInteger( $categoryId );

			if ( ! empty( $categoryId ) )
			{
				if ( $this->getState( 'filter.category_id.include', true ) )
				{
					$query->whereIn( $db->quoteName( 'a.catid' ), $categoryId );
				}
				else
				{
					$query->whereNotIn( $db->quoteName( 'a.catid' ), $categoryId );
				}
			}
		}

		// Filter by author
		$authorId    = $this->getState( 'filter.author_id' );
		$authorWhere = '';

		if ( is_numeric( $authorId ) )
		{
			$authorId    = (int) $authorId;
			$type        = $this->getState( 'filter.author_id.include', true ) ? ' = ' : ' <> ';
			$authorWhere = $db->quoteName( 'a.created_by' ) . $type . ':authorId';
			$query->bind( ':authorId', $authorId, ParameterType::INTEGER );
		}
		elseif ( is_array( $authorId ) )
		{
			$authorId = array_values( array_filter( $authorId, 'is_numeric' ) );

			if ( $authorId )
			{
				$type        = $this->getState( 'filter.author_id.include', true ) ? ' IN' : ' NOT IN';
				$authorWhere = $db->quoteName( 'a.created_by' ) . $type . ' (' . implode( ',', $query->bindArray( $authorId ) ) . ')';
			}
		}

		// Filter by author alias
		$authorAlias      = $this->getState( 'filter.author_alias' );
		$authorAliasWhere = '';

		if ( is_string( $authorAlias ) )
		{
			$type             = $this->getState( 'filter.author_alias.include', true ) ? ' = ' : ' <> ';
			$authorAliasWhere = $db->quoteName( 'a.created_by_alias' ) . $type . ':authorAlias';
			$query->bind( ':authorAlias', $authorAlias );
		}
		elseif ( is_array( $authorAlias ) && ! empty( $authorAlias ) )
		{
			$type             = $this->getState( 'filter.author_alias.include', true ) ? ' IN' : ' NOT IN';
			$authorAliasWhere = $db->quoteName( 'a.created_by_alias' ) . $type
			                    . ' (' . implode( ',', $query->bindArray( $authorAlias, ParameterType::STRING ) ) . ')';
		}

		if ( ! empty( $authorWhere ) && ! empty( $authorAliasWhere ) )
		{
			$query->where( '(' . $authorWhere . ' OR ' . $authorAliasWhere . ')' );
		}
		elseif ( ! empty( $authorWhere ) || ! empty( $authorAliasWhere ) )
		{
			// One of these is empty, the other is not so we just add both
			$query->where( $authorWhere . $authorAliasWhere );
		}

		// Filter by start and end dates.
		if ( ( ! $user->authorise( 'core.edit.state', 'com_crosswords' ) ) && ( ! $user->authorise( 'core.edit', 'com_crosswords' ) ) )
		{
			$query->where(
				[
					'(' . $db->quoteName( 'a.publish_up' ) . ' IS NULL OR ' . $db->quoteName( 'a.publish_up' ) . ' <= :publishUp)',
					'(' . $db->quoteName( 'a.publish_down' ) . ' IS NULL OR ' . $db->quoteName( 'a.publish_down' ) . ' >= :publishDown)',
				]
			)
			      ->bind( ':publishUp', $nowDate )
			      ->bind( ':publishDown', $nowDate );
		}

		// Filter by Date Range or Relative Date
		$dateFiltering = $this->getState( 'filter.date_filtering', 'off' );
		$dateField     = $db->escape( $this->getState( 'filter.date_field', 'a.created' ) );

		switch ( $dateFiltering )
		{
			case 'range':
				$startDateRange = $this->getState( 'filter.start_date_range', '' );
				$endDateRange   = $this->getState( 'filter.end_date_range', '' );

				if ( $startDateRange || $endDateRange )
				{
					$query->where( $db->quoteName( $dateField ) . ' IS NOT NULL' );

					if ( $startDateRange )
					{
						$query->where( $db->quoteName( $dateField ) . ' >= :startDateRange' )
						      ->bind( ':startDateRange', $startDateRange );
					}

					if ( $endDateRange )
					{
						$query->where( $db->quoteName( $dateField ) . ' <= :endDateRange' )
						      ->bind( ':endDateRange', $endDateRange );
					}
				}

				break;

			case 'relative':
				$relativeDate = (int) $this->getState( 'filter.relative_date', 0 );
				$query->where(
					$db->quoteName( $dateField ) . ' IS NOT NULL AND '
					. $db->quoteName( $dateField ) . ' >= ' . $query->dateAdd( $db->quote( $nowDate ), - 1 * $relativeDate, 'DAY' )
				);
				break;

			case 'off':
			default:
				break;
		}

		// Process the filter for list views with user-entered filters
		if ( is_object( $params ) && ( $params->get( 'filter_field' ) !== 'hide' ) && ( $filter = $this->getState( 'list.filter' ) ) )
		{
			// Clean filter variable
			$filter      = StringHelper::strtolower( $filter );
			$monthFilter = $filter;
			$hitsFilter  = (int) $filter;
			$textFilter  = '%' . $filter . '%';

			switch ( $params->get( 'filter_field' ) )
			{
				case 'author':
					$query->where(
						'LOWER(CASE WHEN ' . $db->quoteName( 'a.created_by_alias' ) . ' > ' . $db->quote( ' ' )
						. ' THEN ' . $db->quoteName( 'a.created_by_alias' ) . ' ELSE ' . $db->quoteName( 'ua.name' ) . ' END) LIKE :search'
					)
					      ->bind( ':search', $textFilter );
					break;

				case 'hits':
					$query->where( $db->quoteName( 'a.hits' ) . ' >= :hits' )
					      ->bind( ':hits', $hitsFilter, ParameterType::INTEGER );
					break;

				case 'month':
					if ( $monthFilter != '' )
					{
						$monthStart = date( "Y-m-d", strtotime( $monthFilter ) ) . ' 00:00:00';
						$monthEnd   = date( "Y-m-t", strtotime( $monthFilter ) ) . ' 23:59:59';

						$query->where(
							[
								':monthStart <= CASE WHEN a.publish_up IS NULL THEN a.created ELSE a.publish_up END',
								':monthEnd >= CASE WHEN a.publish_up IS NULL THEN a.created ELSE a.publish_up END',
							]
						)
						      ->bind( ':monthStart', $monthStart )
						      ->bind( ':monthEnd', $monthEnd );
					}
					break;

				case 'title':
				default:
					// Default to 'title' if parameter is not valid
					$query->where( 'LOWER(' . $db->quoteName( 'a.title' ) . ') LIKE :search' )
					      ->bind( ':search', $textFilter );
					break;
			}
		}

		// Filter by language
		if ( $this->getState( 'filter.language' ) )
		{
			$query->whereIn( $db->quoteName( 'a.language' ), [ Factory::getApplication()->getLanguage()->getTag(), '*' ], ParameterType::STRING );
		}

		// Filter by a single or group of tags.
		$tagId = $this->getState( 'filter.tag' );

		if ( is_array( $tagId ) && count( $tagId ) === 1 )
		{
			$tagId = current( $tagId );
		}

		if ( is_array( $tagId ) )
		{
			$tagId = ArrayHelper::toInteger( $tagId );

			if ( $tagId )
			{
				$subQuery = $db->getQuery( true )
				               ->select( 'DISTINCT ' . $db->quoteName( 'content_item_id' ) )
				               ->from( $db->quoteName( '#__contentitem_tag_map' ) )
				               ->where(
					               [
						               $db->quoteName( 'tag_id' ) . ' IN (' . implode( ',', $query->bindArray( $tagId ) ) . ')',
						               $db->quoteName( 'type_alias' ) . ' = ' . $db->quote( 'com_crosswords.crossword' ),
					               ]
				               );

				$query->join(
					'INNER',
					'(' . $subQuery . ') AS ' . $db->quoteName( 'tagmap' ),
					$db->quoteName( 'tagmap.content_item_id' ) . ' = ' . $db->quoteName( 'a.id' )
				);
			}
		}
		elseif ( $tagId = (int) $tagId )
		{
			$query->join(
				'INNER',
				$db->quoteName( '#__contentitem_tag_map', 'tagmap' ),
				$db->quoteName( 'tagmap.content_item_id' ) . ' = ' . $db->quoteName( 'a.id' )
				. ' AND ' . $db->quoteName( 'tagmap.type_alias' ) . ' = ' . $db->quote( 'com_crosswords.crossword' )
			)
			      ->where( $db->quoteName( 'tagmap.tag_id' ) . ' = :tagId' )
			      ->bind( ':tagId', $tagId, ParameterType::INTEGER );
		}

		// Add the list ordering clause.
		$query->order(
			$db->escape( $this->getState( 'list.ordering', 'a.ordering' ) ) . ' ' . $db->escape( $this->getState( 'list.direction', 'ASC' ) )
		);

		return $query;
	}

	/**
	 * Method to get a list of crosswords.
	 *
	 * Overridden to inject convert the attribs field into a Registry object.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 * @since   4.0.0
	 */
	public function getItems() {
		$items = parent::getItems();

		$user   = Factory::getUser();
		$userId = $user->get( 'id' );
		$guest  = $user->get( 'guest' );
		$groups = $user->getAuthorisedViewLevels();
		$input  = Factory::getApplication()->input;

		// Get the global params
		$globalParams = ComponentHelper::getParams( 'com_crosswords', true );

		$taggedItems = [];

		// Convert the parameter fields into objects.
		foreach ( $items as $item )
		{
			$crosswordParams = new Registry( $item->attribs );

			// Unpack readmore and layout params
			$item->alternative_readmore = $crosswordParams->get( 'alternative_readmore' );
			$item->layout               = $crosswordParams->get( 'layout' );

			$item->params = clone $this->getState( 'params' );
			$item->params->merge( $crosswordParams );

			// Get display date
			switch ( $item->params->get( 'list_show_date' ) )
			{
				case 'modified':
					$item->displayDate = $item->modified;
					break;

				case 'published':
					$item->displayDate = ( $item->publish_up == 0 ) ? $item->created : $item->publish_up;
					break;

				default:
				case 'created':
					$item->displayDate = $item->created;
					break;
			}

			/**
			 * Compute the asset access permissions.
			 * Technically guest could edit an crossword, but lets not check that to improve performance a little.
			 */
			if ( ! $guest )
			{
				$asset = 'com_crosswords.crossword.' . $item->id;

				// Check general edit permission first.
				if ( $user->authorise( 'core.edit', $asset ) )
				{
					$item->params->set( 'access-edit', true );
				}
				elseif ( ! empty( $userId ) && $user->authorise( 'core.edit.own', $asset ) )
				{
					// Now check if edit.own is available.
					// Check for a valid user and that they are the owner.
					if ( $userId == $item->created_by )
					{
						$item->params->set( 'access-edit', true );
					}
				}
			}

			$access = $this->getState( 'filter.access' );

			if ( $access )
			{
				// If the access filter has been set, we already have only the crosswords this user can view.
				$item->params->set( 'access-view', true );
			}
			else
			{
				// If no access filter is set, the layout takes some responsibility for display of limited information.
				if ( $item->catid == 0 || $item->category_access === null )
				{
					$item->params->set( 'access-view', in_array( $item->access, $groups ) );
				}
				else
				{
					$item->params->set( 'access-view', in_array( $item->access, $groups ) && in_array( $item->category_access, $groups ) );
				}
			}

			// Some contexts may not use tags data at all, so we allow callers to disable loading tag data
			if ( $this->getState( 'load_tags', $item->params->get( 'show_tags', '1' ) ) )
			{
				$item->tags             = new TagsHelper();
				$taggedItems[$item->id] = $item;
			}

			if ( Associations::isEnabled() && $item->params->get( 'show_associations' ) )
			{
				$item->associations = AssociationHelper::displayAssociations( $item->id );
			}
		}

		// Load tags of all items.
		if ( $taggedItems )
		{
			$tagsHelper = new TagsHelper();
			$itemIds    = array_keys( $taggedItems );

			foreach ( $tagsHelper->getMultipleItemTags( 'com_crosswords.crossword', $itemIds ) as $id => $tags )
			{
				$taggedItems[$id]->tags->itemTags = $tags;
			}
		}

		return $items;
	}

	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @return  integer  The starting number of items available in the data set.
	 *
	 * @since   4.0.0
	 */
	public function getStart() {
		return $this->getState( 'list.start' );
	}


	public function set_crosswords_status( $id, $column, $status ) {

		if ( is_array( $id ) )
		{

			$id = implode( ',', $id );
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery( true );
		$query
			->update( $db->quoteName( '#__crosswords' ) )
			->set( $db->quoteName( $column ) . ' = ' . $status )
			->where( 'id in (' . $id . ')' );
		$db->setQuery( $query );

		if ( ! $db->execute() )
		{

			return false;
		}
		else
		{

			return true;
		}
	}

	public function delete_crosswords( $ids ) {

		$db    = Factory::getDbo();
		$query = $db->getQuery( true );
		$id    = implode( ',', $ids );

		$query
			->delete( '#__crosswords' )
			->where( 'id in (' . $id . ')' );

		$db->setQuery( $query );

		if ( $db->execute() )
		{

			$query = $db->getQuery( true );
			$query->delete( '#__crosswords_responses' )->where( 'cid in (' . $id . ')' );
			$db->setQuery( $query );
			$db->execute();

			$query = $db->getQuery( true );
			$query->delete( '#__crosswords_response_details' )->where( 'crossword_id in (' . $id . ')' );
			$db->setQuery( $query );
			$db->execute();

			$query = $db->getQuery( true );
			$query->delete( '#__crosswords_questions' )->where( 'cid in (' . $id . ')' );
			$db->setQuery( $query );
			$db->execute();

			// 			$query = $db->getQuery(true);
			// 			$query->update($table)

			return true;
		}

		return false;
	}

	public function get_crossword( $id ) {

		$db    = Factory::getDbo();
		$query = $db->getQuery( true );

		$query
			->select( 'a.id, a.title, a.alias, a.description, a.catid, a.created_by, a.published' )
			->from( '#__crosswords as a' )
			->where( 'a.id = ' . $id );

		$db->setQuery( $query );
		$item = $db->loadObject();

		return $item;
	}

	public function save_crossword( $crossword ) {

		$db = Factory::getDbo();

		if ( $db->updateObject( '#__crosswords', $crossword, 'id' ) )
		{

			return true;
		}

		return false;
	}

	function get_questions( $catid ) {

		$db    = Factory::getDbo();
		$query = $db->getQuery( true );

		$query->select( 'id, question' )->from( '#__crosswords_keywords' )->where( 'catid = ' . $catid . ' and published = 1' );
		$db->setQuery( $query );
		$questions = $db->loadObjectList();

		return $questions;
	}

	public function get_admin_emails( $groupId ) {

		if ( ! $groupId )
		{
			return false;
		}

		$userids = Access::getUsersByGroup( $groupId );

		if ( empty( $userids ) )
		{
			return false;
		}

		$query = 'select email from #__users where id in (' . implode( ',', $userids ) . ')';
		$this->_db->setQuery( $query );
		$users = $this->_db->loadColumn();

		return $users;
	}

}

?>