<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2021 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die();

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR . '/components/com_crosswords/models/crossword.php';

class CrosswordsModelForm extends CrosswordsModelCrossword {

	public $typeAlias = 'com_crosswords.crossword';

	public function __construct( $config ) {
		parent::__construct( $config );
		$this->populateState();
	}

	public function populateState() {
		$app = JFactory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt( 't_id' );
		$this->setState( 'crossword.id', $pk );

		$this->setState( 'crossword.catid', $app->input->getInt( 'catid' ) );

		$return = $app->input->get( 'return', null, 'base64' );
		$this->setState( 'return_page', base64_decode( $return ) );

		// Load the parameters.
		$params = $app->getParams();
		$this->setState( 'params', $params );

		$this->setState( 'layout', $app->input->getString( 'layout' ) );
	}

	public function getItem( $itemId = null ) {
		$itemId = (int) ( ! empty( $itemId ) ) ? $itemId : $this->getState( 'crossword.id' );

		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load( $itemId );

		// Check for a table object error.
		if ( $return === false && $table->getError() )
		{
			$this->setError( $table->getError() );

			return false;
		}

		$properties = $table->getProperties( 1 );
		$value      = Joomla\Utilities\ArrayHelper::toObject( $properties, 'JObject' );

		// Convert attrib field to Registry.
		$value->params = new JRegistry();
		if ( $value->attribs )
		{
			$value->params->loadString( $value->attribs );
		}

		// Compute selected asset permissions.
		$user   = JFactory::getUser();
		$userId = $user->id;
		$asset  = 'com_crosswords.crossword.' . $value->id;

		// Check general edit permission first.
		if ( $user->authorise( 'core.edit', $asset ) )
		{
			$value->params->set( 'access-edit', true );
		}

		// Now check if edit.own is available.
		elseif ( ! empty( $userId ) && $user->authorise( 'core.edit.own', $asset ) )
		{
			// Check for a valid user and that they are the owner.
			if ( $userId == $value->created_by )
			{
				$value->params->set( 'access-edit', true );
			}
		}

		// Check edit state permission.
		if ( $itemId )
		{
			// Existing item
			$value->params->set( 'access-change', $user->authorise( 'core.edit.state', $asset ) );
			if ( $user->authorise( 'core.attachfiles', 'com_crosswords.category.' . $value->catid ) )
			{
				$value->params->set( 'access-attach', true );
			}
		}
		else
		{
			// New item.
			$catId = (int) $this->getState( 'crossword.catid' );

			if ( $catId )
			{
				$value->params->set( 'access-change', $user->authorise( 'core.edit.state', 'com_crosswords.category.' . $catId ) );
				$value->catid = $catId;
			}
			else
			{
				$value->params->set( 'access-change', $user->authorise( 'core.edit.state', 'com_crosswords' ) );
			}
		}

		// Convert the metadata field to an array.
		$registry = new JRegistry();
		if ( $value->metadata )
		{
			$registry->loadString( $value->metadata );
		}
		$value->metadata = $registry->toArray();

		if ( $itemId )
		{
			$value->tags = new JHelperTags();
			$value->tags->getTagIds( $value->id, 'com_crosswords.crossword' );
			$value->metadata['tags'] = $value->tags;
		}

		$app          = JFactory::getApplication();
		$value->level = $app->input->post->getInt( 'difficulty_level', 1 );
		$value->size  = $app->input->post->getInt( 'grid_size', 15 );
		$value->size  = $value->size < 15 ? 15 : ( $value->size > 23 ? 23 : $value->size );

		return $value;
	}

	public function getReturnPage() {
		return base64_encode( $this->getState( 'return_page' ) );
	}

	public function save( $data ) {
		// Associations are not edited in frontend ATM so we have to inherit
		// them
		if ( JLanguageAssociations::isEnabled() && ! empty( $data['id'] ) )
		{
			if ( $associations = JLanguageAssociations::getAssociations( 'com_crosswords', '#__crosswords', 'com_crosswords.item', $data['id'] ) )
			{
				foreach ( $associations as $tag => $associated )
				{
					$associations[$tag] = (int) $associated->id;
				}

				$data['associations'] = $associations;
			}
		}

		// Check if the user is new user and have not posted enough crosswords to post external urls, this contains them
		$user = JFactory::getUser();
		if ( empty( $data['id'] ) )
		{
			$asset         = 'com_crosswords.category.' . $data['catid'];
			$data['state'] = ( $user->authorise( 'core.moderate.crossword', $asset ) && ! $user->authorise( 'core.admin', $asset ) ) ? 0 : 1;
		}

		if ( parent::save( $data ) )
		{
			return true;
		}

		return false;
	}

}
