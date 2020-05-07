/* Язык */
function openlangueList() {
	$("#langue").off('click');
	$("#langue").click(function(){
		$("#langueList").css("display","block");
		$("#langueList").attr('tabindex', '-1');
		$("#langueList").focus();
	
		closeDropDownBlock('closeLangueList', $("#langueList"));
	});
}

/* Закрытие выпадающих блоков */
function closeDropDownBlock(nameFunc, objA, objB) {
	if(objB == undefined){objB = objA;}
	objB.blur(function(){
		$(document.activeElement).click(function(){
			objB.off('blur');
			$(this).off('click');
			var checkP = $(document.activeElement).closest(objA).prop('nodeName');
			if(checkP != undefined){
				closeDropDownBlock(nameFunc, objA, $(document.activeElement));
			}else{
				window[nameFunc](objA);
			}
		});
	});
}

/* Процесс закрытия окна */
function closeLangueList(){
	$("#langueList").removeAttr("tabindex");
	$("#langueList").css("display","none");
}

/* Меню */
function openMenu() {
	$("#containerDownMenu").css("display","block");
	$("#containerDownMenu").attr('tabindex', '-1');
	$("#containerDownMenu").focus();
	closeDropDownBlock('closeMenu', $("#containerDownMenu"));
}
/* Процесс закрытия окна */
function closeMenu(){
	$("#containerDownMenu").removeAttr("tabindex");
	$("#containerDownMenu").css("display","none");
}

/* Авторизация */
function Authorization(elem) {
	if($('#loader').is(':hidden')){
		var login = $("#login").val();
		var password = $("#password").val();
		
		$('.inputInConteiner').removeClass('errorBorder');
		if(login == ''){$('#login').addClass('errorBorder'); $('#login').focus(); return false;}
		if(password == ''){$('#password').addClass('errorBorder'); $('#password').focus(); return false;}
		
		$("#loader").css("display", "block");
		$.ajax({
			type:"POST",
			url:"[*url | ADDRESS*]/authorization.func",
			data:"login="+login+"&password="+password,
			headers:{'TOKEN':$('#ajax').attr('token')},
			success:function(html){
				$("#ajax").html(html);
			}
		});
	}
}

/* Закрыть уведомление */
function closeAlert() {	
	$('.close').off('click');
	$('.close').click(function(){
		$(".alertTop").fadeOut();
	});	
}

/* Гендер */
function openGenderList() {
	$("#gender").off('click');
	$("#gender").click(function(){
			
		var lastValue = $("#gender").val()
		$("#gender").val('');
		
		var widthBlock = $("#genderForm").width();
		$("#genderList").width(widthBlock);
		
		$("#genderList").css("display","block");
		
		document.getElementById("gender").onblur = function(){
			$("#genderList").css("display","none");
			var value = $("#gender").val();
			if(value == ""){$("#gender").val(lastValue);}
		};
		
		selectGenderList();
	});
}

function selectGenderList() {
	$('.optGenderList').off('mousedown');
	$('.optGenderList').mousedown(function(e) {
		var selectElem = $(e.target).text();
		var valueId = $(e.target).attr('valueId');
		
		$("#gender").val(selectElem);
		$("#gender").attr('valueId', valueId);
	});
}

/* Выход из кабинета */
function exitAccount(elem) {
	if($('#loader').is(':hidden')){
		$("#loader").css("display", "block");
		$.ajax({
			type:"POST",
			url:"[*url | ADDRESS*]/personal_account.func",
			data:"exit=True",
			headers:{'TOKEN':$('#ajax').attr('token')},
			success:function(html){
				$("#ajax").html(html);
			}
		});
	}
}

/* Восстановление пароля */
function recoveryPass() {
	var mail = $("#recoveryMail").val();
	$('.inputInConteiner').removeClass('errorBorder');
	if(mail == ''){$('#recoveryMail').addClass('errorBorder'); $('#recoveryMail').focus(); return false;}
	
	var checkMail = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	
	if(checkMail.test(mail) == false){
		messageLabelRecovery(1, '');
		return false;
	}

	if($('#loader').is(':hidden')){
		$("#loader").css("display", "block");
		$.ajax({
			type:"POST",
			url:"[*url | ADDRESS*]/password_recovery.func",
			data:"mail="+mail,
			headers:{'TOKEN':$('#ajax').attr('token')},
			success:function(html){
				$("#ajax").html(html);
			}
		});
	}
	
	closeAlert();
}

