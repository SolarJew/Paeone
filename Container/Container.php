<?php
/*
#################################################
#  Paeone: Open Source Free System.             #
#  Authors: Yuri Nikolaev, Maksim Kudryavcev    #
#  Ver: 0.1                                     #
#################################################
*/

/* Подключаем необходимые компоненты */
require PATH.'/Container/Router.php';

class Container extends Router
{
	static $design;
	static $dataPages;
	static $languages;
	
	/* Значение данных переменных задаётся в классе функции, ДО parent::__construct(); */
	static $permitGet; // Если true: разрешает обращение к функции через GET запрос (по умолчанию false)
	static $permitGlobal; // Если true: разрешает обращение к функции со сторонних ресурсов (по умолчанию false)
	static $permitHtml; // Если true: выводит результат в формате html (по умолчанию json)
	
	function __construct(){
		parent::__construct();
		
		/* Запускаем при первом обращении к классу Container */
		if(self::$dataPages == false){
			$this->dataPage	= null;
			
			/* По коду ответа роутера определяем кто обработчик */
			switch($this->request->code){
				case 2:
					/* Вернуть ответ как файл (если он существует) */
					$this->fileReading();
					break;
				case 1:
					/* Вернуть ответ как web страницу без подложки или json (если функция доступна) */
					$this->includeFunction();
					break;
				case 0:
					/* Вернуть ответ как web страницу */
					$this->handler();
					break;
			}
			
			/* Если ответом на запрос не является изображение заменяем все метки на корректный адрес */
			if(!isset($this->image)){
				$lang = ($this->request->language != $this->config->DEFAULT_LANGUAGE ? '/'.$this->request->language : '');
				$this->result = str_replace(array("[*url | ADDRESS*]", "[*url | TYPE*]"), array(ADDRESS.$lang, TYPE), $this->result);
			}
		
		/*
			Запускаем при вторичных обращениях к классу Container.
			Эта часть запускается при обращении любого View файла или Function файла
		*/
		}else{
			if($this->request->code == 1)
				$this->permit();
			/* Определяем имя обратившегося класса */
			$name = mb_strtolower(get_class($this));
			try {
				/* Страницу 404 обрабатывает класс Page, поэтому корректируем имя если требуется вывести именно 404 */
				$name = ($name == 'page' && self::$dataPages->address == '404' ? '404' : $name);
				/* Если шаблон отсутствует выводим соответствующее сообщение (вместо шаблона) */
				if(file_exists(DESIGN."/".$this->config->DESING."/".$name.".html") === false)
					throw new Exception('File not found');
				/* Считываем шаблон */
				$string = file_get_contents(DESIGN."/".$this->config->DESING."/".$name.".html");
				
				/*
					Режем шаблон на элементы,
					Собираем массив из этих элементов,
					Записываем результат в $this->template для дальнейшей работы с элементами шаблона
				*/
				preg_match_all("/\[\*split \| (\w+)*\*\]/", $string, $array);
				$arrayT = explode('[*/split*]', str_replace($array[0], '[*/split*]', $string));
				
				$i = 0; $template = array();
				foreach($array[1] as $elem){
					$i++;
					$template[$elem] = $arrayT[$i];
					$i++;
				}
				
				$this->template = $template;
				
			}catch(Exception $e){
				$this->template = '';
				/* Выводим ошибку отсутствия шаблона, только если это web страница (функция может использоваться без шаблона) */
				if($this->request->code == 0)
					$this->template = $e->getMessage();
			}
		}
		/* Пробрасываем в this данные из базы при любом по счёту обращении */
		$this->dataPage = self::$dataPages;
	}
	
	/* Вернуть ответ как файл */
	private function fileReading()
	{
		/* Корректируем элемент адреса, если запрос адресован дизайну */
		if($this->request->address[0] == 'design'){
			$this->request->address[0] = str_replace('design', 'design/'.$this->config->DESING, $this->request->address[0]);
		}
		
		try {
			/* Проверяем разрешение для данного адреса */
			$shortAddr = $this->request->address;
			array_pop($shortAddr);
			if(!isset($this->config->ALLOWED_ADDR[ucfirst(implode('/', $shortAddr))]))
				throw new Exception('File not found');
				
			/* Получаем строку адреса */
			$pathFile = ucfirst(implode('/', $this->request->address)).'.'.$this->request->type;
			
			/* Проверяем наличие файла по адресу */
			if(file_exists($pathFile) === false)
				throw new Exception('File not found');
				
			/* Подключаем массив MIME типов и считываем файл */
			include PATH.'/Container/MimeArray.php';
			$this->result = file_get_contents($pathFile);
			
			/* Если файл является изображением, ставим соответствующую метку */
			if(strpos($mime_types[$this->request->type], 'image') !== false)
				$this->image = true;
			
			/* Подготавливаем заголовки для ответа */
			header("Cache-Control: max-age=3600, must-revalidate");
			header("Content-type: ".$mime_types[$this->request->type]."");
			header("Pragma: cache");
			
		}catch(Exception $e){
			/* Если адрес не является разрешённым, или файл отсутствует, запрашиваем страницу 404 */
			$this->request->code = 0;
			$this->handler(404);
		}
	}
	
