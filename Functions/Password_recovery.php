<?php
/*
#################################################
#  Paeone: Open Source Free System.             #
#  Authors: Yuri Nikolaev, Maksim Kudryavcev    #
#  Ver: 0.1                                     #
#################################################
*/

/* Сброс пароля */
class Password_recovery extends Container
{
	function __construct(){
		parent::__construct();
		
		$answer = $this->checkUser();
		$this->template = $this->view($answer);
	}
	
	private function codeGenerator()
	{
		$result = '';
		$arr = array('1','2','3','4','5','6','7','8','9','0');
		$code = "";
		for($i = 0; $i < 5; $i++){
			$index = rand(0, count($arr) - 1);
			$result .= $arr[$index];
		}
		return $result;
	}
	
	private function checkUser()
	{
		$result = '';
		if(isset($this->request->post['mail'])){
			$answer = $this->db->select("*","users","emailUser = '".$this->request->post['mail']."'");
			if(isset($answer[0]['idUser'])){
				$code = $this->codeGenerator();
				if(mail($this->request->post['mail'], "[*lang | send_title*]", "[*lang | send_greeting*] ".$answer[0]['nameUser'].".\n [*lang | send_text*] ".$code."")){
					$this->db->update("users","passUser = '".md5($code)."'","idUser = '".$answer[0]['idUser']."'");
					$result['success'] = '[*lang | success*]';
				}else{
					$result['error'] = '[*lang | error_send*]';
				}
			}else{
				$result['error'] = '[*lang | error_user*]';
			}
		}else{
			$result['error'] = '[*lang | error_params*]';
		}
		
		return $result;
	}
	
	private function view($answer)
	{
		if(isset($answer['error'])){
			$str = str_replace('[*func | text*]', $answer['error'], $this->template['error_mess']);
		}else{
			$str = str_replace('[*func | text*]', $answer['success'], $this->template['success_mess']);
		}
		
		$result = str_replace('[*func | message*]', $str, $this->template['answer']);
		
		return $result;
	}
}
?>