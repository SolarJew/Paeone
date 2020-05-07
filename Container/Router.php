<?php
/*
#################################################
#  Paeone: Open Source Free System.             #
#  Authors: Yuri Nikolaev, Maksim Kudryavcev    #
#  Ver: 0.1                                     #
#################################################
*/

/* Подключаем необходимые компоненты */
require PATH.'/config.php';
require PATH.'/Container/DB.php';

class Router
{
	function __construct(){
		static $config;
		static $db;
		static $language;
		static $request;
		
		/* Инициализация объектов только при первом обращении */
		if($config == false){
			$config = $this->config = new Config();
			$confDB = new ConfigDataBase();
			$db = $this->db = new DBClass($confDB->SERVER, $confDB->USER, $confDB->PASSWORD, $confDB->DATABASE);
		
			$request = $this->request = (object) array(	'address'		=> array(),
														'get'			=> array(),
														'post'			=> array(),
														'language'		=> $config->DEFAULT_LANGUAGE,
														'type'			=> null,
														'code'			=> 0);
			
			/* Обратотка компонентов запроса, проверка корректности адреса */
			$this->preparingСomponents();
			$language = $this->language;
			
			$type = ($this->config->TYPE_DEFAULT_PAGE == 'null' || $this->config->TYPE_DEFAULT_PAGE == '/' ? '' : '.');
			$type.= ($this->config->TYPE_DEFAULT_PAGE == 'null' ? '' : $this->config->TYPE_DEFAULT_PAGE);
			define('TYPE', $type);
		}
		
		$this->config	= $config;
		$this->db		= $db;
		$this->language	= $language;
		$this->request	= $request;
	}
	
	/* Очистка элементов GET/POST переменных */
	private function formatStr($string) 
    {
        $string = trim($string);
        $string = stripslashes($string);
        $string = htmlspecialchars($string);
        return $string;
    }
	
	/* Сборка массива и строки GET переменных */
	private function replaceGetVariables()
	{
		$result = '';
		foreach($_GET as $key => $elem){
			$key = preg_replace("/[^\-\_a-zа-яё0-9]+/iu", "", $key);
			$elem = $this->formatStr($elem);
			if($key != '' && $key != 'route'){
				$this->request->get[$key] = $elem;
				if($result != '') $result .= '&';
				$result .= $key;
				if($elem != '') $result .= '='.$elem;
			}
		}
		
		return $result;
	}
	
	/* Сборка массива POST переменных */
	private function replacePostVariables()
	{
		foreach($_POST as $key => $elem)
			$this->request->post[$this->formatStr($key)] = $this->formatStr($elem);
	}
	
