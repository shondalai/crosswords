<?php
/**
 * @version		$Id: crosswords.php 01 2011-08-13 11:37:09Z maverick $
 * @package		CoreJoomla.Crosswords
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2011 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');

class CrosswordsModelCrossword extends JModelItem {
	
	protected $_context = 'com_crosswords.crossword';

	protected function populateState() {
		
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('crossword.id', $pk);

		$offset = $app->input->getUInt('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		// TODO: Tune these values based on other permissions.
		$user = JFactory::getUser();
		
		if ((!$user->authorise('core.edit.state', CW_APP_NAME)) &&  (!$user->authorise('core.edit', CW_APP_NAME))){
			
			$this->setState('filter.published', 1);
		}
	}

	public function &getItem($pk = null) {
		
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('crossword.id');
		
		if(empty($pk)){
			
			$item = new stdClass();
			
			$item->id = $item->catid = $item->published = $item->rows = $item->columns = 0;
			$item->title = $item->alias = $item->description = '';
			
			return $item;
		}

		if ($this->_item === null) {
				
			$this->_item = array();
		}
		
		if (!isset($this->_item[$pk])) {

			try {
				
				$db = $this->getDbo();
				$query = $db->getQuery(true);

				$query->select(
					$this->getState(
						'item.select', 
						'a.id, a.title, a.alias, a.description, a.questions, a.rows, a.columns, a.solved, a.hits,'.
						'CASE WHEN badcats.id is null THEN a.published ELSE 0 END AS published, a.catid, a.created, a.created_by, a.hits'
					)
				);
				
				$query->from('#__crosswords AS a');

				// Join on category table.
				$query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access');
				$query->join('LEFT', '#__categories AS c on c.id = a.catid');

				// Join on user table.
				$query->select('u.name AS user_name, u.email');
				$query->join('LEFT', '#__users AS u on u.id = a.created_by');
		
				// Join over the categories to get parent category titles
				$query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias');
				$query->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

				$query->where('a.id = ' . (int) $pk);

				// Join to check for category published state in parent categories up the tree
				// If all categories are published, badcats.id will be null, and we just use the article state
				$subQuery = $db->getQuery(true);
				$subquery = ' (SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ';
				$subquery .= 'ON cat.lft BETWEEN parent.lft AND parent.rgt ';
				$subquery .= 'WHERE parent.extension = ' . $db->quote('com_content');
				$subquery .= ' AND parent.published <= 0 GROUP BY cat.id)';
				$query->join('LEFT OUTER', $subquery . ' AS badcats ON badcats.id = c.id');

				// Filter by published state.
				$published = $this->getState('filter.published');

				if (is_numeric($published)) {
					
					$query->where('(a.published = ' . (int) $published.')');
				}

				$db->setQuery($query);

				$data = $db->loadObject();

				if ($error = $db->getErrorMsg()) {
					
					throw new Exception($error);
				}

				if (empty($data)) {
					
					return JError::raiseError(404, JText::_('COM_CROSSWORDS_ERROR_CROSSWORD_NOT_FOUND'));
				}

				// Check for published state if filter set.
				if ((is_numeric($published)) && ($data->published != $published)) {
					
					return JError::raiseError(404, JText::_('COM_CROSSWORDS_ERROR_CROSSWORD_NOT_FOUND'));
				}

				// Compute selected asset permissions.
				$user	= JFactory::getUser();
				$data->params = clone $this->getState('params');

				// Technically guest could edit an article, but lets not check that to improve performance a little.
				if (!$user->get('guest')) {
					
					$userId	= $user->get('id');

					// Check general edit permission first.
					if ($user->authorise('core.edit', CW_APP_NAME)) {
						
						$data->params->set('access-edit', true);
					}
					// Now check if edit.own is available.
					elseif (!empty($userId) && $user->authorise('core.edit.own', CW_APP_NAME)) {
						
						// Check for a valid user and that they are the owner.
						if ($userId == $data->created_by) {
							
							$data->params->set('access-edit', true);
						}
					}
				}

				$this->_item[$pk] = $data;
			}
			catch (JException $e)
			{
				if ($e->getCode() == 404) {
					// Need to go thru the error handler to allow Redirect to work.
					JError::raiseError(404, $e->getMessage());
				}
				else {
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}

	public function hit($pk = 0){
		
		$app = JFactory::getApplication();
		$hitcount = $app->input->getInt('hitcount', 1);
		
		if ($hitcount) {
			
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('crossword.id');
			$db = $this->getDbo();
			
			$db->setQuery('UPDATE #__crosswords SET hits = hits + 1 WHERE id = '.(int) $pk);
			
			if (!$db->execute()) {
				
				$this->setError($db->getErrorMsg());
				return false;
			}
		}
		
		return true;
	}
	
	public function populate_crossword_details(&$crossword){

		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$params = JComponentHelper::getParams(CW_APP_NAME);
		
		$crossword->response_id = 0;
		$crossword->solved = 0;
		
		if(!$user->guest){
			
			$query = $db->getQuery(true);
			
			$query
				->select('id, solved')
				->from('#__crosswords_responses')
				->where('cid = '.$crossword->id.' and created_by = '.$user->id);

			$db->setQuery($query);
			$result = $db->loadObject();
			
			$crossword->solved = isset($result->solved) ? $result->solved : 0;
			$crossword->response_id = isset($result->id) ? $result->id : 0;
		}
		
		// Get questions
		$query = $db->getQuery(true);
		
		$query
			->select('q.id, k.keyword, r.answer, k.question, q.row, q.column, q.axis, q.position')
			->from('#__crosswords_questions as q')
			->join('left', '#__crosswords_keywords as k on q.keyid = k.id')
			->join('left', '#__crosswords_response_details as r on q.id = r.question_id and r.response_id = '.$crossword->response_id)
			->where('q.cid = '.$crossword->id)
			->order('q.position asc');
		
		$db->setQuery($query);
		$questions = $db->loadObjectList();
		
		if(empty($questions)){
			
			return false;
		}
		
		// Form grid cells
		$cells = array();
		
		for($x=-1; $x<=$crossword->rows; $x++){
			 
			for($y=-1; $y<=$crossword->columns; $y++){
		
				$cells[$x][$y] = new stdClass();
		
				$cells[$x][$y]->valid = false;
				$cells[$x][$y]->value = '';
				$cells[$x][$y]->claz = array();
				$cells[$x][$y]->topclaz = '';
				$cells[$x][$y]->bottomclaz = '';
				$cells[$x][$y]->id = 'x-'.$x.'-y-'.$y;
			}
		}
		
		foreach ($questions as $q){
			 
			$kchars = preg_split('//u', $q->keyword, -1, PREG_SPLIT_NO_EMPTY);
			$chars = preg_split('//u', $q->answer, -1, PREG_SPLIT_NO_EMPTY);
			 
			if($q->axis == 1){ //row
		
				foreach ($kchars as $i=>$char){
		
					$cells[$q->row][$q->column+$i]->value = (!empty($chars[$i]) ? $chars[$i] : '');
					$cells[$q->row][$q->column+$i]->valid = true;
					$cells[$q->row][$q->column+$i]->claz[] = 'axis-x-pos-'.$q->position;
						
					if($i == 0){
		
						$cells[$q->row][$q->column+$i]->bottomclaz = 'numcell start-pos-across-'.$q->position;
					}
						
					if(count($cells[$q->row][$q->column+$i]->claz) > 1){
		
						$cells[$q->row][$q->column+$i]->claz[] = 'cross-cell';
					}
				}
			}else{
		
				foreach ($kchars as $i=>$char){
		
					$cells[$q->row+$i][$q->column]->value = (!empty($chars[$i]) ? $chars[$i] : '');;
					$cells[$q->row+$i][$q->column]->valid = true;
					$cells[$q->row+$i][$q->column]->claz[] = 'axis-y-pos-'.$q->position;
						
					if($i == 0){
		
						$cells[$q->row+$i][$q->column]->bottomclaz = ' numcell start-pos-down-'.$q->position;
					}
						
					if(count($cells[$q->row+$i][$q->column]->claz) > 1){
							
						$cells[$q->row+$i][$q->column]->claz[] = 'cross-cell';
					}
				}
			}
		}
		
		$query = $db->getQuery(true);
		$query->select('count(*)')->from('#__crosswords_responses')->where('cid = '.$crossword->id.' and solved = 1');
		$db->setQuery($query);
		
		$user_count = (int)$db->loadResult();
		
		$crossword->questions = $questions;
		$crossword->cells = $cells;
		$crossword->users_solved = $this->get_solved_users($crossword->id, $params);
		$crossword->user_count = $user_count - 15;
		
		return $crossword;
	}

	public function get_solved_users($id, $params){
	
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
	
		$query
			->select('a.created_by as id, a.created')
			->select('u.'.$params->get('user_display_name', 'name').' as user_name, u.email')
			->from('#__crosswords_responses as a')
			->join('left', '#__users as u on a.created_by = u.id')
			->where('a.cid = '.$id.' and a.created_by > 0 and a.solved > 0')
			->order('a.created desc');
	
		$db->setQuery($query, 0, 30);
		$list = $db->loadObjectList();
		
		return $list;
	}
	
	function get_keyword($cid, $axis, $position){

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$query
			->select('keyword')
			->from('#__crosswords_keywords as k')
			->join('inner', '#__crosswords_questions as q on q.keyid = k.id')
			->where('q.cid = '.$cid.' and q.axis='.$axis.' and q.position='.$position);
		
		$db->setQuery($query);
		$keyword = $db->loadResult();
		 
		return $keyword;
	}
	
	public function check_result(){

		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		
		$cid = $app->input->getInt('id', 0);
		$query = $db->getQuery(true);
		
		$query
			->select('id, rows, columns')
			->from('#__crosswords')
			->where('id = '.$cid);
		 
		$db->setQuery($query);
		$crossword = $db->loadObject();
		 
		// Get questions
		$query = $db->getQuery(true);
		
		$query
			->select('q.id, k.keyword, q.row, q.column, q.axis, q.position')
			->from('#__crosswords_questions as q')
			->join('left', '#__crosswords_keywords as k on q.keyid=k.id')
			->where('q.cid='.$cid);
		 
		$db->setQuery($query);
		$questions = $db->loadObjectList();
		 
		if(empty($questions)){
		
			$this->setError(JText::_('MSG_ERROR_PROCESSING').' Error: 10021');
		
			return false;
		}
		 
		// Form grid cells
		$cells = array();
		 
		for($x=0; $x<$crossword->rows; $x++){
		
			$cells[$x] = array();
		
			for($y=0; $y<$crossword->columns; $y++){
				 
				$cells[$x][$y] = new stdClass();
				$cells[$x][$y]->letter = null;
			}
		}
		 
		$failed = array();
		$answers = array();
		 
		foreach ($questions as $question){
		
			$answer = '';
			$flag = true;
			$len = mb_strlen($question->keyword, 'UTF-8');
		
			if($question->axis == 1){
				 
				for($y=0; $y < $len; $y++){
		
					$cellnum = $question->column + $y;
					$letter = $app->input->post->getString('cell_'.$cellnum.'_'.$question->row, '');
					$letter = ((empty($letter) || mb_strlen($letter, 'UTF-8') != 1) ? ' ' : $letter);
					$answer = $answer.$letter;
				}
				 
				if(mb_stristr($answer, $question->keyword, false, 'UTF-8') === false){
						
					$failed[] = 'axis-x-pos-' . $question->position;
					$flag = false;
				}
			}else{
				 
				for($x=0; $x < $len; $x++){
		
					$cellnum = $question->row + $x;
					$letter = $app->input->post->getString('cell_'.$question->column.'_'.$cellnum, '');
					$letter = ((empty($letter) || mb_strlen($letter, 'UTF-8') != 1) ? ' ' : $letter);
					$answer = $answer.$letter;
				}
				 
				if(mb_stristr($answer, $question->keyword, false, 'UTF-8') === false){
		
					$failed[] = 'axis-y-pos-' . $question->position;
					$flag = false;
				}
			}
		
			$response = new stdClass();
			$response->answer = $answer;
			$response->question_id = $question->id;
			$response->valid = (int)$flag;
			$answers[] = $response;
		}
		 
		if(!$user->guest){
		
			$query = $db->getQuery(true);
			
			$query
				->select('id')
				->from('#__crosswords_responses')
				->where('cid = '.$cid.' and created_by = '.$user->id);
			
			$db->setQuery($query);
			$response_id = $db->loadResult();
		
			if($response_id){
				
				$query = $db->getQuery(true);
				
				$query
					->delete('#__crosswords_response_details')
					->where('response_id = '.$response_id);
				
				$db->setQuery($query);
				 
				if(!$db->execute()){
		
					$this->setError(JText::_('COM_CROSSWORDS_MSG_ERROR_PROCESSING').' Error: 10022');
					return false;
				}
				 
				if(count($failed) == 0){
					
					$query = $db->getQuery(true);
					
					$query
						->update('#__crosswords_responses')
						->set('solved = 1')
						->where('id = '.$response_id);

					$db->setQuery($query);
					$db->execute();
				}
			}else{
				
				$query = $db->getQuery(true);
				
				$query
					->insert('#__crosswords_responses')
					->columns('cid, created_by, created, solved')
					->values($cid.','.$user->id.','.$db->q(JFactory::getDate()->toSql()).','.(( count($failed) > 0 ) ? '0' : '1'));
				 
				$db->setQuery($query);
				 
				if(!$db->execute()){
		
					$this->setError(JText::_('COM_CROSSWORDS_MSG_ERROR_PROCESSING').' Error: 10023');
					return false;
				}
				 
				$response_id = $db->insertid();
			}
		
			if(count($failed) == 0){
				
				$query = $db->getQuery(true);
				
				$query
					->update('#__crosswords')
					->set('solved = solved + 1')
					->where('id = '.$cid);
				
				$db->setQuery($query);
				$db->execute();
			}
			
			$query = $db->getQuery(true);
			
			$query
				->insert('#__crosswords_response_details')
				->columns('crossword_id, response_id, question_id, answer, valid');

			foreach ($answers as $answer){
				 
				$query->values($cid.','.$response_id.','.$answer->question_id.','.$db->q($answer->answer).','.$answer->valid);
			}
		
			$db->setQuery($query);
		
			if(!$db->execute()){
				 
				$this->setError(JText::_('COM_CROSSWORDS_MSG_ERROR_PROCESSING').' Error: 10024');
				return false;
			}
		}
		 
		return $failed;
	}
    
    public function solve_crossword($id){
    	
    	$user = JFactory::getUser();
    	$db = JFactory::getDbo();
    	$response_id = 0;
    	$params = JComponentHelper::getParams('com_crosswords');
    	$minpct = (int) $params->get('min_solve_pct', 75);
    	
    	if(!$minpct)
    	{
    		$minpct = 100;
    	}
    	
    	if(!$user->guest){
    		
	    	$query = $db->getQuery(true);
	    	$query
	    		->select('id')
	    		->from('#__crosswords_responses')
	    		->where('cid = '.$id.' and created_by = '.$user->id);
	    	
	    	$db->setQuery($query);
	    	
	    	$response_id = $db->loadResult();
    	}
    	
    	$continue = false;
    	
    	if($response_id){
    		
    		$query = $db->getQuery(true);
    		$query
    			->select('count(*) as valid')
    			->from('#__crosswords_response_details as a')
    			->where('a.response_id='.$response_id.' and a.valid=1');
    		
    		$db->setQuery($query);
    		
    		$valid = intval($db->loadResult());
    		
    		if($valid > 0){
    			
    			$query = $db->getQuery(true);
    			$query
    				->select('questions')
    				->from('#__crosswords')
    				->where('id = '.$id);
    			
    			$db->setQuery($query);
    			$total = (int) $db->loadResult();
    			
    			if($valid >= ($total * $minpct/100)) {
    				
    				$continue = true;
    			}
    		}
    		
    		if($continue){
    			
    			$query = $db->getQuery(true);
    			$query
    				->delete('#__crosswords_response_details')
    				->where('response_id = '.$response_id);
    			
    			$db->setQuery($query);
    			
    			if(!$db->execute()){
    				
    				$this->setError(JText::_('COM_CROSSWORDS_MSG_ERROR_PROCESSING').' Error: 100122');
    				return false;
    			}
    			
    			$query = $db->getQuery(true);
    			$query
    				->update('#__crosswords_responses')
    				->set('solved = 1')
    				->where('id = '.$response_id);
    			
    			$db->setQuery($query);
    			$db->execute();
    		}
    	}else{
    		
    		$query = $db->getQuery(true);
    		$query
    			->insert('#__crosswords_responses')
    			->columns('cid, created_by, created, solved')
    			->values($id.','.$user->id.','.$db->q(JFactory::getDate()->toSql()).',0');
    		
    		$db->setQuery($query);
    		
    		if(!$db->execute()){
    			
    			$this->setError(JText::_('COM_CROSSWORDS_MSG_ERROR_PROCESSING').' Error: 10123');
    			return false;
    		}
    		
    		$response_id = $db->insertid();
    	}
    	
    	if($continue){
    		
    		$query = $db->getQuery(true);
    		$query
    			->select('a.id, a.axis, a.row, a.column, a.position, k.keyword')
    			->from('#__crosswords_questions a')
    			->join('left', '#__crosswords_keywords as k on a.keyid = k.id')
    			->where('a.cid = '.$id);
    		
    		$db->setQuery($query);
    		$questions = $db->loadObjectList();
    		
    		$query = $db->getQuery(true);
    		
    		$query
    			->insert('#__crosswords_response_details')
    			->columns('crossword_id, response_id, question_id, answer, valid');
	    		
	    	foreach ($questions as $question){
	    			
	    		$query->values($id.','.$response_id.','.$question->id.','.$db->q($question->keyword).',1'); 
	    	}
	    		
	    	$db->setQuery($query);
	    		
	    	if($db->execute()){
	    			
	    		$query = $db->getQuery(true);
	    			
	    		$query
	    			->update('#__crosswords')
	    			->set('solved = solved + 1')
	    			->where('id = '.$id);
	    			
	    		$db->setQuery($query);
	    		$db->execute();
	    			
	    		return $questions;
	    	}
    	}else{
    			
    		$this->setError(JText::sprintf('COM_CROSSWORDS_MSG_CROSSWORD_MINIMUM_SOLVE_REQUIRED', $minpct));
    		return false;
    	}
    	
    	return false;
    }
    
    public function update_crossword($crossword){
    	 
    	 $db = JFactory::getDbo();
    	 
    	 try{
    	 
    	 	$db->updateObject('#__crosswords', $crossword, 'id');
    	 	return true;
    	 } catch (Exception $e){
    	 	
    	 	$this->setError(JText::_('MSG_ERROR_PROCESSING').'| Code: 1');
    	 	return false;
    	 }
    }
    
    public function create_crossword(&$crossword){
    	
    	$app = JFactory::getApplication();
    	$user = JFactory::getUser();
    	$params = JComponentHelper::getParams(CW_APP_NAME);
    	$db = JFactory::getDbo();
    	
    	if($crossword->id > 0) { //update
    		
    		$object  = new stdClass();
    		
    		$object->title = $crossword->title;
    		$object->description = $crossword->description;
    		$object->catid = $crossword->catid;
    		
    		try{
    		
    			$db->updateObject('#__crosswords', $object, 'id');
    		} catch (Exception $e) {
    			
    			$this->setError(JText::_('MSG_ERROR_PROCESSING').'| Code: 1');
    			return false;
    		}
    	} else {
	    	
    		/********************** BUILD CROSSWORD ***********************/
    		$crossword->size = $crossword->size < 15 ? 15 : ($crossword->size > 23 ? 23 : $crossword->size);
	    	$max_words = ($crossword->level == 1) ? $crossword->size - 10 : (($crossword->level == 2) ? $crossword->size - 5 : $crossword->size);
    		$word_list = null;
    		$try = 0;
    		
    		do {
    		
    			$word_list = $this->load_words($crossword->catid, $crossword->questions, $max_words);
    			$try++;
    		} while($try < 5 && empty($word_list));
    		
    		if(empty($word_list)){
    			
    			$this->setError(JText::_('MSG_ERROR_PROCESSING').'| Code: 2');
    			return false;
    		}
    		
	     	require_once JPATH_COMPONENT.DS.'helpers'.DS.'crossword_weaver.php';
	     	$weaver = new Crossword($crossword->size, $word_list);
	     	$weaver->build_crossword();
	     	
			if(count($weaver->across) <= 0 || count($weaver->down) <= 0){
				
				$this->setError(JText::_('MSG_ERROR_PROCESSING').'| Code: 3');
				return false;
			}
			/********************** BUILD CROSSWORD ***********************/
				
			$object = new stdClass();
			$crossword->alias = JFilterOutput::stringURLUnicodeSlug($crossword->title);
			
			$object->title = $crossword->title;
			$object->alias = $crossword->alias;
			$object->description = $crossword->description;
			$object->catid = $crossword->catid;
			$object->created_by = $user->id;
			$object->created = JFactory::getDate()->toSql();
			$object->published = 1;
			$object->questions = $max_words;
			$object->rows = $crossword->size;
			$object->columns = $crossword->size;
			
			try {

				$db->insertObject('#__crosswords', $object);
			} catch (Exception $e){
				
				$this->setError(JText::_('MSG_ERROR_PROCESSING').'| Code: 4');
				return false;
			}
			
			$crossword->id = $db->insertid();
        	
        	if($crossword->id){
        		
        		$questions = array();
        		$query = $db->getQuery(true);
        		
        		$query
        			->insert('#__crosswords_questions')
        			->columns($db->qn('cid').','.$db->qn('keyid').','.$db->qn('row').','.$db->qn('column').','.$db->qn('axis').','.$db->qn('position'));
        		
	        	foreach($weaver->across as $number=>$word){
	        		
	        		$query->values($crossword->id.','.$word[2]->id.','.$word[0].','.($word[1] - mb_strlen($word[2]->keyword, 'UTF-8')).',1,'.($number+1));
	        	}
	        	
        		foreach($weaver->down as $number=>$word){
        			
        			$query->values($crossword->id.','.$word[2]->id.','.($word[0] - mb_strlen($word[2]->keyword, 'UTF-8')).','.$word[1].',2,'.($number+1));
	        	}
	        	
	        	try {
	        		
	        		$db->setQuery($query);
	        		$db->execute();
	        	} catch (Exception $e){
	        		
	        		$this->setError(JText::_('MSG_ERROR_PROCESSING').'| Code: 5');
	        		return false;
	        	}
        	}else{
        		
        		$this->setError(JText::_('MSG_ERROR_PROCESSING').'| Code: 6');
        		return false;
        	}
    	}
    	
    	return true;
    }
    
    private function load_words($catid, $custom_list, $max_words){
    	
    	$db = JFactory::getDbo();
    	
    	try{
	    	
	    	if(!empty($custom_list)){
	    		
	    		JArrayHelper::toInteger($custom_list);
	    		$query = $db->getQuery(true);
	    		
	    		$query
	    			->select('id, keyword')
	    			->from('#__crosswords_keywords')
	    			->where('id in ('.implode(',', $custom_list).') and published=1');
	    		
	    		$db->setQuery($query, 0, $max_words);
	    		$wordlist = $db->loadObjectList();
	    		
	    		return count($wordlist) == $max_words ? $wordlist : false;
	    	} else {
		    	
		    	$query = $db->getQuery(true);
		    	$query->select('min(id) as min_value, max(id) as max_value')->from('#__crosswords_keywords');
		    	$db->setQuery($query);
		    	
	    		$result = $db->loadObject();
	    		 
	    		if(!$result->min_value || !$result->max_value){
	    	
	    			$this->setError(JText::_('MSG_ERROR_PROCESSING').'| Code: 7');
	    			return false;
	    		}
	    		 
	    		$random_value = rand($result->min_value, $result->max_value);
	    		$query = $db->getQuery(true);
	    		$conditions = array(
	    				'id >= '.$random_value,
	    				'catid = '.$catid,
	    				'published = 1'
	    				);
	    		
	    		$query
	    			->select('id, keyword')
	    			->from('#__crosswords_keywords')
	    			->where($conditions);
	    		
	    		$db->setQuery($query, 0, 100);
	    		$wordlist = $db->loadObjectList();
	    		$return = array();
	    		
	    		if(count($wordlist) >= $max_words){
	    			
	    			$keys = array_rand($wordlist, $max_words);
	    			
	    			foreach ($keys as $key){
	    				
	    				$return[] = $wordlist[$key];
	    			}
	    		}
	    		
	    		return (count($return) == $max_words) ? $return : false;
	    	}
    	} catch (Exception $e){
    		
    		$this->setError(JText::_('MSG_ERROR_PROCESSING').'| Code: 8');
    		return  false;
    	}
    	
    	return false;
    }
    
    function save_keyword()
    {
    	$app = JFactory::getApplication();
    	$user = JFactory::getUser();
    	$db = JFactory::getDbo();
    	
    	$question_title = trim($app->input->post->getString('question', null));
    	$question_keyword = trim($app->input->post->getString('keyword', null));
    	$question_category = $app->input->post->getString('category', null);
    	
    	if(empty($question_title) || empty($question_keyword) || !$question_category)
    	{
    		return -1;
    	}
    	
    	try
    	{
	    	$query = $db->getQuery(true)
	    		->select('count(*)')
	    		->from('#__crosswords_keywords')
	    		->where('keyword = '.$db->q($question_keyword))
	    		->where('catid = '.$question_category);
	    	
	    	$db->setQuery($query);
	    	$count = (int) $db->loadResult();
	    	
	    	if($count)
	    	{
	    		return -2;
	    	}
	    	
	    	$query = $db->getQuery(true);
	    	$query
	    		->insert('#__crosswords_keywords')
	    		->columns('question, keyword, created_by, created, catid')
	    		->values(
	    				$db->q($question_title).','.
	    				$db->q(mb_strtoupper($question_keyword, 'UTF-8')).','.
	    				$user->id.','.
	    				$db->q(JFactory::getDate()->toSql()).','.
	    				$question_category);
	    	
	    	$db->setQuery($query);
	    	
	    	if($db->execute())
	    	{
	    		return $db->insertid();
	    	}
    	}
    	catch (Exception $e)
    	{
    		return -3;
    	}
    	
    	return false;
    }

    public function get_admin_emails($groupId){
    
    	if(!$groupId) return false;
    
    	$userids = JAccess::getUsersByGroup($groupId);
    
    	if(empty($userids)) return false;
    
    	$query = 'select email from #__users where id in ('.implode(',', $userids).')';
    	$this->_db->setQuery($query);
    	$users = $this->_db->loadColumn();
    
    	return $users;
    }
}