/* Маски */
function focusInputMask() {
	$('.mask').off('focus');
	$('.mask').focus(function(){
		var mask = $(this).attr('typeMask');
		var readonly = $(this).attr('readonly');
		var placeholder = $(this).attr('placeholder');
		var valInput = $(this).val();
		
		if(readonly == undefined){
			
			if(mask == 'phone'){
				var mPl = placeholder.split('(');
				if(valInput == '' && mPl[1] != undefined){$(this).val(mPl[0]);}
			}
			
			$(this).off('keydown');
			$(this).keydown(function(e){
				
				if(e.key != 'F5' && e.key != 'Tab'){				 
					var valInput = $(this).val();					 
					var count = valInput.length;
					
					if(e.key >= '0' && e.key <= '9' || e.key == 'Backspace'){
						
						if(count < placeholder.length && e.key != 'Backspace' || this.selectionStart != this.selectionEnd){
							if(mask == 'data' && placeholder[count] == '.'){$(this).val(valInput+placeholder[count]);}
							if(mask == 'phone'){
								if(placeholder[count] != '_' && placeholder[count] != undefined){
									$(this).val(valInput+placeholder[count]);
								}
								
								if(count == placeholder.length){
									if(mPl[1] != undefined){$(this).val(mPl[0]);}
									return false;
								}
								
							}
							
						}else{
							if(e.key != 'Backspace'){
								return false;
							}else{
								if(mask == 'phone' && count > mPl[0].length || mask == 'data'){
									if(placeholder[count - 2] == '(' || placeholder[count - 2] == ')' || placeholder[count - 2] == '-' || placeholder[count - 2] == '.'){
										$(this).val(valInput.substring(0, valInput.length - 1));
									}
								}else{
									return false;	
								}
							}
						}
						
					}else{
						return false;		
					}
				}
			});
			
			$(this).off('blur');
			$(this).blur(function(){
				var val = $(this).val();
				
				if(val.length < placeholder.length){$(this).val('');var val = '';}
				
				if(mask == 'data' && val != ''){
					var mData = val.split('.');
					var date = new Date(mData[2]+'/'+mData[1]+'/'+mData[0]+'');
					if(mData[2] != date.getFullYear() || mData[1] != (date.getMonth() + 1) || mData[0] != date.getDate()){
						$(this).addClass('errorBorder');
					}
				}
			});
		}
	});
}

/* Подгрузка изображения */
function proccessLodImg(input, check) {
	if(input.files && input.files[0]) {
		parts = input.files[0].name.split('.');
		if(parts.length > 1){format = parts.pop();}
		
		if(format != 'png' && format != 'jpg' && format != 'jpeg' && format != 'gif'){
			if(check == 'lk'){messageLabelPersonalAccount(1, '');}else{messageLabelRegistration(5, '');}
			return false;	
		}
		
		if(input.files[0].size > 30000){
			if(check == 'lk'){messageLabelPersonalAccount(1, '');}else{messageLabelRegistration(5, '');}
			return false;
		}
			
		var reader = new FileReader();
		reader.onload = function(e) {
			var image = new Image();
			image.src = reader.result;
			image.onload = function() {
				if(image.width != 200 && image.height != 200){
					if(check == 'lk'){messageLabelPersonalAccount(1, '');}else{messageLabelRegistration(5, '');}
					return false;
				}
    		};
	
			$('#newImg').attr('src', e.target.result);
			$('#newImg').css('display', 'block');
			if(check == 'lk'){updateImgPersonal_account();}
		}
		reader.readAsDataURL(input.files[0]);
	}
}

$(document).ready(function() {
	openlangueList();
});

