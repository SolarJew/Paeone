<?php
/*
#################################################
#  Paeone: Open Source Free System.             #
#  Authors: Yuri Nikolaev, Maksim Kudryavcev    #
#  Ver: 0.1                                     #
#################################################
*/

/* Registration */
class Registration extends Container
{
	function __construct(){
		parent::__construct();
		
		if(!isset($_SESSION['user'])){
			$this->template = $this->template['registration'];
		}else{
			header('Location: '.ADDRESS.($this->request->language != $this->config->DEFAULT_LANGUAGE ? '/'.$this->request->language : '').'/personal_account'.TYPE, true, 301);
			exit(0);
		}
	}
}
?>