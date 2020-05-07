<?php
/*
#################################################
#  Paeone: Open Source Free System.             #
#  Authors: Yuri Nikolaev, Maksim Kudryavcev    #
#  Ver: 0.1                                     #
#################################################
*/

/* Авторизация */
class Authorization extends Container
{
	function __construct(){
		//self::$permitGet = true;
		//self::$permitGlobal = true;
		//self::$permitHtml = true;
		
		parent::__construct();
		
		$answer = $this->checkUser();
		$this->template = $this->view($answer);
	}
	
	private function checkUser()
	{
		$result = array();
		if(isset($this->request->post['login']) && isset($this->request->post['password'])){
			
			$answer = $this->db->select("idUser, passUser","users","loginUser = '".$this->request->post['login']."' and statusUser = '1'");
			if(isset($answer[0]['idUser'])){
				if(md5($this->request->post['password']) == $answer[0]['passUser']){
					$_SESSION['user'] = $answer[0]['idUser'];
				}else{
					$result['error'] = '[*lang | error_login_pass*]';
				}
			}else{
				$result['error'] = '[*lang | error_login_pass*]';
			}
		}else{
			$result['error'] = '[*lang | error_params*]';
		}
		
		return $result;
	}
	
	private function view($answer)
	{
		if(isset($answer['error'])){
			$str = str_replace('[*func | error_text*]', $answer['error'], $this->template['error_mess']);
			$result = str_replace('[*func | message*]', $str, $this->template['error_answer']);
		}else{
			$result = $this->template['posit_answer'];
		}
		
		return $result;
	}
}
?>