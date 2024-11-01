<?php

/************* Creating the setting menu *************/
add_action('admin_menu', 'w8ing_admin_add_page');

function w8ing_admin_add_page() {
	add_options_page('W8ing', 'W8ing', 'manage_options', 'w8ing', 'w8ing_options_page');
}

/************* Custom style for our admin page *************/
function w8ing_admin_style() {
	wp_register_script('w8ing_admin_style', plugins_url('w8ing_style.css', __FILE__));
	wp_enqueue_script('w8ing_admin_style');
}

add_action( 'admin_enqueue_scripts', 'w8ing_admin_style' );  


/************* Creating the admin page wrapper *************/
function w8ing_options_page() { 
	?>
	<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2><?php echo __("W8ing","w8ing"); ?></h2>
	<form action="options.php" method="post">
	<?php settings_fields('w8ing_options'); ?>
	<?php do_settings_sections('w8ing'); ?>
	<input name="submit" type="submit" class="button button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
	</form></div>
	<?php
}

/************* Creating the various settings and forms needed *************/
function w8ing_admin_init(){
	
	/*** We store all our plugin data in 1 WP option containing an array ***/
	register_setting( 'w8ing_options', 'w8ing_options', 'w8ing_options_validate' );
	
	/*** Then we had various setting section and forms ***/
	add_settings_section('w8ing_page', __('Landing page','w8ing'), 'w8ing_page_text', 'w8ing');
	add_settings_field('w8ing_pageID', __('Select the landing page','w8ing'), 'w8ing_setting_pageID', 'w8ing', 'w8ing_page');
	
	add_settings_section('w8ing_activation', __('Activation','w8ing'), 'w8ing_activation_text', 'w8ing');
	add_settings_field('w8ing_active', __('Activate the landing page','w8ing'), 'w8ing_setting_active', 'w8ing', 'w8ing_activation');
	add_settings_field('w8ing_homeRedirect', __('Redirect to homepage','w8ing'), 'w8ing_setting_homeRedirect', 'w8ing', 'w8ing_activation');
	
	add_settings_section('w8ing_whitelist', __('White List','w8ing'), 'w8ing_section_text', 'w8ing');
	add_settings_field('w8ing_ip_list', __('IP Address list','w8ing').'<br/><span class="description">'.__('(1 per line)','w8ing').'</span>', 'w8ing_setting_white_ip_list', 'w8ing', 'w8ing_whitelist');
	add_settings_field('w8ing_page_list', __('Posts & Pages list','w8ing').'<br/><span class="description">'.__('(post ID - 1 per line)','w8ing').'</span>', 'w8ing_setting_white_page_list', 'w8ing', 'w8ing_whitelist');

}

add_action('admin_init', 'w8ing_admin_init');

/************* Form Section : Select the page to be used as Landing page *************/
function w8ing_page_text() {
	echo '<p>'.__('Select on which page you want your traffic to be redirected when the landing page is activated','w8ing').'</p>';
} 

function w8ing_setting_pageID() {
	$options = get_option('w8ing_options');
	echo '<select id="w8ing_pageID" name="w8ing_options[pageID]"">';
	$pages = get_pages($args);
	echo '<option value="">'.__("Select a page","w8ing").'</option>';
	
	foreach($pages as $page){
		echo '<option value="'.$page->ID.'"';
		
		if(isset($options) && isset($options['pageID']) && $options['pageID']==$page->ID){
			echo ' selected="selected"';
		}
		
		echo '>'.$page->post_title.'</option>';
	}
	
	
	echo "</select>";
}

/************* Form Section : Manage the activation of the redirection *************/
function w8ing_activation_text() {
		echo '<p>'.__('The following check box allows you to activate or not the redirection to the the landing page','w8ing').'</p>';
}

