<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
function LoggedSystem()
{
	$CI =& get_instance();
 	$log = $CI->session->all_userdata();
 	$logged = $CI->session->userdata('sanitasDistLogged');
 	if(!$logged){
 		redirect("/main/login");
 	}
 	else {
        return true;
    }
}
