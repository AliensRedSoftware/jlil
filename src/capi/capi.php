<?php
use std, gui, framework, app;
use facade\Json;
/**
 * Установить имя api
 */
public function setApi ($name = 'capi') {
	$GLOBALS['__API_NAME']	=	$name;
}

/**
 * Возвращаем имя api
 */
public function getApi () {
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
public function setPermission ($perm = 'system') {
	$GLOBALS['__API_PERMISSION'] = $perm;
}

/**
 * Возвращаем имя привилегий
 */
public function getPermission () {
	if (!$GLOBALS['__API_PERMISSION']) {
		return false;
	} else {
		return $GLOBALS['__API_PERMISSION'];
	}
}

/**
 * Создает запрос (callback)
 * --------------
 * request	-	Имя запроса
 * opt 		-	Параметры запроса
 */
public function request ($req = 'getDot', $opt = [], $callback = null) {
	$GLOBALS['__APP_PRELOADING'] = true;
	$api		=	getApi();
	$permission	=	getPermission();
	(new Thread(function() use ($api, $permission, $req, $opt, $callback) { //Создание потока основного и его запуск
		if ($opt) {
			unset($options);
			foreach ($opt as $func => $val) {
				$i++;
				$options .= "$func=$val";
				if (count($opt) != $i) {
					$options .= '&';
				}
			}
			$r = file_get_contents("http://s2s5.space/$api/$permission/$req?$options");
		} else {
			$r = file_get_contents("http://s2s5.space/$api/$permission/$req");
		}
		setRequest($r);
		if(is_callable($callback)) {
			$callback($r);
		}
	}))->start();
}

/**
 * Установить запрос
 * -----------------
 * req - Значение
 */
public function setRequest ($req) {
	$GLOBALS['__API_DATA'] = $req;
	$GLOBALS['__APP_PRELOADING'] = false;
}

/**
 * Возвращаем запрос
 */
public function getRequest () {
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
public function getDot ($callback = null) {
	request('getDot', [], function ($data) use ($callback) {
		echo "[getDot][Возвращение] => $data";
		$GLOBALS['__APP_PRELOADING'] = false;
		if (is_callable($callback)) {
			$callback(json_decode($data, true));
		}
	});
}
/**
/**
 * Возвращаем все пространство (callback)
 * --------------------
 * @return string
 */
public function getSpace ($dot = null, $callback = null) {
	request('getSpace', ['dot' => $dot], function ($data) use ($callback) {
		echo "[getSpace][Возвращение] => $data";
		$GLOBALS['__APP_PRELOADING'] = false;
		if (is_callable($callback)) {
			$callback(json_decode($data, true));
		}
	});
}/**
 * Возвращаем все нити в сообщение (callback)

/**
 * Возвращаем все нити в сообщение (callback)
 * --------------------
 * @return string
 */
public function getMsg ($space = null, $callback = null) {
	request('getMsg', ['space' => $space], function ($data) use ($callback) {
		echo "[getMsg][Возвращение] => $data";
		$GLOBALS['__APP_PRELOADING'] = false;
		if (is_callable($callback)) {
			$callback(json_decode($data, true));
		}
	});
}