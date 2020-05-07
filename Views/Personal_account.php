<?php
/*
#################################################
#  Paeone: Open Source Free System.             #
#  Authors: Yuri Nikolaev, Maksim Kudryavcev    #
#  Ver: 0.1                                     #
#################################################
*/

/* Личный кабинет */
class Personal_account extends Container
{
	function __construct(){
		parent::__construct();
		
		if(!isset($_SESSION['user'])){
			header('Location: '.ADDRESS.TYPE, true, 301);
			exit(0);
		}
		
		$answer = $this->db->select("*","users","idUser = '".$_SESSION['user']."'");
		
		if(isset($answer[0]['idUser']) && $answer[0]['statusUser'] == '1'){
			
			if($answer[0]['imgUser'] == '' || file_exists(DOWNLOADS.'/images/'.$answer[0]['imgUser']) === false)
				$answer[0]['imgUser'] = 'none_avatar.png';
			
			$key = array('[*img*]','[*login*]','[*password*]','[*surname*]','[*name*]','[*bithday*]','[*gender*]','[*phone*]','[*mail*]');
			
			$userDate = date('d.m.Y', strtotime($answer[0]['dateBirthUser']));
			if($answer[0]['genderUser'] == 'M'){$userGender = '[*lang | list_male*]';}else{$userGender = '[*lang | list_female*]';}
			$p = $answer[0]['phoneUser'];
			$userPhone = '+'.$p[0].'('.$p[1].$p[2].$p[3].')'.$p[4].$p[5].$p[6].$p[7].$p[8].$p[9].$p[10];
			
			$data = array($answer[0]['imgUser'],$answer[0]['loginUser'],'password',$answer[0]['surnameUser'],$answer[0]['nameUser'],$userDate,$userGender,$userPhone,$answer[0]['emailUser']);
			
			$this->template = str_replace($key, $data, $this->template['personal_account']);
		}else{
			unset($_SESSION['user']);
			header('Location: '.ADDRESS.TYPE, true, 301);
			exit(0);
		}
	}
}
?>