	/* Вернуть ответ как text/html или json */
	private function includeFunction()
	{
		try {
			/* Проверка корректности запроса, проверка существования запрашиваемого файла */
			if(file_exists(FUNCTIONS.'/'.ucfirst(end($this->request->address)).'.php') === false)
				throw new Exception('Page not found');
			
			/* Установка данных страницы в none, если элемента не существует */
			if(self::$dataPages == false)
				self::$dataPages = $this->dataPage = 'none';
			/* Получение языкового пакета */
			if(self::$languages == false)
				self::$languages = parse_ini_file(LANGUAGE."/".$this->request->language."/".$this->request->language, true);
			
			/* Определение имени функции */
			$nameFunc = ucfirst(end($this->request->address));
			
			/* Подключение и исполнение запрошенной функции */
			include FUNCTIONS.'/'.$nameFunc.'.php';
			$function = new $nameFunc();
			
			/* Установка значений вместо языковых меток (если таковые имеются) */
			$result = $function->template;
			if(isset(self::$languages[$nameFunc])){
				$result = $this->pregMatchRepl('lang', self::$languages[$nameFunc], $result);
			}
			
			/* Вывод результата в формате json или html */
			if(self::$permitHtml != false){
				$this->result = $result;
				header("Content-type: text/html; charset=UTF-8");
			}else{
				$this->result = json_encode(str_replace(array("\r\n", "\n", "\r"), '', $result));
				header("Content-type: application/json; charset=UTF-8");
			}
			
			header("Cache-Control: must-revalidate");
			header("Pragma: no-cache");
			header("Expires: -1");
			
		}catch(Exception $e){
			$this->request->code = 0;
			$this->handler(404);
		}
	}
	
	/* Подключение дочерних классов родильским */
	public function connection($name)
	{
		try {
			/* Если у родительского класса отсутствует шаблон или он не является строкой, выводим ошибку */
			if(!is_string($this->template))
				throw new Exception('Incorrect template: '.mb_strtolower(get_class($this)));
			/* Заменяем в общем дизайне метку класса на его шаблон */
			self::$design = str_replace('[*include | '.mb_strtolower(get_class($this)).'*]', $this->template, self::$design);
			/* Если в языковом пакете присутствует ключ данного класса, производим замену языковых меток на соответствующий текст */
			if(isset(self::$languages[ucfirst(get_class($this))])){
				self::$design = $this->pregMatchRepl('lang', self::$languages[ucfirst(get_class($this))], self::$design);
			}
			/* Подключаем и инициализируем дочерний класс */
			include VIEWS.'/'.ucfirst($name).'.php';
			$class = new $name();
			/* Если объект запущен классом Сontent и в настройках страницы указано, что View не стандартный, меняем метку, на новую */
			if(mb_strtolower(get_class($this)) == 'content' && $this->dataPage->view != '')
				self::$design = str_replace('[*include | page*]', '[*include | '.mb_strtolower($this->dataPage->view).'*]', self::$design);
			/* Если у дочернего класса отсутствует шаблон или он не является строкой, выводим ошибку */
			if(!is_string($class->template))
				throw new Exception('Incorrect template: '.mb_strtolower($name));
			/* Заменяем в общем дизайне метку дочернего класса на его шаблон */
			self::$design = str_replace('[*include | '.mb_strtolower($name).'*]', $class->template, self::$design);
			/* Если в языковом пакете присутствует ключ дочернего класса, производим замену языковых меток на соответствующий текст */
			if(isset(self::$languages[ucfirst($name)])){
				self::$design = $this->pregMatchRepl('lang', self::$languages[ucfirst($name)], self::$design);
			}
		}catch(Exception $e){
			self::$design = str_replace('[*include | '.mb_strtolower($name).'*]', $e->getMessage(), self::$design);
		}
	}
	
	/* Замена меток на значения */
	private function pregMatchRepl($var, $array, $result)
	{	/* Получаем два массива меток на основе $var */
		preg_match_all("/\[\*".$var." \| (\w+)*\*\]/", $result, $arrayElem);
		/* Если метки найдены, собираем массив ключей (меток) и значений */
		if(isset($arrayElem[0][0])){
			$langText = array();
			foreach($arrayElem[1] as $key => $elem){
				$langText['key'][$elem]		= $arrayElem[0][$key];
				$langText['elem'][$elem]	= (!isset($array[$elem]) ? '' : $array[$elem]);
			}
			/* Заменяем метки на значения */
			$result = str_replace($langText['key'], $langText['elem'], $result);
		}
		
		return $result;
	}
	
