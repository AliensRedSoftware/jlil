<?php
use gui, app, std, framework; // import JavaFX classes from jphp-gui-ext

UXApplication::runLater(function () {
	//-->Создание виджетов
    $form					=	new UXForm();		//create new form
	$vboxCenter				=	new UXVbox();		//низ
	$vboxTop				=	new UXVbox();		//Вверх
	$dotItem				=	new UXComboBox();	//Ящик с точками
	$spaceItem				=	new UXComboBox();	//Ящик Пространство
	$ThreadsItem			=	new UXComboBox();	//Ящик с нитями
	$textArea				=	new UXTextArea();	//Текст
	$send					=	new UXTextField();	//Поля отправки
	//-->API...
	new Module('src/capi/capi.php');					//Импорт capi
	setApi('capi');										//Имя api
	setPermission('system');							//Привилегия api
	getDot(function ($dot) use ($dotItem) {
		switch ($dot['status']) {
			case 200:
				foreach ($dot['response'] as $val) {
					$dotItem->items->add($val);		//Добавление точек
				}
				$dotItem->selectedIndex = 0;
			break;
		}
	});
	//-->Обработка
	$dotItem->on('action', function ($e = null) use ($spaceItem) {
		getSpace($e->sender->selected, function ($data) use ($spaceItem) {
			switch ($data['status']) {
				case 200:
					$spaceItem->items->clear();
					foreach ($data['response'] as $val) {
						$spaceItem->items->add($val);
					}
				break;
			}
		});
	});
	$spaceItem->on('action', function ($e = null) use ($ThreadsItem) {
		getMsg($e->sender->selected, function ($data) use ($ThreadsItem) {
			switch ($data['status']) {
				case 200:
					$ThreadsItem->items->clear();
					foreach ($data['response'] as $val => $key) {
						$ThreadsItem->items->add($val);
					}
				break;
			}
		});
	});
	$ThreadsItem->on('action', function ($e = null) use ($spaceItem, $textArea) {
		getMsg($spaceItem->selected, function ($data) use ($textArea) {
			switch ($data['status']) {
				case 200:
					$textArea->clear();
					foreach ($data['response'] as $val) {
						//$textArea->appendText("$val:\n");
						foreach ($val['msg'] as $mt) {
							$textArea->appendText($mt['txt']);
						}
					}
				break;
			}
		});
	});
	//-------------------------------------------------------------------------
	//-->Настройки формы
	$form->title				=	'jlil 1.0.0';	//Название формы
	$form->size					=	[320, 480];		//Размер
	//-->Отправка
	$send->promptText			=	'Сообщение...';	//Подсказка
	$send->rightAnchor			=	1;				//Растягивание слева
	$send->leftAnchor			=	1;				//Растягивание справо
	$send->bottomAnchor			=	1;				//Растягивание вниз
	//-->Низ сетка
	$vboxCenter->size			=	$form->size;  //Размер
	$vboxCenter->alignment		=	'BOTTOM_LEFT';	//Расположение (снизу слева)
	$vboxCenter->topAnchor		=	1;				//Растягивание сверху
	$vboxCenter->rightAnchor	=	1;				//Растягивание справо
	$vboxCenter->leftAnchor		=	1;				//Растягивание слева
	$vboxCenter->bottomAnchor	=	1;				//Растягивание вниз
	//-->Текст
	$textArea->size				=	$form->size;  //Размер
	$textArea->topAnchor		=	1;				//Растягивание сверху
	$textArea->rightAnchor		=	1;				//Растягивание справо
	$textArea->leftAnchor		=	1;				//Растягивание слева
	$textArea->bottomAnchor		=	1;				//Растягивание вниз
	//-->Вверх сетка
	$vboxTop->size				=	$form->size;  //Размер
	$vboxTop->alignment			=	'TOP_LEFT';		//Расположение (Сверху слева)
	$vboxTop->topAnchor			=	1;				//Растягивание сверху
	$vboxTop->rightAnchor		=	1;				//Растягивание справо
	$vboxTop->leftAnchor		=	1;				//Растягивание слева
	$vboxTop->bottomAnchor		=	1;				//Растягивание вниз
	//-->Точки
	$dotItem->rightAnchor		=	1;				//Растягивание справо
	$dotItem->leftAnchor		=	1;				//Растягивание слева
	//-->Добавление на форму
	$form->add($vboxTop);
	//-->Добавление на ввернию сетку
	$vboxTop->add($dotItem);
	$vboxTop->add($spaceItem);
	$vboxTop->add($ThreadsItem);
	$vboxTop->add($vboxCenter);
	//-->Добавление на нижнию сетку
	$vboxCenter->add($textArea);
	$vboxCenter->add($send);
	//-->Показать форму
    $form->show(); // show form
	//-->Обновление получение...
	Timer::every(1, function () use ($form, $vboxCenter, $dotItem, $spaceItem, $ThreadsItem, $textArea) { //1 = 0.1сек
		$vboxCenter->size	=	$form->size;
		$textArea->size		=	$form->size;
		$dotItem->width		=	$form->width;
		$spaceItem->width	=	$form->width;
		$ThreadsItem->width =	$form->width;
	});
});
