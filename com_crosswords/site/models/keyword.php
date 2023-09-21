<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2023 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\IpHelper;

require_once JPATH_ADMINISTRATOR . '/components/com_crosswords/models/keyword.php';

/**
 * Crosswords Component Keyword Form Model
 *
 * @since  4.0.0
 */
class CrosswordsModelKeywordForm extends CrosswordsModelKeyword {

	/**
	 * Model context string.
	 *
	 * @var        string
	 */
	protected $_context = 'com_crosswords.keyword';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 * @since   1.6
	 *
	 */
	protected function populateState() {
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt( 'id' );
		$this->setState( 'keyword.id', $pk );

		$offset = $app->input->getUint( 'limitstart' );
		$this->setState( 'list.offset', $offset );

		// Load the parameters.
		$params = $app->getParams();
		$this->setState( 'params', $params );

		$user = Factory::getUser();

		// If $pk is set then authorise on complete asset, else on component only
		$asset = empty( $pk ) ? 'com_content' : 'com_content.keyword.' . $pk;

		if ( ( ! $user->authorise( 'core.edit.state', $asset ) ) && ( ! $user->authorise( 'core.edit', $asset ) ) )
		{
			$this->setState( 'filter.published', 1 );
			$this->setState( 'filter.archived', 2 );
		}

		$this->setState( 'filter.language', Multilanguage::isEnabled() );
	}

	/**
	 * Method to get keyword data.
	 *
	 * @param   integer  $pk  The id of the keyword.
	 *
	 * @return  object|boolean  Menu item data object on success, boolean false
	 *
	 * @since 4.0.0
	 */
	public function getItem( $pk = null ) {
		$user = Factory::getUser();

		$pk = (int) ( $pk ?: $this->getState( 'keyword.id' ) );

		if ( $this->_item === null )
		{
			$this->_item = [];
		}

		if ( ! isset( $this->_item[$pk] ) )
		{
			try
			{
				$db    = $this->getDatabase();
				$query = $db->getQuery( true );

				$query->select(
					$this->getState(
						'item.select',
						[
							$db->quoteName( 'a.id' ),
							$db->quoteName( 'a.question' ),
							$db->quoteName( 'a.keyword' ),
							$db->quoteName( 'a.catid' ),
							$db->quoteName( 'a.created' ),
							$db->quoteName( 'a.created_by' ),
							$db->quoteName( 'a.modified' ),
							$db->quoteName( 'a.modified_by' ),
							$db->quoteName( 'a.checked_out' ),
							$db->quoteName( 'a.checked_out_time' ),
							$db->quoteName( 'a.published' ),
							$db->quoteName( 'a.ordering' ),
							$db->quoteName( 'a.access' ),
							$db->quoteName( 'a.language' ),
						]
					)
				)
				      ->select(
					      [
						      $db->quoteName( 'c.title', 'category_title' ),
						      $db->quoteName( 'c.alias', 'category_alias' ),
						      $db->quoteName( 'c.access', 'category_access' ),
						      $db->quoteName( 'c.language', 'category_language' ),
						      $db->quoteName( 'u.name', 'author' ),
						      $db->quoteName( 'parent.title', 'parent_title' ),
						      $db->quoteName( 'parent.id', 'parent_id' ),
						      $db->quoteName( 'parent.path', 'parent_route' ),
						      $db->quoteName( 'parent.alias', 'parent_alias' ),
						      $db->quoteName( 'parent.language', 'parent_language' ),
					      ]
				      )
				      ->from( $db->quoteName( '#__crosswords_keywords', 'a' ) )
				      ->join( 'INNER', $db->quoteName( '#__categories', 'c' ), $db->quoteName( 'c.id' ) . ' = ' . $db->quoteName( 'a.catid' ) )
				      ->join( 'LEFT', $db->quoteName( '#__users', 'u' ), $db->quoteName( 'u.id' ) . ' = ' . $db->quoteName( 'a.created_by' ) )
				      ->join( 'LEFT', $db->quoteName( '#__categories', 'parent' ), $db->quoteName( 'parent.id' ) . ' = ' . $db->quoteName( 'c.parent_id' ) )
				      ->where( [ $db->quoteName( 'a.id' ) . ' = :pk', $db->quoteName( 'c.published' ) . ' > 0' ] )
				      ->bind( ':pk', $pk, ParameterType::INTEGER );

				// Filter by language
				if ( $this->getState( 'filter.language' ) )
				{
					$query->whereIn( $db->quoteName( 'a.language' ), [ Factory::getLanguage()->getTag(), '*' ], ParameterType::STRING );
				}

				// Filter by published state.
				$published = $this->getState( 'filter.published' );
				$archived  = $this->getState( 'filter.archived' );

				if ( is_numeric( $published ) )
				{
					$query->whereIn( $db->quoteName( 'a.published' ), [ (int) $published, (int) $archived ] );
				}

				$db->setQuery( $query );

				$data = $db->loadObject();

				if ( empty( $data ) )
				{
					throw new Exception( Text::_( 'COM_CROSSWORDS_ERROR_KEYWORD_NOT_FOUND' ), 404 );
				}

				// Check for published state if filter set.
				if ( ( is_numeric( $published ) || is_numeric( $archived ) ) && ( $data->published != $published && $data->published != $archived ) )
				{
					throw new Exception( Text::_( 'COM_CROSSWORDS_ERROR_KEYWORD_NOT_FOUND' ), 404 );
				}

				// Convert parameter fields to objects.
				$registry = new Registry( $data->attribs );

				$data->params = clone $this->getState( 'params' );
				$data->params->merge( $registry );

				$data->metadata = new Registry( $data->metadata );

				// Technically guest could edit an keyword, but lets not check that to improve performance a little.
				if ( ! $user->get( 'guest' ) )
				{
					$userId = $user->get( 'id' );
					$asset  = 'com_content.keyword.' . $data->id;

					// Check general edit permission first.
					if ( $user->authorise( 'core.edit', $asset ) )
					{
						$data->params->set( 'access-edit', true );
					}
					elseif ( ! empty( $userId ) && $user->authorise( 'core.edit.own', $asset ) )
					{
						// Now check if edit.own is available.
						// Check for a valid user and that they are the owner.
						if ( $userId == $data->created_by )
						{
							$data->params->set( 'access-edit', true );
						}
					}
				}

				// Compute view access permissions.
				if ( $access = $this->getState( 'filter.access' ) )
				{
					// If the access filter has been set, we already know this user can view.
					$data->params->set( 'access-view', true );
				}
				else
				{
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$user   = Factory::getUser();
					$groups = $user->getAuthorisedViewLevels();

					if ( $data->catid == 0 || $data->category_access === null )
					{
						$data->params->set( 'access-view', in_array( $data->access, $groups ) );
					}
					else
					{
						$data->params->set( 'access-view', in_array( $data->access, $groups ) && in_array( $data->category_access, $groups ) );
					}
				}

				$this->_item[$pk] = $data;
			}
			catch ( Exception $e )
			{
				if ( $e->getCode() == 404 )
				{
					// Need to go through the error handler to allow Redirect to work.
					throw $e;
				}
				else
				{
					$this->setError( $e );
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}

	/**
	 * Cleans the cache of com_content and content modules
	 *
	 * @param   string  $group  The cache group
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function cleanCache( $group = null, $clientId = 0 ) {
		parent::cleanCache( 'com_crosswords' );
	}

}
