<?php
/*
#################################################
#  Paeone: Open Source Free System.             #
#  Authors: Yuri Nikolaev, Maksim Kudryavcev    #
#  Ver: 0.1                                     #
#################################################
*/

/* Authorization */
class Authorization extends Container
{
	function __construct(){
		parent::__construct();
		
		if(!isset($_SESSION['user'])){
			$this->template = $this->template['authorization'];
		}else{
			$this->template = $this->template['user'];
		}
	}
}
?>