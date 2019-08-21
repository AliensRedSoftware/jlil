<?php
namespace app\forms;
use std, gui, app, framework;
use app\modules\capi as api;

class MainForm extends AbstractForm {

	/**
     * @event show
     */
	function doShow(UXWindowEvent $e = null) {
		$this->vbox->style	=	'-fx-border-color:#333333;';
		//-->API...
		api::setApi('capi');
		api::setPermission('system');
		api::getDot(function ($dot) {
			switch ($dot['status']) {
				case 200:
					foreach ($dot['response'] as $val) {
						$this->dot->items->add($val);		//Добавление точек
					}
					$this->dot->selectedIndex = 0;
				break;
			}
		});
	}

	/**
     * @event space.action
     */
    function doSpaceAction(UXEvent $e = null) {
		api::getMsg($e->sender->selected, function ($data) {
			switch ($data['status']) {
				case 200:
					$this->threads->items->clear();
					foreach ($data['response'] as $val => $key) {
						$this->threads->items->add($val);
					}
					if (!$this->threads->selected) {
						$this->threads->selectedIndex = 0;
					}
				break;
			}
		});
    }

	/**
     * @event dot.action
     */
    function doDotAction(UXEvent $e = null) {
		api::getSpace($e->sender->selected, function ($data) {
			switch ($data['status']) {
				case 200:
					$this->space->items->clear();
					foreach ($data['response'] as $val) {
						$this->space->items->add($val);
					}
					if (!$this->space->selected) {
						$this->space->selectedIndex = 0;
					}
				break;
			}
		});
    }

	/**
     * @event threads.action
     */
    function doThreadsAction(UXEvent $e = null) {
		api::getMsg($this->space->selected, function ($data) {
			switch ($data['status']) {
				case 200:
					$msgArr = [];
					foreach ($data['response'] as $val) {
						foreach ($val['msg'] as $msg) {
							array_push($msgArr, trim($msg['txt']));
						}
					}
					$this->container->content = $this->getMsg($msgArr);
				break;
			}
		});
    }

	/**
     * @event send.globalKeyDown-Enter
     */
	function doSendGlobalKeyDownEnter(UXKeyEvent $e = null) {
		api::sendThreads($this->threads->selected, $e->sender->text, function ($data) {
			switch ($data['status']) {
				case 200:
					$this->doThreadsAction();
				break;
				default:
					$this->doThreadsAction();
				break;
			}
		});
		$e->sender->clear();
	}

	/**
     * @event theme.action
     */
    function doThemeAction(UXEvent $e = null) {
		$this->clearStylesheets();
        switch($e->sender->selectedIndex) {
			case 1:
				$this->addStylesheet('app/.theme/bootstrap3.fx.css');
			break;
			case 2:
				$this->addStylesheet('app/.theme/bootstrap2.fx.css');
			break;
			case 3:
				$this->addStylesheet('app/.theme/dark.fx.css');
			break;
			case 4:
				$this->addStylesheet('app/.theme/FlatBee.fx.css');
			break;
			case 5:
				$this->addStylesheet('app/.theme/MistSilver.fx.css');
			break;
        }
    }

	/**
	 * Возвращаем созданное сообщение
	 * ------------------------------
	 */
	public function getMsg (array $msg) {
		$this->vbox->children->clear();
		foreach ($msg as $val) {
			if (!is_array($val)) {
				$panel	=	new UXPanel();
				$label	=	new UXLabel($val);
				$label->padding = [5, 5, 5, 5];
				$panel->add($label);
				$this->vbox->add($panel);
			}
		}
		return $this->vbox;
	}

	/**
	 * Возвращает собранный контайнер
	 * -------------------------------
	 */
	public function getContainer (UXNode $arr) {
		$vbox = new UXVbox();
		foreach ($arr as $val) {
			$vbox->add($val);
		}
	}
}