	/* Проверка разрешений для includeFunction() */
	private function permit()
	{	/* Если запрос GET, а разрешения нет, выводим ошибку */
		if($_SERVER['REQUEST_METHOD'] == 'GET' && self::$permitGet == false)
			throw new Exception('Page not found');
		
		/* Если переменные отсутствуют, создаём их */
		$_SERVER['HTTP_TOKEN'] = (!isset($_SERVER['HTTP_TOKEN']) ? '' : $_SERVER['HTTP_TOKEN']);
		$_SERVER['HTTP_REFERER'] = (!isset($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER']);
		$_SESSION['token'] = (!isset($_SESSION['token']) ? '' : $_SESSION['token']);
		
		/* Если токен в запросе не равен токету в сессии или HTTP_REFERER не с нашего домена и разрешения нет, выводим ошибку */
		if(	$_SERVER['HTTP_TOKEN'] != $_SESSION['token'] && self::$permitGlobal == false || 
			strpos($_SERVER['HTTP_REFERER'], ADDRESS) === false && self::$permitGlobal == false)
			throw new Exception('Page not found');
		
		self::$permitGet = false;
		self::$permitGlobal = false;
	}
	
	/* Обработка стандартного запроса, вернуть text/html */
	private function handler($notFound = false)
	{	/* Готовоим заголовок */
		header("Content-type: text/html; charset=UTF-8");
		/* Если объект запущен конструктором, ищем в базе страницу запроса */
		if($notFound == false)
			$this->searchPage();
		/* Если станица не найдена, или объект запущен не из конструктора, ищем в базе страницу 404, готовим заголовок */
		if($this->dataPage == null || $this->dataPage =='none' ){
			$this->pageNotFound();
			header('HTTP/1.1 404 Not Found');
		}
		
		try {
			/* Если подложка не существует, останавливаем выполнение, выводим сообщение об ошибке */
			if(file_exists(DESIGN."/".$this->config->DESING."/index.html") === false)
				throw new Exception('File ..Design/'.$this->config->DESING.'/index.html: Not found');
			
			/* Наполняем статические переменные */
			self::$design		= file_get_contents(DESIGN."/".$this->config->DESING."/index.html");
			self::$dataPages	= $this->dataPage;
			self::$languages	= parse_ini_file(LANGUAGE."/".$this->request->language."/".$this->request->language, true);
			
			/* Подключаем и инициализируем класс-подложку */
			$name = 'content';
			include VIEWS.'/'.ucfirst($name).'.php';
			$class = new $name();
			
			/* Генерируем простой токен */
			$this->dataPage->token = $_SESSION['token'] = md5(session_id().time());
			/* Менем метки на данные из базы в шаблоне */
			self::$design = $this->pregMatchRepl('data', (array) $this->dataPage, self::$design);
			/* Менем метки на данные из языкового пакета в шаблоне */
			if(isset(self::$languages[ucfirst($name)])){
				self::$design = $this->pregMatchRepl('lang', self::$languages[ucfirst($name)], self::$design);
			}
			/* выводим результат */
			$this->result = self::$design;
			
		}catch(Exception $e){
			exit($e->getMessage());
		}
	}
	
	/* Получение данных из БД страницы 404 */
	private function pageNotFound()
	{	/* Делаем запрос в БД страниц 404 по языкам (по умолчанию и выбранному) */
		$answer = $this->db->select("*", "pages", "address = '404' and language IN ('".$this->request->language."','".$this->config->DEFAULT_LANGUAGE."')");
		
		/* Фильтруем полученные страницы по выбранному языку, если такой страницы нет, в результат упадёт страница с языком по умолчанию. */
		$result = false;
		foreach($answer as $elem){
			if($elem['language'] == $this->request->language || $elem['language'] == $this->config->DEFAULT_LANGUAGE && $result == false)
				$result = $elem;
		}
		
		if($result != false)
			$this->dataPage = (object) $result;
	}
	
	/* Поиск страницы */
	private function searchPage()
	{	/*
			Подготавливаем строку для поиска страниц на основе массива корректного запроса.
			Получаем массив найденых страниц.
			Мы должны получить все страницы согласно иерархии. Если хотябы одной страницы не будет, значит по такому адресу искомая страница располагаться не может.
		*/
		$wPage = "'".implode("','", $this->request->address)."'";
		$answer = $this->db->select("*", "pages", "address IN (".$wPage.") and language = '".$this->request->language."' and status = '1'");
		if(!is_array($answer)) $answer = array();
		
		/* Пересобираем массив найденых страниц с ключами в виде адреса страницы */
		$answerEnd = array();
		foreach($answer as $elem){
			$answerEnd[$elem['address']] = $elem;
		}
		
		/*
			Даже если все страницы согласно массиву корректного запроса найдены, проверяем их последовательность
			Если последовательность верна, возвращаем массив станицы.
		*/
		$check = true;
		$category = 0;
		foreach($this->request->address as $elem){
			if(!isset($answerEnd[$elem]) || $answerEnd[$elem]['category'] != $category){
				$check = false;
				break;
			}
			$category = $answerEnd[$elem]['idPage'];
		}
		
		if($check == true)
			$this->dataPage = (object) $answerEnd[end($this->request->address)];
	}
}
?>