	/* Обратотка компонентов запроса, проверка корректности адреса */
	private function preparingСomponents()
	{	/*
			Урезаем строку адреса (убираем тип соединения и имя хоста) (Если входная точка не является корневой директорией, то очищается и имя директории).
			Отделяем GET переменные от оставшейся строки.
			Основываясь на урезаной строке адреса и строке GET переменных, получаем строку запроса .
			(такая конфигурация поможет в дальнейшем исключить ошибки типа: example.com/???? или example.com/*** или example.com/!!!! и т.д.)
		*/
		$blankAddress	= str_replace(ADDRESS.'/', '', HTTP.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		$getVariables	= parse_url($blankAddress, PHP_URL_QUERY);
		$address		= str_replace($getVariables, '', $blankAddress);
		
		/*
			Из строки запроса убираем все символы кроме допустимых.
			Пересобираем строку GET переменных убирая из ключей все символы кроме допустимых.
			Определяем тип по строке запроса (html, php css, / и т.д.)
			Пересобираем массив POST переменных.
		*/
		$replaceAddr	= preg_replace("/[^\-\/\.\_a-zа-яё0-9]+/iu", "", $address);
		$replaceVar		= $this->replaceGetVariables();
		$type			= (substr($replaceAddr, -1) == '/' ? '/' : preg_replace("/[^a-zа-яё0-9]+/iu", "", pathinfo($address, PATHINFO_EXTENSION)));
		$this->replacePostVariables();
		
		/*
			Основываясь на полученом типе определяем нужен ли нам симол (.) при формировании корректной строки адреса.
			Получаем массив адреса из сторки запроса предварилельно убрав тип.
			Записываем в наш объект запроса полученый тип с корректировкой при пустом значении типа.
		*/
		$dot					= ($type != '' && $type != '/' ? '.' : '');
		$aGetAddr				= explode('/', substr($replaceAddr, 0, (mb_strlen($replaceAddr) - mb_strlen($dot.$type))));
		$this->request->type	= ($replaceAddr == '' ? $this->config->TYPE_DEFAULT_PAGE : ($type == '' ? 'null' : $type));
		
		/*
			Собираем корректный массив запроса исключая пустые элементы (это позволит избавиться от ошибок типа: example.com/one///two// и т.д.)
			Собираем строку корректного адреса, считаем количество ошибок (пустых элементов),
			так же заносим в ошибки index (если он является единственным элементом и
			полученый тип относится к страницам, а не файлам)
		*/
		$correctAddr = ''; $err = 0;
		for($i = 0; $i < count($aGetAddr); $i++){
			if($aGetAddr[$i] == '' || $aGetAddr[0] == 'index' && !isset($aGetAddr[1]) &&
			isset($this->config->TYPES[$this->request->type]) && $this->config->TYPES[$this->request->type] == true){
				$this->request->code++; $err++;
			}else{
				$this->request->address[] = $aGetAddr[$i];
				$correctAddr .= ($correctAddr != '' ? '/' : '').$aGetAddr[$i];
			}
		}
		
		/*
			Если полученный корректный адрес пуст или установлен по умолчани тип (/) и присутствует ошибка, минусуем одну ошибку 
			(слеш на конце адреса автоматически добавляяет одну ошибку, её мы и убираем)
		*/
		if($correctAddr == '' || $this->config->TYPE_DEFAULT_PAGE == '/' && $this->request->code > 0)
			$this->request->code = $this->request->code - 1;
		
		/*
			Получаем список доступных языковых папок и собираем массив языковых ключей
		*/
		foreach(glob(LANGUAGE.'/*') as $elem)
			$this->language[substr($elem, mb_strlen(LANGUAGE.'/'))] = substr($elem, mb_strlen(LANGUAGE.'/'));
		
		/*
			Проверяем существование нулевого элемента в корректном массиве запроса и проверяем на совпадение значение этого элемента
			с именем одной из языковых папок для определения языка.
			Проверяем совпадение значения нулевого элемента корректного адреса с выбранным языком по умолчанию.
			Убираем из строки корректного адреса определение языка (если но установлен по умолчанию).
			Изменяем в нашем объекте запроса найденный язык.
			Удаляем из массива корректного адреса нулевой элемент (элемент языка) НЕ сохраняя ключи
		*/
		if(isset($this->request->address[0]) && isset($this->language[$this->request->address[0]])){
			if($this->request->address[0] == $this->config->DEFAULT_LANGUAGE)
				$correctAddr = substr($correctAddr, mb_strlen($this->config->DEFAULT_LANGUAGE.'/'));
			$this->request->language = $this->request->address[0];
			array_splice($this->request->address, 0, 1);
		}
		
		/*
			Если в корректном массиве не осталось элементов, добавим нулевым элементом index
		*/
		if(!isset($this->request->address[0])) $this->request->address[0] = 'index';
		
		/*
			Если по умолчанию выбран тип (окончание адреса) не "null" и не "/", добавляем в строку корректного адреса точку
		*/
		if(	$this->config->TYPE_DEFAULT_PAGE != 'null' && $this->config->TYPE_DEFAULT_PAGE != '/' && $correctAddr != '' && 
			isset($this->config->TYPES[$this->request->type]) && $this->config->TYPES[$this->request->type] == true)
			$correctAddr .= '.'.$this->config->TYPE_DEFAULT_PAGE;
		
		/*
			Если по умолчанию выбран тип "/" и тип адреса соответствует формату (страница), добавляем в строку корректного адреса тип выбранный по умолчанию.
		*/
		if(	$this->config->TYPE_DEFAULT_PAGE == '/' && $correctAddr != '' && isset($this->config->TYPES[$this->request->type]) &&
			$this->config->TYPES[$this->request->type] == true)
			$correctAddr .= $this->config->TYPE_DEFAULT_PAGE;
		
		/*
			Если собранная строка корректного адреса не соответствует исходной строке, считаем + ошибка.
		*/
		if(ADDRESS.'/'.$correctAddr.($replaceVar != '' ? '?' : '').$replaceVar != ADDRESS.'/'.$address.$getVariables)
			$this->request->code++;
		
		/*
			Если собранная строка корректного адреса файла не соответствует исходной строке, считаем + ошибка.
		*/
		if(ADDRESS.'/'.$correctAddr.$dot.$type.($replaceVar != '' ? '?' : '').$replaceVar != ADDRESS.'/'.$address.$getVariables)
			$err++;
		
		/*
			Если тип не найден в массиве типов определённых под страницу, считаем + ошибка.
		*/
		if(!isset($this->config->TYPES[$this->request->type]))
			$this->request->code++;
		
		/*
			301 редирект на корректный адрес если code (ошибка) больше 0 и тип определён как страница
		*/
		if($this->request->code > 0 && isset($this->config->TYPES[$this->request->type]) && $this->config->TYPES[$this->request->type] == true){
			header('Location: '.ADDRESS.'/'.$correctAddr.($replaceVar != '' ? '?' : '').$replaceVar, true, 301);
			exit(0);
		}
		
		/*
			301 редирект на корректный адрес если code (ошибка) больше 0 и $err (ошибка 2) больше 0 
		*/
		if($this->request->code > 0 && $err > 0){
			header('Location: '.ADDRESS.'/'.$correctAddr.$dot.$type.($replaceVar != '' ? '?' : '').$replaceVar, true, 301);
			exit(0);
		}
	}
}
?>