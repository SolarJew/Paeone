<?php
/*
#################################################
#  Paeone: Open Source Free System.             #
#  Authors: Yuri Nikolaev, Maksim Kudryavcev    #
#  Ver: 0.1                                     #
#################################################
*/

/* Язык */
class Language extends Container
{
	function __construct(){
		parent::__construct();
		
		$this->template = $this->view();
	}
	
	private function view()
	{
		$languageName = array(	'ru'	=> 'Русский',
								'en'	=> 'English');
		
		$strAddr = implode('/', $this->request->address);
		
		$list_language = '';
		foreach($languageName as $key => $elem){
			if($key != $this->request->language){
				$url = ADDRESS.($key != $this->config->DEFAULT_LANGUAGE ? '/'.$key : '');
				$url.= ($strAddr == 'index' ? ($this->config->TYPE_DEFAULT_PAGE != '/' && $key != $this->config->DEFAULT_LANGUAGE ? '[*url | TYPE*]' : '/') : '/'.$strAddr.'[*url | TYPE*]');
				
				$list_language .= str_replace(	array('[*func | url*]', '[*func | alpha_two*]', '[*func | name_lang*]'), 
												array($url, $key, $elem),
												$this->template['language_elem']);
			}
		}
		$result = str_replace(array('[*func | alpha_two*]', '[*func | list_language*]'), array($this->request->language, $list_language), $this->template['language']);
		
		return $result;
	}
}
?>