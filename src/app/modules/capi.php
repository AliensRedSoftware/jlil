<?php
namespace app\modules;

use std, gui, framework;
use php\format\JsonProcessor;
use Exception;

/**
 * capi - работа с модулем xmessage
 * --------------------------------
 * @ver - 1.1
 */
class capi {

	/**
	 * Установить имя api
	 * ------------------
	 * name			-	Имя api
 	 */
	static function setApi ($name = 'capi') {
		$GLOBALS['__API_NAME']	=	$name;
	}

	/**
 	 * Возвращаем имя api
 	 */
	static function getApi () {
		if (!$GLOBALS['__API_NAME']) {
			return false;
		} else {
			return $GLOBALS['__API_NAME'];
		}
	}

	/**
 	 * Установить привилегию на выполнение api
 	 * ----------------------------------------
	 * perm			-	Название
 	 */
	static function setPermission ($perm = 'system') {
		$GLOBALS['__API_PERMISSION'] = $perm;
	}

	/**
 	 * Возвращаем имя привилегий
	 * -------------------------
	 * @return string
 	 */
	static function getPermission () {
		if (!$GLOBALS['__API_PERMISSION']) {
			return false;
		} else {
			return $GLOBALS['__API_PERMISSION'];
		}
	}

	/**
	 * Создает запрос (callback)
	 * -------------------------
 	 * request		-	Имя запроса
	 * opt			-	Параметры запроса
	 * prealoder	-	Статус загрузки
	 * -------------------------
	 * @return callback
	 */
	static function request ($req = 'getDot', $opt = [], $prealoder = true, $callback = null) {
		$form = app()->getForm(MainForm);
		if ($prealoder) {
			$form->dot->enabled		=	false;
			$form->space->enabled	=	false;
			$form->threads->enabled	=	false;
			$form->send->enabled	=	false;
			$form->showPreloader('Возвращение...');
		}
		$api		=	capi::getApi();
		$permission	=	capi::getPermission();
		(new Thread(function() use ($form, $api, $permission, $req, $opt, $prealoder, $callback) { //Создание потока основного и его запуск
			if ($opt) {
				unset($options);
				foreach ($opt as $func => $val) {
					if (trim($val)) {
						$i++;
						$val = str::replace($val, ' ', '+');
						$options .= "$func=$val";
						if (count($opt) != $i) {
							$options .= '&';
						}
					}
				}
				$r = fs::get("http://s2s5.space/$api/$permission/$req?$options");
			} else {
				$r = fs::get("http://s2s5.space/$api/$permission/$req");
			}
			capi::setRequest($r);
		 	uiLater(function() use ($form, $r, $req, $opt, $prealoder, $callback) {
				try {
					$parser = new JsonProcessor(JsonProcessor::DESERIALIZE_AS_ARRAYS);
					$r = $parser->parse($r);
					if(is_callable($callback)) {
						$form->hidePreloader();
						if ($prealoder) {
							$form->dot->enabled		=	true;
							$form->space->enabled	=	true;
							$form->threads->enabled	=	true;
							$form->send->enabled	=	true;
						}
						$callback($r);
					}
				} catch (Exception $e) {
					$form->showPreloader('Восстановление соедение...');
					waitAsync (1000, function () use ($callback, $r, $req, $opt){
						capi::request($req, $opt, function ($data) use ($callback, $r){
							if($data){
								if (is_callable($callback)) {
									$callback($data);
								}
							}
						});
					});
				}
		 	});
		 }))->start();
	}

	/**
 	 * Установить запрос
 	 * -----------------
 	 * req - Значение
 	 */
	static function setRequest ($req) {
		$GLOBALS['__API_DATA'] = $req;
	}

	/**
 	 * Возвращаем запрос
 	 */
	static function getRequest () {
		if ($GLOBALS['__API_DATA']) {
			return $GLOBALS['__API_DATA'];
		} else {
			return false;
		}
	}

	/**
 	 * Возвращаем все точки (callback)
	 * -------------------------------
	 * prealoder	-	Статус загрузки
	 * -------------------------------
 	 * @return callback
 	 */
	static function getDot ($prealoder = true, $callback = null) {
		capi::request('getDot', [], $prealoder, function ($data) use ($callback) {
			if (is_callable($callback)) {
				$callback($data);
			}
		});
	}

	/**
 	 * Возвращаем все пространство (callback)
 	 * --------------------------------------
	 * dot			-	Точка
	 * prealoder	-	Статус загрузки
 	 * @return callback
 	 */
	static function getSpace ($dot = null, $prealoder = true, $callback = null) {
		capi::request('getSpace', ['dot' => $dot], $prealoder, function ($data) use ($dot, $callback) {
			if (is_callable($callback)) {
				$callback($data);
			}
		});
	}

	/**
 	 * Возвращаем все нити в сообщение или определенную (callback)
 	 * -----------------------------------------------------------
	 * space		-	Пространство
	 * dot			-	Точка
	 * selected		-	Выбранная нить
	 * prealoder	-	Статус загрузки
	 * -----------------------------------------------------------
 	 * @return callback
 	 */
	static function getMsg ($space, $dot, $selected = false, $prealoder = true, $callback = null) {
		capi::request('getMsg', ['space' => $space, 'dot' => $dot, 'selected' => $selected], $prealoder, function ($data) use ($callback) {
			if (is_callable($callback)) {
				$callback($data);
			}
		});
	}

	/**
	 * Отправка сообщение в нить (callback)
	 * ------------------------------------
	 * threads		-	ид нити
	 * txt			-	Текст
	 * prealoder	-	Статус загрузки
	 * ------------------------------------
	 * @return callback
	 */
	static function sendThreads ($threads = null, $txt, $prealoder = true, $callback = null) {
		$txt = str::decode($txt, 'UTF-8');
		capi::request('SendMsg', ['threads' => $threads, 'txt' => $txt], $prealoder, function ($data) use ($callback) {
			if (is_callable($callback)) {
				$callback($data);
			}
		});
	}
}