/* Обновление изображения в личном кабинете */
function updateImgPersonal_account() {
	if($('#loaderImg').is(':hidden')){
		$("#loaderImg").css("display", "block");
		var img = $('#newImg').attr('src');
		$.ajax({
			type:"POST",
			url:"[*url | ADDRESS*]/personal_account.func",
			data:"updateImg=true&img="+img,
			headers:{'TOKEN':$('#ajax').attr('token')},
			success:function(html){
				$("#ajax").html(html);
			}
		});
	}
}


/* Регистрация */
function Registration() {
	if($('#loader').is(':hidden')){
		var login 		= $("#loginR").val();
		var password 	= $("#passwordR").val();
		var password_t 	= $("#password_t").val();
		var surname 	= $("#surname").val();
		var name 		= $("#name").val();
		var bithday 	= $("#bithday").val();
		var gender 		= $("#gender").attr('valueId');
		var phone 		= $("#phone").val();
		var mail 		= $("#mail").val();
		var img 		= $('#newImg').attr('src');
		
		$('.inputInConteiner').removeClass('errorBorder');
		
		if(login == ''){
			$('#loginR').addClass('errorBorder').focus(); return false;
		}else{
			var checkInput = /^[a-zA-Z0-9_\-]+$/
			if(checkInput.test(login) == false){
				$('#loginR').addClass('errorBorder').focus();
				var text = "a-zA-Z0-9_-";
				messageLabelRegistration(3, text);
				return false;
			}
		}
		
		if(password == ''){
			$('#passwordR').addClass('errorBorder').focus(); return false;
		}else{
			var checkInput = /^[A-Za-z0-9_\-]+$/
			if(checkInput.test(password) == false || password.length < 5){
				$('#passwordR').addClass('errorBorder').focus();
				var text = "a-zA-Z0-9_-";
				messageLabelRegistration(6, text);
				return false;
			}
		}
		
		if(password_t == ''){
			$('#password_t').addClass('errorBorder').focus(); return false;
		}else{
			var checkInput = /^[A-Za-z0-9_\-]+$/
			if(checkInput.test(password_t) == false || password_t.length < 5){
				$('#password_t').addClass('errorBorder').focus();
				var text = "a-zA-Z0-9_-";
				messageLabelRegistration(6, text);
				return false;
			}
		}
		
		if(password != password_t){
			messageLabelRegistration(2, '');
			$("#passwordR").addClass("errorBorder");
			$("#password_t").addClass("errorBorder");
			return false;
		}
		
		
		if(surname == ''){
			$('#surname').addClass('errorBorder').focus(); return false;
		}else{
			var checkInput = /^[А-ЯЁа-яёA-Za-z0-9_\-]+$/
			if(checkInput.test(surname) == false){
				$('#surname').addClass('errorBorder').focus();
				var text = "А-ЯЁа-яёA-Za-z0-9_-";
				messageLabelRegistration(3, text);
				return false;
			}
		}
		
		if(name == ''){
			$('#name').addClass('errorBorder').focus(); return false;
		}else{
			var checkInput = /^[А-ЯЁа-яёA-Za-z0-9_\-]+$/
			if(checkInput.test(name) == false){
				$('#name').addClass('errorBorder').focus();
				var text = "А-ЯЁа-яёA-Za-z0-9_-";
				messageLabelRegistration(3, text);
				return false;
			}	
		}
		
		if(bithday == ''){$('#bithday').addClass('errorBorder').focus(); return false;}
		if(gender == ''){$('#gender').addClass('errorBorder').focus(); return false;}
		if(phone == ''){$('#phone').addClass('errorBorder').focus(); return false;}
		
		if(mail == ''){
			$('#mail').addClass('errorBorder').focus(); return false;
		}else{
			var checkMail = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
			if(checkMail.test(mail) == false){
				messageLabelRegistration(1, '');
				return false;
			}
		}
		
		$("#loader").css("display", "block");
		var param = "img="+img+"&login="+login+"&password="+password+"&surname="+surname+"&name="+name+"&bithday="+bithday+"&gender="+gender+"&phone="+phone+"&mail="+mail;
		
		$.ajax({
			type:"POST",
			url:"[*url | ADDRESS*]/registration.func",
			data:param,
			headers:{'TOKEN':$('#ajax').attr('token')},
			success:function(html){
				$("#ajax").html(html);
			}
		});
	}
}