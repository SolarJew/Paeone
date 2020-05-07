<?php
/*
#################################################
#  Paeone: Open Source Free System.             #
#  Authors: Yuri Nikolaev, Maksim Kudryavcev    #
#  Ver: 0.1                                     #
#################################################
*/

/* Регистрация */
class Registration extends Container
{
	function __construct(){
		parent::__construct();
		
		$answer = $this->insertUser();
		$this->template = $this->view($answer);
	}

	private function checkData()
	{
		$result = array();
			
		$login = preg_replace("/[^\-\_A-Za-z0-9]+/iu", "", $this->request->post['login']);
		if($this->request->post['login'] != $login){
			throw new Exception('[*lang | error_input*] A-Za-z0-9_-');
		}else{
			$result['answer']['loginUser'] = $login;
		}
		
		$password = preg_replace("/[^\-\_A-Za-z0-9]+/iu", "", $this->request->post['password']);
		if($this->request->post['password'] != $password || strlen($password) < 5){
			throw new Exception('[*lang | error_length_pass*] [*lang | error_input*] A-Za-z0-9_-');
		}else{
			$result['answer']['passUser'] = md5($password);
		}
		
		$surname = trim(preg_replace("/[^\-\_A-Za-zА-ЯЁа-яё0-9]+/iu", "", $this->request->post['surname']));
		if($this->request->post['surname'] != $surname){
			throw new Exception('[*lang | error_input*] A-Za-zА-ЯЁа-я0-9_-');
		}else{
			$result['answer']['surnameUser'] = mb_convert_case($surname, MB_CASE_TITLE, 'UTF-8');
		}
		
		$name = trim(preg_replace("/[^\-\_A-Za-zА-ЯЁа-яё0-9]+/iu", "", $this->request->post['name']));
		if($this->request->post['name'] != $name){
			throw new Exception('[*lang | error_input*] A-Za-zА-ЯЁа-я0-9_-');
		}else{
			$result['answer']['nameUser'] = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
		}
		
		$mDate = explode('.', $this->request->post['bithday']);
		if(isset($mDate[1]) && isset($mDate[2])){
			if(strlen($this->request->post['bithday']) != 10 || checkdate($mDate[1], $mDate[0], $mDate[2]) === false){
				throw new Exception('[*lang | error_date*]');
			}else{
				$result['answer']['dateBirthUser'] = $mDate[2]."-".$mDate[1]."-".$mDate[0];
			}
		}else{
			throw new Exception('[*lang | error_date*]');
		}
		
		if($this->request->post['gender'] == 'M' || $this->request->post['gender'] == 'F'){
			$result['answer']['genderUser'] = $this->request->post['gender'];
		}else{
			throw new Exception('[*lang | error_gender*]');
		}
		
		$mail = trim(preg_replace("/[^\-\@\.\_a-z0-9]+/iu", "", $this->request->post['mail']));
		if(filter_var($mail, FILTER_VALIDATE_EMAIL) === false){
			throw new Exception('[*lang | error_mail*]');
		}else{
			$result['answer']['emailUser'] = $mail;
		}
		
		$result['answer']['phoneUser'] = preg_replace("/[^0-9]+/iu", '', $this->request->post['phone']);
		$result['answer']['creatDateUser'] = date('Y-m-d H:i:s');
		
		if($this->request->post['img'] != ''){
			$split = explode('/', $this->request->post['img']);
			$type = explode(';', $split[1]);
						
			if($type[0] == 'png' || $type[0] == 'jpg' || $type[0] == 'jpeg' || $type[0] == 'gif'){
				
				if($type[0] == 'jpeg'){$typeImg = 'jpg';}else{$typeImg = $type[0];}
				
				$postImg = str_replace(' ', '+', $this->request->post['img']);
				list($w, $h) = @getimagesize($postImg);
				$mI = explode(',', $postImg);
				$img = base64_decode($mI[1]);
				
				if(mb_strlen($img, '8bit') < 30000 && $w == 200 && $h == 200){
					$result['answer']['img'] = $img;
					$nameImg = $this->request->post['login']."_".strtotime($result['answer']['creatDateUser']).".".$typeImg;
					$result['answer']['saveImg'] = true;
				}else{
					throw new Exception('[*lang | error_img*]');
				}
			}else{
				throw new Exception('[*lang | error_img*]');
			}
			
		}else{
			$nameImg = '';
			$result['answer']['saveImg'] = false;
		}
		
		$result['answer']['imgUser'] = $nameImg;
		$result['answer']['statusUser'] = '1';
		
		return $result;
	}
	
	private function insertUser()
	{
		$result = false;
		
		$check = true;
		$checkArray = array('img'=>0, 'login'=>1, 'password'=>1, 'surname'=>1, 'name'=>1, 'bithday'=>1, 'gender'=>1, 'phone'=>1, 'mail'=>1);
		foreach($this->request->post as $key => $elem){
			if(!isset($checkArray[$key]) || $elem == '' && $checkArray[$key] == 1){
				$check = false;
				break;
			}
			unset($checkArray[$key]);
		}
		if(count($checkArray) > 0)
			$check = false;
		
		if($check == true){
			
			try{
				$mUser = $this->checkData();
			}catch(Exception $e){
				$mUser['error'] = $e->getMessage();
			}
			
			if(!isset($mUser['error'])){
				$answer = $this->db->select("idUser,emailUser","users","emailUser = '".$mUser['answer']['emailUser']."' or loginUser = '".$mUser['answer']['loginUser']."'");
				
				if(!isset($answer[0]['idUser'])){
						
					$answer = $this->db->insert("users","loginUser,
														 passUser,
														 surnameUser,
														 nameUser,
														 dateBirthUser,
														 genderUser,
														 imgUser,
														 phoneUser,
														 emailUser,
														 creatDateUser,
														 statusUser","'".$mUser['answer']['loginUser']."',
																		  '".$mUser['answer']['passUser']."',
																		  '".$mUser['answer']['surnameUser']."',
																		  '".$mUser['answer']['nameUser']."',
																		  '".$mUser['answer']['dateBirthUser']."',
																		  '".$mUser['answer']['genderUser']."',
																		  '".$mUser['answer']['imgUser']."',
																		  '".$mUser['answer']['phoneUser']."',
																		  '".$mUser['answer']['emailUser']."',
																		  '".$mUser['answer']['creatDateUser']."',
																		  '".$mUser['answer']['statusUser']."'");
						
					if($answer != false){
						$_SESSION['user'] = $answer;
						if($mUser['answer']['saveImg'] == true){
							$f = fopen(DOWNLOADS."/images/".$mUser['answer']['imgUser'], "w");
							fwrite($f,$mUser['answer']['img']);
							fclose($f);
						}
					}else{
						$result['error'] = '[*lang | error_registering*]';
					}
				}else{
					if(isset($answer[0]['emailUser']) && $answer[0]['emailUser'] == $mUser['answer']['emailUser']){
						$result['error'] = '[*lang | error_isset_user_mail*]';
					}else{
						$result['error'] = '[*lang | error_isset_user_login*]';
					}
				}
			}else{
				$result['error'] = $mUser['error'];
			}
			
		}else{
			$result['error'] = '[*lang | error_params*]';
		}
		
		return $result;
	}
	
	private function view($answer)
	{
		$result = false;
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