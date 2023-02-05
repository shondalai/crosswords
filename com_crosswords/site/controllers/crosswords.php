<?php
/**
 * @package     Crosswords
 * @subpackage  com_crosswords
 *
 * @copyright   Copyright (C) 2023 BulaSikku Technologies Private Limited.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die();

class CrosswordsControllerCrosswords extends JControllerAdmin {

	protected $text_prefix = 'COM_CROSSWORDS';
	protected $view_list = 'crosswords';

	public function __construct( $config = [] ) {
		parent::__construct( $config );
	}

	public function getModel( $name = 'Form', $prefix = 'CrosswordsModel', $config = [ 'ignore_request' => true ] ) {
		$model = parent::getModel( $name, $prefix, $config );

		return $model;
	}

	protected function getReturnPage() {
		$return = $this->input->get( 'return', null, 'base64' );

		if ( empty( $return ) || ! JUri::isInternal( base64_decode( $return ) ) )
		{
			$app   = JFactory::getApplication();
			$catid = $app->input->post->getInt( 'jform[catid]' );

			if ( $catid )
			{
				return JRoute::_( CrosswordsHelperRoute::getCategoryRoute( $catid ) );
			}
			else
			{
				return JRoute::_( 'index.php?option=com_crosswords&view=categories&id=0' );
			}
		}
		else
		{
			return base64_decode( $return );
		}
	}

	public function delete() {
		parent::delete();
		$this->setRedirect( $this->getReturnPage() );
	}

	public function publish() {
		parent::publish();

		$app   = JFactory::getApplication();
		$catid = $app->input->post->getInt( 'jform[catid]' );

		if ( $catid )
		{
			return JRoute::_( CrosswordsHelperRoute::getCategoryRoute( $catid ) );
		}
		else
		{
			return JRoute::_( 'index.php?option=com_crosswords&view=categories&id=0' );
		}
	}

}
