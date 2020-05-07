<?php
/*
#################################################
#  Paeone: Open Source Free System.             #
#  Authors: Yuri Nikolaev, Maksim Kudryavcev    #
#  Ver: 0.1                                     #
#################################################
*/

/* Password_recovery */
class Password_recovery extends Container
{
	function __construct(){
		parent::__construct();
		
		if(!isset($_SESSION['user'])){
			$this->template = $this->template['password_recovery'];
		}else{
			header('Location: '.ADDRESS.'/personal_account'.TYPE, true, 301);
			exit(0);
		}
	}
}
?>