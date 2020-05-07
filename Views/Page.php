<?php
/*
#################################################
#  Paeone: Open Source Free System.             #
#  Authors: Yuri Nikolaev, Maksim Kudryavcev    #
#  Ver: 0.1                                     #
#################################################
*/

class Page extends Container
{
	function __construct(){
		parent::__construct();
		
		$this->template = $this->template['page'];
	}
}
?>