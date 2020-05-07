<?php
/*
#################################################
#  Paeone: Open Source Free System.             #
#  Authors: Yuri Nikolaev, Maksim Kudryavcev    #
#  Ver: 0.1                                     #
#################################################
*/

/* Файл конфигурации */
class Config
{
	function __construct(){
		$this->DESING				= 'default'; /* допустимые значения соответствуют названиям папок в директории Design */
		$this->DEFAULT_LANGUAGE		= '[*default_language*]'; /* допустимые значения соответствуют названиям папок в директории Language */
		$this->TYPE_DEFAULT_PAGE	= '[*type_default_page*]'; /* допустимые значения: 'null' , '/' , 'html' , 'htm' , 'php' */
		
		/* 	true в типах обозначает вывод с подложкой шаблона
			false в типах обозначает вывод только контента (без подложки, так же может меняться заголовок "Content-type" в зависимости от функции) */
		$this->TYPES				= array('null'	=> true, // null это: отсутствие любого типа или слеша в конце адреса
											'/'		=> true,
											'html'	=> true,
											'htm'	=> true,
											'php'	=> true,
											'func'	=> false);	// Если требуется обрабатывать txt (robot.txt) или xml (sitemap.xml) через функции,
																// эти типы можно добавить в $this->TYPES со значением false
		
		/* Массив допустимых к прямому обращению адресов (для fileReading() ) */
		$this->ALLOWED_ADDR			= array('Downloads/images'=>1,
											'Design/'.$this->DESING.'/css'=>1,
											'Design/'.$this->DESING.'/img'=>1,
											'Design/'.$this->DESING.'/js'=>1);
	}
}

class ConfigDataBase
{
	function __construct(){
		$this->SERVER				= '[*server*]';		// Адрес сервера БД
		$this->USER					= '[*user*]';		// Имя пользоватедя БД
		$this->PASSWORD				= '[*password*]';	// Пароль пользоватедя БД
		$this->DATABASE				= '[*database*]';	// Название БД
		
		if($this->USER == '') exit('Invalid database login.');
		if($this->PASSWORD == '') exit('Invalid database password.');
	}
}
?>