<?php
namespace app\modules;
use std, gui, framework, app;

class capi {

	/**
	 * Установить имя api
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
 	 */
	static function setPermission ($perm = 'system') {
		$GLOBALS['__API_PERMISSION'] = $perm;
	}

	/**
 	 * Возвращаем имя привилегий
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
 	 * request	-	Имя запроса
	 * opt 		-	Параметры запроса
	 */
	static function request ($req = 'getDot', $opt = [], $callback = null) {
		$form = app()->getForm(MainForm);
		$form->showPreloader('Возвращение...');
		$api		=	capi::getApi();
		$permission	=	capi::getPermission();
		(new Thread(function() use ($form, $api, $permission, $req, $opt, $callback) { //Создание потока основного и его запуск
			uiLater(function() use ($form, $api, $permission, $req, $opt, $callback) {
				if ($opt) {
					unset($options);
					foreach ($opt as $func => $val) {
						$i++;
						$options .= "$func=$val";
						if (count($opt) != $i) {
							$options .= '&';
						}
					}
					$r = stream::getContents("http://s2s5.space/$api/$permission/$req?$options");
				} else {
					$r = stream::getContents("http://s2s5.space/$api/$permission/$req");
				}
				capi::setRequest($r);
				$form->hidePreloader();
				if(is_callable($callback)) {
					$callback($r);
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
	 * --------------------
 	 * @return string
 	 */
	static function getDot ($callback = null) {
		capi::request('getDot', [], function ($data) use ($callback) {
			echo "[getDot][Возвращение] => $data";
			if (is_callable($callback)) {
				$callback(json_decode($data, true));
			}
		});
	}

	/**
 	 * Возвращаем все пространство (callback)
 	 * --------------------
 	 * @return string
 	 */
	static function getSpace ($dot = null, $callback = null) {
		capi::request('getSpace', ['dot' => $dot], function ($data) use ($callback) {
			echo "[getSpace][Возвращение] => $data";
			if (is_callable($callback)) {
				$callback(json_decode($data, true));
			}
		});
	}

	/**
 	 * Возвращаем все нити в сообщение (callback)
 	 * --------------------
 	 * @return string
 	 */
	static function getMsg ($space = null, $callback = null) {
		capi::request('getMsg', ['space' => $space], function ($data) use ($callback) {
			echo "[getMsg][Возвращение] => $data";
			if (is_callable($callback)) {
				$callback(json_decode($data, true));
			}
		});
	}

	/**
	 * Отправка сообщение в нить (callback)
	 */
	static function sendThreads ($threads = null, $txt, $callback = null) {
		capi::request('SendMsg', ['threads' => $threads, 'txt' => $txt], function ($data) use ($callback) {
			echo "[SendMsg][Возвращение] => $data";
			if (is_callable($callback)) {
				$callback(json_decode($data, true));
			}
		});
	}
}