function w8ing_setting_active() {
	$options = get_option('w8ing_options');
	echo '<input type="checkbox" id="w8ing_active" name="w8ing_options[active]"';
	if(isset($options) && isset($options['active']) && $options['active']=="on"){
		echo ' checked="checked"';
	}
	echo "> ".__("yes redirect all traffic (except for white listed IP & Pages) to the landing page.","w8ing");
	echo '<p class="description">'.__('You need to select a page in the "Landing page" section for this parameter to take effect','w8ing').'</p>';
} 

function w8ing_setting_homeRedirect() {
	$options = get_option('w8ing_options');
	echo '<input type="checkbox" id="w8ing_homeRedirect" name="w8ing_options[homeRedirect]"';
	if(isset($options) && isset($options['homeRedirect']) && $options['homeRedirect']=="on"){
		echo ' checked="checked"';
	}
	echo "> ".__(" redirect people who are trying to access the landing page (via it's url) to the homepage","w8ing");
	echo '<p class="description">'.__('Only effective if landing page is NOT activated','w8ing').'</p>';
} 


/************* Form Section : Manage the IP address White list & the post and page White list *************/
function w8ing_section_text() {
	echo '<p>'.__('Set IPs (ex: developers and clients Public IPs) and Post, Custom Posts & Pages (ex: callback pages) that should not be affected by the redirection to the landing page.','w8ing').'</p>';
} 

function w8ing_setting_white_ip_list() {
	$options = get_option('w8ing_options');
	echo "<textarea id='w8ing_ip_list' name='w8ing_options[ip_list]'>";
	if(isset($options) && isset($options['ip_list'])){
		echo str_replace(",", "\r", $options['ip_list']);
	}
	echo "</textarea>";
	echo '<p class="description">'.__('Warning:','w8ing').' '.__('This is not a','w8ing').' <a href="http://en.wikipedia.org/wiki/IP_address_spoofing" target="_blank">'.__('reliable method','w8ing').'</a>.'.__('Don\'t rely on IP filtering for sensitive data.','w8ing').'</p>';
}

function w8ing_setting_white_page_list() {
	$options = get_option('w8ing_options');
	echo "<textarea id='w8ing_page_list' name='w8ing_options[page_list]'>";
	if(isset($options) && isset($options['page_list'])){
		echo str_replace(",", "\r", $options['page_list']);
	}
	echo "</textarea>";
}

/************* Saving the admin page form data *************/
function w8ing_options_validate($input) {
	$options = get_option('w8ing_options');
	
	/*** Validating and saving the landing page ID ***/
	if(preg_match('/^[0-9]+$/i', $input['pageID'])) {
		$options['pageID']=$input['pageID'];
	}
	else{
		$options['pageID']="";
	}
	
	/*** Validating and saving the activation checkbox param ***/
	if(isset($input) && isset($input['active']) && $input['active']=="on") {
		$options['active'] = "on";
	}
	else{
		$options['active'] = '';
	}
	
	/*** Validating and saving the home redirection checkbox param ***/
	if(isset($input) && isset($input['homeRedirect']) && $input['homeRedirect']=="on") {
		$options['homeRedirect'] = "on";
	}
	else{
		$options['homeRedirect'] = '';
	}
		
	/*** Validating and saving the white list IP list ***/
	/*** Note: IP list is stored as a coma separated list ***/
	$ip_list = preg_split("/\\r\\n|\\r|\\n/", $input['ip_list']);
	$newIpList=array();
	foreach ($ip_list as $ip){
		if(preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/i', $ip)) {
			$newIpList[]=$ip;
		}
	}
	
	$options['ip_list']=implode(",",$newIpList);	
	
	/*** Validating and saving the white list page list ***/
	/*** Note: Page list is stored as a coma separated list ***/
	$page_list = preg_split("/\\r\\n|\\r|\\n/", $input['page_list']);
	$newpage_list=array();
	foreach ($page_list as $page){
		if(preg_match('/^[0-9]+$/i', $page)) {
			$newpage_list[]=$page;
		}
	}
	
	$options['page_list']=implode(",",$newpage_list);
	
	
	return $options;
}
