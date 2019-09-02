<?php
namespace app\forms;

use std, gui, framework;
use Exception;

class skin extends AbstractForm {

	public function construct() {
		$bootstrap	=	new \\\bootstrap();
		$Name		=	$this->getName();
		$framework	=	$bootstrap->getFrameWork();
		$this->selectedFrameWork = $framework;
		Logger::info("[Фреймворк] [$Name] Загружен - $framework =)");
		return "res://app/fxml/$framework/" . $this->getName();
	}

	/**
     * @event showing
     */
    function doShowing(UXWindowEvent $e = null) {
		switch ($this->selectedFrameWork) {
			case 'jfx':
				$e->sender->style = 'TRANSPARENT';
				$e->sender->layout->backgroundColor = UXColor::of('#00000000');
				$e->sender->transparent = true;
			break;
			default:
			break;
		}
	}

	/**
     * @event skin.keyDown-Enter
     */
    function doSkinKeyDownEnter(UXKeyEvent $e = null) {
		$bootstrap = new \\\bootstrap();
		$framework = $bootstrap->getFrameWork();
		if ($e->sender->text) {
			$skins = str::split($e->sender->text, fs::separator());
			$skn = str::split($skins[count($skins) - 1], '.');
			foreach ($this->getSkins() as $skin) {
				if ($skin == $skn[0]) {
					UXDialog::showAndWait("Скин уже существует => $skin");
					return;
				}
			}
			try {
				if ($skn[1] != 'fx' && $skn[1] != 'css') {
					UXDialog::showAndWait('Стиль не найден :(', 'ERROR');
					return;
				}
				mkdir('.' . fs::separator() . "skins" . fs::separator() . $framework . fs::separator() . $skn[0], 0777);
				fs::copy($e->sender->text, '.' . fs::separator() . "skins" . fs::separator() . $framework . fs::separator() . $skn[0] . fs::separator() . $skn[0] . '.fx.css');
				$this->form('MainForm')->toast("Скин успешно установлен => " . $skn[0]);
				$this->form('MainForm')->theme->items->add($skn[0]);
				$this->form('MainForm')->theme->selected = $skn[0];
				if ($this->list->items->isNotEmpty()) {
					foreach ($this->list->items->toArray() as $val) {
						$url	=	$val;
						$val	=	str::split($val, fs::separator());
						fs::copy($url, '.' . fs::separator() . "skins" . fs::separator() . $framework . fs::separator() . $skn[0] . fs::separator() . $val[count($val) - 1]);
					}
				}
				$this->hide();
			} catch (Exception $e) {
				fs::delete('.' . fs::separator() . "skins" . fs::separator() . $framework . fs::separator() . $skn[0]);
				UXDialog::showAndWait('Стиль не найден :(', 'ERROR');
			}
		} else {
			$this->hide();
		}
	}

	/**
     * @event hide
     */
	function doHide(UXWindowEvent $e = null) {
		$this->skin->clear();
		$this->list->items->clear();
	}

	/**
     * @event close.action
     */
    function doCloseAction(UXEvent $e = null) {
		app()->shutdown();
	}

	/**
     * @event add.action
     */
	function doAddAction(UXEvent $e = null) {
		foreach ($this->list->items->toArray() as $val) {
			if ($val == $this->zavisim->text) {
				$this->toast('Данная зависимость уже существует!');
				return;
			}
		}
		if (trim($this->zavisim->text)) {
			$this->list->items->add($this->zavisim->text);
			$this->toast('Успешно :)');
		} else {
			$this->toast('Данная зависимость пустое!');
		}
		$this->zavisim->clear();
	}

	/**
     * @event zavisim.keyDown-Enter
     */
	function doZavisimKeyDownEnter(UXKeyEvent $e = null) {
		$this->doAddAction();
	}

	/**
     * @event clear.action
     */
	function doClearAction(UXEvent $e = null) {
		$this->toast('Успешно :)');
		$this->list->items->clear();
	}

	/**
	 * Возвращаем скины
	 * ----------------
	 * @return Array
	 */
	public function getSkins() {
		$bootstrap = new \\\bootstrap();
		$framework = $bootstrap->getFrameWork();
		$arr = [];
		$files = fs::scan('.' . fs::separator() . "skins" . fs::separator() . $framework . fs::separator(), ['excludeFiles' => true]);
		foreach ($files as $file) {
			$skins = str::split($file, fs::separator());
			array_push($arr, $skins[count($skins) - 1]);
		}
		return $arr;
	}

}