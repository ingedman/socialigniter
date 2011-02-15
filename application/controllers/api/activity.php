<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* 
 * Activity API : Core : Social-Igniter
 *
 */
class Activity extends Oauth_Controller
{
    function __construct()
    {
        parent::__construct();      
	}
	
    /* GET types */
    function recent_get()
    {
    	$activity = $this->social_igniter->get_timeline(NULL, 10);
        
        if($activity)
        {
            $message	= array('status' => 'success', 'message' => 'Success activity has been found', 'data' => $activity);
        }
        else
        {
            $message	= array('status' => 'error', 'message' => 'Could not find any activity');
        }
        
        $this->response($message, 200); 
    }

	// Acitivty View
	function view_get()
    {
    	$search_by	= $this->uri->segment(4);
    	$search_for	= $this->uri->segment(5);
		$activity	= $this->social_igniter->get_activity_view($search_by, $search_for);    
   		 	
        if($activity)
        {
            $message 	= array('status' => 'success', 'message' => 'Yay found some actvity', 'data' => $activity);
        }
        else
        {
            $message 	= array('status' => 'error', 'message' => 'Could not find any '.$search_by.' content for '.$search_for);
        }

        $this->response($message, 200);
    }


	/* POST types */
    function create_authd_post()
    {
    	$user_id = $this->session->userdata('user_id');   
    
    	$category_data = array(
    		'parent_id'		=> $this->input->post('parent_id'),
			'site_id'		=> config_item('site_id'),		
			'permission'	=> $this->input->post('permission'),
			'module'		=> $this->input->post('module'),
			'type'			=> $this->input->post('type'),
			'category'		=> $this->input->post('category'),
			'category_url'	=> $this->input->post('category_url')
    	);

		// Insert
	    $category = $this->categories_model->add_category($category_data);

		if ($category)
		{
        	$message	= array('status' => 'success', 'data' => $category);
        }
        else
        {
	        $message	= array('status' => 'error', 'message' => 'Oops unable to add your category');
        }
	
        $this->response($message, 200);
    }
    
    /* PUT types */
    function update_authd_put()
    {
		$viewed = $this->social_tools->update_activity_viewed($this->get('id'));			
    	
        if($viewed)
        {
            $message = array('status' => 'success', 'message' => 'Activity viewed');
        }
        else
        {
            $message = array('status' => 'error', 'message' => 'Could not mark as viewed');
        } 

        $this->response($message, 200);           
    }  

    /* DELETE types */
    function destroy_authd_delete()
    {		
		// Make sure user has access to do this func
		$access = $this->social_tools->has_access_to_modify('comment', $this->get('id'));
    	
    	// Move this up to result of "user_has_access"
    	if ($access)
        {        
        	$this->social_igniter->delete_activity($this->get('id'), $this->oauth_user_id);
        
			// Reset comments with this reply_to_id
			$this->social_tools->update_comment_orphaned_children($this->get('id'));
			
			// Update Content
			$this->social_igniter->update_content_comments_count($this->get('id'));
        
        	$message = array('status' => 'success', 'message' => 'Activity was deleted');
        }
        else
        {
            $message = array('status' => 'error', 'message' => 'Could not delete that activity');
        }
        
        $this->response($message, 200);
    }

}