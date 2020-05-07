<?php
/*
#################################################
#  Paeone: Open Source Free System.             #
#  Authors: Yuri Nikolaev, Maksim Kudryavcev    #
#  Ver: 0.1                                     #
#################################################
*/

session_start();

/* Определяем константы */
$host = (isset($_SERVER['PHP_SELF']) ? str_replace('/index.php', '', $_SERVER['PHP_SELF']) : '');
define('HOST', $_SERVER['HTTP_HOST'].$host);

$http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://');
define('HTTP', $http);
define('ADDRESS', HTTP.HOST);

define('PATH', realpath(dirname(__FILE__)));
define('LANGUAGE', PATH.'/Language');
define('VIEWS', PATH.'/Views');
define('FUNCTIONS', PATH.'/Functions');
define('DESIGN', PATH.'/Design');
define('DOWNLOADS', PATH.'/Downloads');

/* Подключаем необходимые компоненты */
require PATH.'/Container/Container.php';

class main extends Container
{
	function __construct(){
		parent::__construct();
		
		/* Выводим результат */
		echo $this->result;
	}
}

/* Объявляем класс */
new main();

?>