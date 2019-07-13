<?php
/**
 * @version		$Id: crosswords.php 01 2013-06-14 11:37:09Z maverick $
 * @package		corejoomla.crosswords
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2013 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

class CrosswordsControllerCrosswords extends JControllerLegacy {
	
	function __construct(){
		
		parent::__construct();
		
		$this->registerDefaultTask('get_latest_crosswords');
		$this->registerTask('list', 'redirect_to_category');
		$this->registerTask('popular', 'get_popular_crosswords');
		$this->registerTask('solved', 'get_solved_crosswords');
		$this->registerTask('mycrosswords', 'get_user_crosswords');
		$this->registerTask('myresponses', 'get_user_responses');
		$this->registerTask('view', 'get_crossword_details');
        $this->registerTask('check_result', 'check_crossword_result');
        $this->registerTask('solvequestion', 'solve_question');
        $this->registerTask('solvecrossword', 'solve_crossword');
        $this->registerTask('get_questions', 'get_questions');
        $this->registerTask('edit', 'edit_crossword');
        $this->registerTask('form', 'get_form');
        $this->registerTask('save_crossword', 'save_crossword');
        $this->registerTask('save_keyword', 'save_keyword');
	}
	
	/**
	 * @deprecated
	 * Should be removed in later versions of the component
	 */
	public function redirect_to_category(){
		
		$app = JFactory::getApplication();
		$id = $app->input->getInt('id', 0);
		
		if($id){
			
			$itemid = CJFunctions::get_active_menu_id();
			$model = $this->getModel('categories');
			$category = $model->get_migrated_category($id);
			
			if(!empty($category)){
				
				$url = JRoute::_('index.php?option='.CW_APP_NAME.'&view=crosswords&task=latest&id='.$category->migrate_id.':'.$category->alias.$itemid);
				$app->redirect($url, '', 'message', true);
			} else {
				
				$this->get_latest_crosswords();
			}
		} else {
			
			$this->get_latest_crosswords();
		}
	}
	
	public function get_latest_crosswords(){
		
		$view = $this->getView('crosswords', 'html');
		$model = $this->getModel('crosswords');
		$cat_model = $this->getModel('categories');
		
		$view->setModel($model, true);
		$view->setModel($cat_model, false);
		$view->assign('action', 'latest');
		
		$view->display();
	}

	public function get_popular_crosswords(){
	
		$view = $this->getView('crosswords', 'html');
		$model = $this->getModel('crosswords');
		$cat_model = $this->getModel('categories');
		
		$view->setModel($model, true);
		$view->setModel($cat_model, false);
		$view->assign('action', 'popular');
	
		$view->display();
	}

	public function get_solved_crosswords(){
	
		$view = $this->getView('crosswords', 'html');
		$model = $this->getModel('crosswords');
		$cat_model = $this->getModel('categories');
	
		$view->setModel($model, true);
		$view->setModel($cat_model, false);
		$view->assign('action', 'solved');
	
		$view->display();
	}

	public function get_user_crosswords(){
	
		$view = $this->getView('crosswords', 'html');
		$model = $this->getModel('crosswords');
		$cat_model = $this->getModel('categories');
	
		$view->setModel($model, true);
		$view->setModel($cat_model, false);
		$view->assign('action', 'mycrosswords');
	
		$view->display();
	}

	public function get_user_responses(){
	
		$view = $this->getView('crosswords', 'html');
		$model = $this->getModel('crosswords');
		$cat_model = $this->getModel('categories');
	
		$view->setModel($model, true);
		$view->setModel($cat_model, false);
		$view->assign('action', 'myresponses');
	
		$view->display('responses');
	}
	
	public function get_crossword_details(){

		$view = $this->getView('crossword', 'html');
		$model = $this->getModel('crossword');
		$cat_model = $this->getModel('categories');
		
		$view->setModel($model, true);
		$view->setModel($cat_model, false);
		$view->assign('action', 'view');
		
		$view->display();
	}

    public function submit_keyword() {
    	
        $user = JFactory::getUser();
        
        if(!$user->authorise('core.keywords', CW_APP_NAME)) {
        	
            echo json_encode(array('error'=>JText::_('COM_CROSSWORDS_MSG_NOT_AUTHORIZED')));
        }else {
        	
            $model = $this->getModel('crosswords');
            $keyword = $model->save_keyword();
            
            if($keyword) {
            	
            	$app = JFactory::getApplication();
            	$params = JComponentHelper::getParams(CW_APP_NAME);
            	
		    	$question_title = $app->input->getString('question-title', '');
            	
            	if($params->get('notif_admin_new_keyword', 0) == '1'){
            		
            		$itemid = CJFunctions::get_active_menu_id();
		    		$question_keyword = $app->input->getString('question-keyword', '');
		    		$question_category = $app->input->getInt('question-category', 0);
		    		
            		$body = JText::sprintf('COM_CROSSWORDS_EMAIL_ADMIN_NEW_KEYWORD_BODY', $user->username, $question_title, $question_keyword, $question_category);
            		$from = $app->getCfg('mailfrom' );
            		$fromname = $app->getCfg('fromname' );

            		$admin_emails = $model->get_admin_emails($params->get('admin_user_groups', 0));
            		
            		if(!empty($admin_emails)){
            		
            			CJFunctions::send_email($from, $fromname, $admin_emails, $sub, $body, 1);
            		}
            	}
            	
            	$question->id = $keyword;
            	$question->question = $question_title;
            	
                echo json_encode(array('message'=>JText::_('COM_CROSSWORDS_MSG_QUESTION_SUBMITTED'), 'question'=>$keyword));
            }else {
            	
                echo json_encode(array('error'=>$model->getError()));
            }
        }
        
        jexit();
    }
    
    public function solve_question(){
    	
    	$user = JFactory::getUser();
    	$app = JFactory::getApplication();
    	$params = JComponentHelper::getParams('com_crosswords');
    	 
    	if($params->get('enable_solve_question', 1) != 1)
    	{
    		echo json_encode(array('error'=>JText::_('COM_CROSSWORDS_MSG_NOT_AUTHORIZED')));
    		$app->close();
    	}

    	if(!$user->authorise('core.solve', CW_APP_NAME)) 
    	{
    		echo json_encode(array('error'=>JText::_('COM_CROSSWORDS_MSG_NOT_AUTHORIZED')));
    		$app->close();
    	}
    	
    	$app = JFactory::getApplication();
    	$id = $app->input->getInt('id', 0);
    	$axis = $app->input->getWord('axis', null);
    	$pos = $app->input->getInt('pos', 0);
    	
    	if(!$id || !$axis || !$pos)
    	{
    		echo json_encode(array('error'=>JText::_('COM_CROSSWORDS_MSG_ERROR_PROCESSING')));
    		$app->close();
    	}
    	
    	$axis = (strcmp($axis, 'x') == 0) ? 1 : 2;
    	$model = $this->getModel('crossword');
    	$keyword = $model->get_keyword($id, $axis, $pos);
    	
    	if($keyword)
    	{
    		$chars = preg_split('//u', $keyword, -1, PREG_SPLIT_NO_EMPTY);
    		echo json_encode(array('chars'=>$chars));
    	}
    	else
    	{
    		echo json_encode(array('error'=>JText::_('COM_CROSSWORDS_MSG_ERROR_PROCESSING')));
    	}
    	
    	$app->close();
    }
    
    public function solve_crossword(){
    	
    	$user = JFactory::getUser();
    	
    	if(!$user->authorise('core.solve', CW_APP_NAME)) {
    		
    		echo json_encode(array('error'=>JText::_('COM_CROSSWORDS_MSG_NOT_AUTHORIZED')));
    	}else {
    		
    		$app = JFactory::getApplication();
    		$id = $app->input->getInt('id', 0);
    		
    		if(!$id){
    			
    			echo json_encode(array('error'=>JText::_('COM_CROSSWORDS_MSG_ERROR_PROCESSING')));
    		}else{
    			
    			$model = $this->getModel('crossword');
    			$answers = $model->solve_crossword($id);
    			
    			if(!empty($answers)){
    				
    				$return = array();
    				
    				foreach($answers as $answer){
    					
    					$chars = preg_split('//u', $answer->keyword, -1, PREG_SPLIT_NO_EMPTY);
    					
    					if($answer->axis == 1){ // rows
    						
    						foreach($chars as $i=>$char){
    							
    							$celement = new stdClass();
    							$celement->id = 'x-'.$answer->row.'-y-'.($answer->column + $i);
    							$celement->value = $char;
    							
    							$return[] = $celement;
    						}
    					}else{
    						
    						foreach($chars as $i=>$char){
    							
    							$celement = new stdClass();
    							$celement->id = 'x-'.($answer->row+$i).'-y-'.$answer->column;
    							$celement->value = $char;
    							$return[] = $celement;
    						}
    					}
    				}
    				
    				echo json_encode(array('answers'=>$return));
    			}else {
    				
	    			echo json_encode(array('error'=>JText::_('COM_CROSSWORDS_MSG_ERROR_PROCESSING').'<br></br>'.$model->getError()));
	    		}
    		}
    	}
    	
    	jexit();
    }
    
    public function check_crossword_result()
    {
        $user = JFactory::getUser();
        
        if(!$user->authorise('core.solve', CW_APP_NAME)) 
        {
            echo json_encode(array('error'=>JText::_('COM_CROSSWORDS_MSG_NOT_AUTHORIZED')));
        }
        else 
        {
            $model = $this->getModel('crossword');
            $failed = $model->check_result();
            
            if($failed === false)
            {
            	echo json_encode(array('error'=>$model->getError()));
            }
            else if(empty($failed)) 
            {
            	$app = JFactory::getApplication();
            	$params = JComponentHelper::getParams(CW_APP_NAME);
            	$cid = $app->input->getInt('id', 0);
            	
            	CrosswordsHelper::awardPoints($cid, 2);
            	
            	if($params->get('stream_on_solved_crossword', 0) == '1')
            	{
            		CrosswordsHelper::streamActivity($cid, 2);
            	}
            	
            	if($params->get('notif_crossword_solved', 0) == '1')
            	{
            		$crossword = $model->getItem();
            		$from = $app->getCfg('mailfrom' );
            		$fromname = $app->getCfg('fromname' );
            		$sitename = $app->getCfg('sitename');
            		$user_name = $params->get('user_display_name', 'name');
            		$itemid = CJFunctions::get_active_menu_id();
            		$link = JHtml::link(JRoute::_('index.php?option=com_crosswords&view=crosswords&task=view&id='.$crossword->id.':'.$crossword->alias.$itemid, false, -1), $crossword->title);
            		
            		$sub = JText::sprintf('COM_CROSSWORDS_EMAIL_SOLVED_CROSSWORD_SUB', $user->$user_name);
            		$body = JText::sprintf('COM_CROSSWORDS_EMAIL_SOLVED_CROSSWORD_BODY', $crossword->user_name, $crossword->title, $user->$user_name, $link, $sitename);
            		CJFunctions::send_email($from, $fromname, $crossword->email, $sub, $body, 1);
            	}
            	
                echo json_encode(array('message'=>JText::_('COM_CROSSWORDS_MSG_CROSSWORD_SOLVED')));
            }
            else 
            {
                echo json_encode(array('failed'=>$failed));
            }
        }
        
        jexit();
    }
    
    public function get_questions(){
    	
        $user = JFactory::getUser();
        
        if(!$user->authorise('core.create', CW_APP_NAME)) {
        	
            echo json_encode(array('error'=>JText::_('COM_CROSSWORDS_MSG_NOT_AUTHORIZED')));
        }else {
        	
        	$app = JFactory::getApplication();
        	$catid = $app->input->getInt('catid', 0);
        	
        	if($catid <= 0){
        		
        		echo json_encode(array('questions'=>array()));
        	}else{
        		
	            $model = $this->getModel('crosswords');
	            $questions = $model->get_questions($catid);
	            
	            if(empty($questions)){
	            	
	            	echo json_encode(array('error'=>JText::_('COM_CROSSWORDS_MSG_NO_KEYWORDS_IN_CATEGORY')));
	            }else{
	            	
	            	echo json_encode(array('questions'=>$questions));
	            }
        	}
        }
        
        jexit();
    }
    
    public function get_form(){
    
    	$user = JFactory::getUser();
    	
    	if(!$user->authorise('core.create', CW_APP_NAME)){
    	
    		throw new Exception(JText::_('COM_CROSSWORDS_MSG_NOT_AUTHORIZED'), 403);
    	} else {
	    	
	    	$view = $this->getView('form', 'html');
	    	$model = $this->getModel('crossword');
	    	$cat_model = $this->getModel('categories');
	    
	    	$view->setModel($model, true);
	    	$view->setModel($cat_model, false);
	    	$view->assign('action', 'form');
	    
	    	$view->display();
    	}
    }
    
    public function edit_crossword(){
    	
    	$user = JFactory::getUser();
    	 
    	if(!$user->authorise('core.create', CW_APP_NAME)){
    		 
    		throw new Exception(JText::_('COM_CROSSWORDS_MSG_NOT_AUTHORIZED'), 403);
    	} else {
    	
    		$view = $this->getView('form', 'html');
    		$model = $this->getModel('crossword');
    		$cat_model = $this->getModel('categories');
    		 
    		$view->setModel($model, true);
    		$view->setModel($cat_model, false);
    		$view->assign('action', 'form');
    		 
    		$view->display();
    	}
    }
    
    public function save_crossword()
    {
    	$app = JFactory::getApplication();
    	$user = JFactory::getUser();
    	$model = $this->getModel('crossword');
    	$itemid = CJFunctions::get_active_menu_id();
    	
    	$failed = false;
    	$crossword = new stdClass();
    	$crossword->id = $app->input->post->getInt('id', 0);
    	$crossword->title = $app->input->post->getString('title', null);
    	$crossword->alias = $app->input->post->getString('alias', null);
    	$crossword->description = JComponentHelper::filterText($app->input->post->get('description', '', 'raw'));
    	$crossword->catid = $app->input->post->getString('catid', 0);
    	
    	if($crossword->id > 0) 
    	{ 
    		//update
    		if(!$user->authorise('core.edit', CW_APP_NAME.'.category.'.$crossword->catid)) 
    		{
    			throw new Exception(JText::_('COM_CROSSWORDS_MSG_NOT_AUTHORIZED'), 403);
    		}
    		
    		if(empty($crossword->title) || !$crossword->catid)
    		{
    			$app->enqueueMessage(JText::_('COM_CROSSWORDS_REQUIRED_FIELDS_MISSING'));
    			$failed = true;
    		} 
    		else if($model->update_crossword($crossword))
    		{
    			$this->setRedirect(
    					JRoute::_('index.php?option='.CW_APP_NAME.'&view=crosswords&task=view&id='.$crossword->id.':'.$crossword->alias.$itemid),
    					JText::_('COM_CROSSWORDS_MSG_CROSSWORD_SAVE_SUCCESS'));
    		} 
    		else 
    		{
	    		$app->enqueueMessage($model->getError());
	    		$failed = true;
	    	}
    	} 
    	else 
    	{
	    	$crossword->size = $app->input->post->getString('grid_size', 15);
	    	$crossword->level = $app->input->post->getString('difficulty_level', 1);
	    	$crossword->questions = $app->input->post->getArray(array('target-list'=>'array'));
	    	$crossword->questions = $crossword->questions['target-list'];
	    	
	    	if(!$user->authorise('core.create', CW_APP_NAME.'.category.'.$crossword->catid))
	    	{
	    		throw new Exception(JText::_('COM_CROSSWORDS_MSG_NOT_AUTHORIZED'), 403);
	    	}
	    	
	    	if(empty($crossword->title) || !$crossword->size || !$crossword->level || !$crossword->catid)
	    	{
	    		$app->enqueueMessage(JText::_('COM_CROSSWORDS_REQUIRED_FIELDS_MISSING'));
	    		$failed = true;
	    	} 
	    	else if($model->create_crossword($crossword))
	    	{
	    		$params = JComponentHelper::getParams(CW_APP_NAME);
	    		$user_name = $params->get('user_display_name', 'name');
	    		CrosswordsHelper::awardPoints($crossword->id, 1);
	    		 
	    		if($params->get('stream_on_new_crossword', 0) == '1')
	    		{
	    			// Stream activity
	    			CrosswordsHelper::streamActivity($crossword->id, 1);
	    		}
	    		
	    		if($params->get('notif_admin_new_crossword', 1) == 1)
	    		{
	    			$admin_emails = $model->get_admin_emails($params->get('admin_user_groups', 8));
	    			$username = $user->guest ? JText::_('LBL_GUEST') : $user->name.'('.$user->username.')';
	    			$from = $app->getCfg('mailfrom' );
	    			$fromname = $app->getCfg('fromname' );
	    			
	    			$sub = JText::sprintf('COM_CROSSWORDS_ACTIVITY_CREATED_CROSSWORD', $user->$user_name, $crossword->title);
	    			$body = JText::sprintf('COM_CROSSWORDS_EMAIL_ADMIN_NEW_CROSSWORD_BODY', $user->$user_name, $crossword->title);
	    		
	    			if(!empty($admin_emails))
	    			{
	    				CJFunctions::send_email($from, $fromname, $admin_emails, $sub, $body, 1);
	    			}
	    		}
	    		
	    		$this->setRedirect(
	    			JRoute::_('index.php?option='.CW_APP_NAME.'&view=crosswords&task=view&id='.$crossword->id.':'.$crossword->alias.$itemid),
	    			JText::_('COM_CROSSWORDS_MSG_CROSSWORD_SAVE_SUCCESS'));
	    	} 
	    	else 
	    	{
	    		$app->enqueueMessage($model->getError());
	    		$failed = true;
	    	}
    	}
    	
    	if($failed)
    	{
    		$view = $this->getView('form', 'html');
    		$cat_model = $this->getModel('categories');
    		 
    		$view->setModel($model, true);
    		$view->setModel($cat_model, false);
    		$view->assign('action', 'form');
    		$view->assignRef('item', $crossword);
    		 
    		$view->display();
    	}
    }
    
    public function save_keyword() 
    {
        $user = JFactory::getUser();
        if(!$user->authorise('core.keywords', CW_APP_NAME)) 
        {
            echo json_encode(array('error'=>JText::_('COM_CROSSWORDS_MSG_NOT_AUTHORIZED')));
        }
        else 
        {
            $model = $this->getModel('crossword');
            $id = $model->save_keyword();
            
            if($id > 0) 
            {
            	$app = JFactory::getApplication();
            	$params = JComponentHelper::getParams('com_crosswords');
            	
		    	$question_title = $app->input->post->getString('question', null);
		    	$question_keyword = $app->input->post->getString('keyword', null);
		    	$question_category = $app->input->post->getInt('category', null);
            	
            	if($params->get('notif_admin_new_keyword', 0) == 1)
            	{
            		$from = $app->get('mailfrom' );
            		$fromname = $app->get('fromname' );
            		$sub = JText::_('COM_CROSSWORDS_EMAIL_ADMIN_NEW_KEYWORD_SUB');
            		$body = JText::sprintf('COM_CROSSWORDS_EMAIL_ADMIN_NEW_KEYWORD_BODY', $user->username, $question_title, $question_keyword, $question_category);
            		
            		$crosswords_model = $this->getModel('crosswords');
            		$admin_emails = $crosswords_model->get_admin_emails($params->get('admin_user_groups', 0));
            		
            		if(!empty($admin_emails))
            		{
            			CJFunctions::send_email($from, $fromname, $admin_emails, $sub, $body, 1);
            		}
            	}
            	
            	$keyword = new stdClass();
            	$keyword->id = $id;
            	$keyword->question = $question_title;
            	
                echo json_encode(array('message'=>JText::_('COM_CROSSWORDS_MSG_QUESTION_SUBMITTED'), 'question'=>$keyword));
            }
            else 
            {
            	$error = JText::_('COM_CROSSWORDS_MSG_ERROR_PROCESSING');
            	switch ($id)
            	{
            		case -1: 
            			$error = JText::_('COM_CROSSWORDS_REQUIRED_FIELDS_MISSING');
            			break;
            			
            		case -2:
            			$error = JText::_('COM_CROSSWORDS_ERROR_DUPLICATE_KEYWORD');
            			break;
            			
            		case -3:
            			$error = JText::_('COM_CROSSWORDS_MSG_ERROR_PROCESSING');
            			break;
            	}
            	
                echo json_encode(array('error'=>$error));
            }
        }
        
        jexit();
    }
}