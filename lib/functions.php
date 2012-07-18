<?php

//
//	called when a comment is made, checks if object is moderated
//	if so adds to moderation list
//
function moderated_comments_check($event, $object_type, $obj){
	
	if($obj->name == "generic_comment" && moderated_comments_is_moderated($obj->entity_guid) && !elgg_is_logged_in()){
		$entity = get_entity($obj->entity_guid);
		if($entity->owner_guid != elgg_get_logged_in_user_guid()){
			moderated_comments_add_to_review_list($obj);
			system_message(elgg_echo('moderated_comments:comment_success'));
		}
	}
}


//
//	this function adds the new comment id to the list that needs to be checked
//
function moderated_comments_add_to_review_list($obj){

	$entity = get_entity($obj->entity_guid);
	
	$review_array = explode(',', $entity->unmoderated_comments);

	// add new comment id to array
	if(!is_array($review_array)){
		$review_array = array();
	}
	
	if(!in_array($obj->id, $review_array)){
		$review_array[] = $obj->id;
	}

	// save the new array
	moderated_comments_save_array($review_array, $entity);
}

//
//	this function saves the array as a list of ids separated by commas
//
function moderated_comments_save_array($review_array, $entity){
	$context = elgg_get_context();
	elgg_set_context('moderated_comments_permissions');
	sort($review_array);
	//convert new array back into a list
	$review_list = implode(',', $review_array);

	//save the list
	$entity->unmoderated_comments = $review_list;
	
	elgg_set_context($context);
}


//
//	this function returns true if the entity is being moderated
//
function moderated_comments_is_moderated($id){
	$entity = get_entity($id);
	
	if(!is_object($entity)){
		return false;
	}
	
	if($entity->is_moderated || $entity->access_id == ACCESS_PUBLIC){
		return true;
	}
	
	return false;
}


//
// This function checks each object on creation (called by event handler)
//	if object has public access, set to moderated
//
function moderated_comments_entity_create($event, $object_type, $object){
	if($object_type == "object"){
		if($object->access_id == ACCESS_PUBLIC){
			$object->is_moderated = true;
		}
		else{
			$object->is_moderated = false;
		}
	}
}



//
//	This function checks if the entity is being moderated, if so we need to count
// and return the number of APPROVED comments, not total comments
// called by commments:count plugin hook
//
function moderated_comments_comment_count($hook, $type, $returnvalue, $params){
	if(moderated_comments_is_moderated($params['entity']->guid)){
		// get array of total comments
		$comments = $params['entity']->getAnnotations('generic_comment');
		// get array of comments awaiting review
		$unreviewed = explode(',', $params['entity']->unmoderated_comments);
		
		$count = 0;
		for($i=0; $i<count($comments); $i++){
			$id = $comments[$i]->id;	
			if(!empty($id)){		
				if(!in_array($id, $unreviewed)){
					$count++;  // the comment isn't in our list to review, so count it as real	
				}
			}
		}
		
		return $count;
	}
}


// permissions check
function moderated_comments_permissions_check(){
	$context = elgg_get_context();
	if($context == "moderated_comments_permissions"){
		return true;
	}
	
	return NULL;
}


//
//	removes a single item from an array
//	resets keys
//
function removeFromArray($value, $array){
	if(!is_array($array)){ return $array; }
	if(!in_array($value, $array)){ return $array; }
	
	for($i=0; $i<count($array); $i++){
		if($value == $array[$i]){
			unset($array[$i]);
			$array = array_values($array);
		}
	}
	
	return $array;
}


function moderated_comments_editablecomments_check($hook, $type, $return, $params) {
  $comment = $params['annotation'];
  
  $entity_guid = $comment->entity_guid;

  $mc_entity = get_entity($entity_guid);

  // get array of unreviewed comments
  $review_array = explode(',', $mc_entity->unmoderated_comments);
  
  if (in_array($comment->id, $review_array)) {
    return false;
  }
}












