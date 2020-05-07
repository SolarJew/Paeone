<?php
/*
#################################################
#  Paeone: Open Source Free System.             #
#  Authors: Yuri Nikolaev, Maksim Kudryavcev    #
#  Ver: 0.1                                     #
#################################################
*/

/* Выход из системы */
class Personal_account extends Container
{
	function __construct(){
		parent::__construct();
		
		$this->template = $this->view();
	}
	
	private function updateImg()
	{
		$result = false;
		
		$answer = $this->db->select("idUser,loginUser,statusUser,imgUser","users","idUser = '".$_SESSION['user']."'");
		if(isset($answer[0]['idUser']) && $answer[0]['statusUser'] == '1'){
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
						$nameImg = $answer[0]['loginUser']."_".time().".".$typeImg;
						$f = fopen(DOWNLOADS."/images/".$nameImg, "w");
						fwrite($f,$img);
						fclose($f);
						
						if(file_exists(DOWNLOADS."/images/".$nameImg)){
							$this->db->update("users","imgUser = '".$nameImg."'","idUser = '".$_SESSION['user']."'");
							@unlink(DOWNLOADS."/images/".$answer[0]['imgUser']);
						}else{
							$result['error_code'] = '4';
						}
					}else{
						$result['error_code'] = '1';
					}
				}else{
					$result['error_code'] = '1';
				}
			}else{
				$result['error_code'] = '2';
			}
		}else{
			$result['error_code'] = '3';
		}
		
		return $result;
	}
	
	private function view()
	{
		$result = '';
		if(isset($this->request->post['exit'])){
			unset($_SESSION['user']);
			$result = $this->template['logout'];
		}
		
		if(isset($this->request->post['updateImg']) && isset($this->request->post['img'])){
			$answer = $this->updateImg();
			if(isset($answer['error_code'])){$str = $answer['error_code'];}else{$str = "";}
			$result = "<script>messageLabelPersonalAccount(".$str.");</script>";
		}
		
		return $result;
	}
}
?>