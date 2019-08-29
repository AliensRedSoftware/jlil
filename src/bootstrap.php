<?php
use gui, std, framework;
$packageLoader = new FrameworkPackageLoader();
$packageLoader->register();
$bootstrap = new bootstrap();
$bootstrap->start();
class bootstrap {

	/**
	 * Возвращение выбранного фреймворка
	 * @return string
	 */
	public function getFrameWork() {
		$ini = new IniStorage();
		$ini->path = 'config.ini';
		$framework = $ini->get('framework', 'skin');
		if ($framework == 'jfx') { //-->JFX
			return $framework;
		} else {//-->Стандартный фреймворк javaFX
			return 'awt';
		}
	}

	/**
	 * Запуск программы...
	 */
	public function start() {
		$App = new Application();
		$App->launch();
	}
}
