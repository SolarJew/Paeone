<?php
/*
#################################################
#  Paeone: Open Source Free System.             #
#  Authors: Yuri Nikolaev, Maksim Kudryavcev    #
#  Ver: 0.1                                     #
#################################################
*/

/* Подложка элементов */
class Content extends Container
{
	function __construct(){
		parent::__construct();
		
		$this->template = $this->view();
		
		$this->connection('language');
		$this->connection('authorization');
		
		$view = 'page';
		if($this->dataPage->view != '')
			$view = ucfirst($this->dataPage->view);
		
		$this->connection($view);
	}
	
	private function view()
	{
		$url = '[*url | ADDRESS*]'.($this->request->language != $this->config->DEFAULT_LANGUAGE ? '[*url | TYPE*]' : '/');
		$result = str_replace('[*func | url*]', $url, $this->template['content']);
		
		return $result;
	}
}
?>