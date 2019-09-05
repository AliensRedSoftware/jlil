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
		$badproxymax = 5;
		if ($prealoder) {
			$form->showLoading('Возвращение...');
		}
		$api		=	capi::getApi();
		$permission	=	capi::getPermission();
		$ch = curl_init();
		//-->Установка загаловок
		$header = str::split(file_get_contents('header.conf'), "\n");
		if ($header) {
			curl_setopt($ch, 'CURLOPT_HTTPHEADER', $header);
		}
		//-->Установка прокси
		$proxy = str::split(file_get_contents('proxy'), "\n")[0];
		if ($proxy) {
			curl_setopt($ch, 'CURLOPT_PROXYTYPE', 'CURLPROXY_SOCKS5');
			curl_setopt($ch, 'CURLOPT_PROXY', $proxy);
			Logger::info("[CAPI] [Прокси] установлены => $proxy");
		}
		(new Thread(function() use ($ch, $proxy, $form, $badproxymax, $api, $permission, $req, $opt, $prealoder, $callback) { //Создание потока основного и его запуск
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
				curl_setopt($ch, 'CURLOPT_URL', "http://s2s5.space/$api/$permission/$req?$options");
				curl_exec_async($ch, function ($data) use ($badproxymax, $proxy, $form, $req, $opt, $prealoder, $callback) {
					if (!$data) {
						//-->Проверка на проксю
						if ($proxy) {
							$GLOBALS['badproxy']++;
							$badproxy = $GLOBALS['badproxy'];
							Logger::info("[CAPI] [Прокси] [$badproxy из $badproxymax] не удача подключение => $proxy");
							if($GLOBALS['badproxy'] == $badproxymax) {
								$listproxy = '';
								$count = count(str::split(file_get_contents('proxy'), "\n"));
								$lines = str::split(file_get_contents('proxy'), "\n");
								foreach ($lines as $prx) {
									if ($proxy != $prx) {
										$listproxy .= "$prx\n";
									}
								}
								Stream::putContents('proxy', trim(ltrim($listproxy)));
								$GLOBALS['badproxy'] = 0;
							}
						}
						$form->showLoading('Восстановление соедение...');
						Logger::error('[CAPI] ошибка соеденение =(');
						waitAsync (1000, function () use ($callback, $data, $req, $opt){
							capi::request($req, $opt, function ($data) use ($callback, $data) {
								if($data){
									if (is_callable($callback)) {
										$callback($data);
									}
								}
							});
						});
					} else {
						try {
							$parser = new JsonProcessor(JsonProcessor::DESERIALIZE_AS_ARRAYS);
							$data = $parser->parse($data);
							if(is_callable($callback)) {
								$GLOBALS['badproxy'] = 0;
								$form->hideLoading();
								$callback($data);
							}
						} catch (Exception $e) {
							//-->Проверка на проксю
							if ($proxy) {
								$GLOBALS['badproxy']++;
								$badproxy = $GLOBALS['badproxy'];
								Logger::info("[CAPI] [Прокси] [$badproxy из $badproxymax] не удача подключение => $proxy");
								if($GLOBALS['badproxy'] == $badproxymax) {
									$listproxy = '';
									$count = count(str::split(file_get_contents('proxy'), "\n"));
									$lines = str::split(file_get_contents('proxy'), "\n");
									foreach ($lines as $prx) {
										if ($proxy != $prx) {
											$listproxy .= "$prx\n";
										}
									}
									Stream::putContents('proxy', trim(ltrim($listproxy)));
									$GLOBALS['badproxy'] = 0;
								}
							}
							$form->showLoading('Восстановление соедение...');
							Logger::error('[CAPI] ошибка соеденение =(');
							waitAsync (1000, function () use ($callback, $data, $req, $opt){
								capi::request($req, $opt, function ($data) use ($callback, $data) {
									if($data){
										if (is_callable($callback)) {
											$callback($data);
										}
									}
								});
							});
						}
					}
				});
			} else {
				curl_setopt($ch, 'CURLOPT_URL', "http://s2s5.space/$api/$permission/$req");
				curl_exec_async($ch, function ($data) use ($badproxymax, $proxy, $form, $req, $opt, $prealoder, $callback) {
					if (!$data) {
						//-->Проверка на проксю
						if ($proxy) {
							$GLOBALS['badproxy']++;
							$badproxy = $GLOBALS['badproxy'];
							Logger::info("[CAPI] [Прокси] [$badproxy из $badproxymax] не удача подключение => $proxy");
							if($GLOBALS['badproxy'] == $badproxymax) {
								$listproxy = '';
								$count = count(str::split(file_get_contents('proxy'), "\n"));
								$lines = str::split(file_get_contents('proxy'), "\n");
								foreach ($lines as $prx) {
									if ($proxy != $prx) {
										$listproxy .= "$prx\n";
									}
								}
								Stream::putContents('proxy', trim(ltrim($listproxy)));
								$GLOBALS['badproxy'] = 0;
							}
						}
						$form->showLoading('Восстановление соедение...');
						Logger::error('[CAPI] ошибка соеденение =(');
						waitAsync (1000, function () use ($callback, $data, $req, $opt){
							capi::request($req, $opt, function ($data) use ($callback, $data) {
								if($data){
									if (is_callable($callback)) {
										$callback($data);
									}
								}
							});
						});
					} else {
						try {
							$parser = new JsonProcessor(JsonProcessor::DESERIALIZE_AS_ARRAYS);
							$data = $parser->parse($data);
							if(is_callable($callback)) {
								$GLOBALS['badproxy'] = 0;
								$form->hideLoading();
								$callback($data);
							}
						} catch (Exception $e) {
							//-->Проверка на проксю
							if ($proxy) {
								$GLOBALS['badproxy']++;
								$badproxy = $GLOBALS['badproxy'];
								Logger::info("[CAPI] [Прокси] [$badproxy из $badproxymax] не удача подключение => $proxy");
								if($GLOBALS['badproxy'] == $badproxymax) {
									$listproxy = '';
									$count = count(str::split(file_get_contents('proxy'), "\n"));
									$lines = str::split(file_get_contents('proxy'), "\n");
									foreach ($lines as $prx) {
										if ($proxy != $prx) {
											$listproxy .= "$prx\n";
										}
									}
									Stream::putContents('proxy', trim(ltrim($listproxy)));
									$GLOBALS['badproxy'] = 0;
								}
							}
							$form->showLoading('Восстановление соедение...');
							Logger::error('[CAPI] ошибка соеденение =(');
							waitAsync (1000, function () use ($callback, $data, $req, $opt){
								capi::request($req, $opt, function ($data) use ($callback, $data) {
									if($data){
										if (is_callable($callback)) {
											$callback($data);
										}
									}
								});
							});
						}
					}
				});
			}
		}))->start();
	}


	/**
	 * Повторить запрос
	 */
	static function Refresh() {

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