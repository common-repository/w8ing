<?php
/**
 * Plugin Name: W8ing
 * Plugin URI: https://github.com/bgcom/bgp-wp-w8ing
 * Description: A basic waiting/landing page plugin for Wordpress
 * Version: 1.0
 * Author: Guillaume Molter for B+G & Partners SA
 * Author URI: http://bgcom.ch
 * License: WTFPL
 */
 
 
require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'w8ing_admin.php');

function w8ing_redirect($wp_query){
	
	/*** We retreive the plugin's options - Array ***/
	$options = get_option('w8ing_options');
	
	$landingURl=false;
	$vip=false;
	

	if(function_exists('icl_object_id')) {
		$options["pageID"] = icl_object_id($options["pageID"],'page',true);
	}
	
	/*** We check that the visitor is not a white listed IP address or trying to reach a white listed  ***/
	
	if(isset($options["ip_list"]) && $options["ip_list"]!=""){
		$ip_list=explode(",", $options["ip_list"]);
		foreach($ip_list as $ip){
			if($ip==$_SERVER['REMOTE_ADDR']){
				$vip=true;
			}
		}
	}
	
	if(isset($options["page_list"]) && $options["page_list"]!=""){
		$post_list=explode(",", $options["page_list"]);
		foreach($post_list as $post){
			if($post==$wp_query->query_vars["page_id"]){
				$vip=true;
			}
		}
	}
	
	
	
	/*** We check if a landing page has been defined and if  ***/
	if(isset($options["pageID"])){
		
		$landingURl=get_permalink($options["pageID"]);
		
		if($landingURl && !$vip && !is_admin()){
			
			/*** We check if the landing page redirection is active or not ***/
			if(isset($options["active"]) && $options["active"]=="on"){
				
				$redirect = true;
				
				if( isset($wp_query->query_vars) && isset($wp_query->query_vars["page_id"]) && $options["pageID"]==$wp_query->query_vars["page_id"]){
					$redirect=false;
				}
				
				if( isset($wp_query->queried_object) && isset($wp_query->queried_object->ID) && $options["pageID"]==$wp_query->queried_object->ID){
					$redirect=false;
				}
				
				if($redirect) wp_redirect($landingURl, 302 );
			}
			/*** If not we check if we should redirect to the homepage ***/
			elseif(isset($options["homeRedirect"]) && $options["homeRedirect"]=="on"){
			
				$redirect=false;
				
				
				if(isset($wp_query->query_vars) && isset($wp_query->query_vars["page_id"]) && $options["pageID"]==$wp_query->query_vars["page_id"]){
					$redirect=true;
				}
				if(isset($wp_query->queried_object) && isset($wp_query->queried_object->ID) && $options["pageID"]==$wp_query->queried_object->ID){
					$redirect=true;
				}
				
				if($redirect) wp_redirect(get_bloginfo("wpurl"), 302 );
				
			}
		}
	}
		
}


add_action('pre_get_posts', 'w8ing_redirect', 1);

?>