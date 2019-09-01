<?php
namespace app\forms;

use std, gui, framework;
use action\Element;
use app\modules\capi as api;

class MainForm extends AbstractForm {

	public $selectedFrameWork;
	public $changedFramework;

	public function construct() {
		$bootstrap	=	new \\\bootstrap();
		$Name		=	$this->getName();
		$framework	=	$bootstrap->getFrameWork();
		$this->selectedFrameWork = $framework;
		Logger::info("[Фреймворк] [$Name] Загружен - $framework =)");
		return "res://app/fxml/$framework/" . $this->getName();
	}

	/**
     * @event framework.action
     */
	function doFrameworkAction(UXEvent $e = null) {
		$framework = $this->selectedFrameWork;;
		if ($this->changedFramework && $e->sender->selected != $framework) {
			if(uiconfirm('Вы точно хотите изменить ?)')) {
				$ini = new IniStorage();
				$ini->path = 'config.ini';
				$ini->set('framework', $e->sender->selected, 'skin');
				$this->selectedFrameWork = $framework;
				$this->free();
				app()->getForm(skin)->free();
				app()->showForm($this->getName());
			} else {
				$this->framework->selected = $framework;
			}
		} else {
			$this->changedFramework = true;
		}
	}

	/**
     * @event framework.construct
     */
    function doFrameworkConstruct(UXEvent $e = null) {
		$this->framework->selected = $this->selectedFrameWork;
		$this->changedFramework = true;
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
     * @event show
     */
	function doShow(UXWindowEvent $e = null) {
		$this->vbox->style	=	'-fx-border-color:#333333;';
		//-->API...
		api::setApi('capi');
		api::setPermission('system');
		api::getDot(true, function ($dot) {
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
     * @event copythreads.action
     */
	function doCopythreadsAction(UXEvent $e = null) {
		$threads = $this->threads->selected;
		UXClipboard::setText($threads);
		$this->toast("Успешно установлен в буфер обмена =>$threads");
	}

	/**
     * @event copySpace.action
     */
	function doCopySpaceAction(UXEvent $e = null) {
		$space = $this->space->selected;
		UXClipboard::setText($space);
		$this->toast("Успешно установлен в буфер обмена =>$space");
	}

	/**
     * @event copyDot.action
     */
	function doCopyDotAction(UXEvent $e = null) {
		$dot = $this->dot->selected;
		UXClipboard::setText($dot);
		$this->toast("Успешно установлен в буфер обмена =>$dot");
	}

	/**
     * @event img.action
     */
	function doImgAction(UXEvent $e = null) {
		if ($e->sender->selected) {
			$this->update->selected = false;
		}
		$this->doThreadsAction();
	}

	/**
     * @event update.action
     */
    function doUpdateAction(UXEvent $e = null) {
		if ($this->img->selected) {
			$e->sender->selected = false;
		} else {
			$this->doThreadsAction();
		}
	}

	/**
     * @event space.action
     */
	function doSpaceAction(UXEvent $e = null) {
		api::getMsg($e->sender->selected, $this->dot->selected, false, true, function ($data) {
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
		api::getSpace($e->sender->selected, true, function ($data) {
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
    function doThreadsAction(UXEvent $e = null, $prealoder = true) {
		api::getMsg($this->space->selected, $this->dot->selected, $this->threads->selected, $prealoder, function ($data) use ($e) {
			switch ($data['status']) {
				case 200:
					$this->container->content = $this->getMsg($data['response']['msg']);
					if ($this->update->selected) {
						$this->doThreadsAction($e, false);
					}
				break;
			}
		});
    }

	/**
     * @event newSkin.action
     */
	function doNewSkinAction(UXEvent $e = null) {
		$this->showPreloader('Ожидание ответа от формы...');
		$this->form('skin')->showAndWait();
		$this->hidePreloader();
	}

    /**
     * @event theme.construct
     */
	function doThemeConstruct(UXEvent $e = null) {
		$ini = new IniStorage();
		$ini->path = 'config.ini';
		$selected = $ini->get('selected', 'skin');
		foreach ($this->form('skin')->getSkins() as $skin) {
			$e->sender->items->add($skin);
			if ($selected == $skin) {
				$e->sender->selected = $selected;
			}
		}
		if (!$e->sender->selected) {
			$e->sender->selectedIndex = 0;
		}
		if ($e->sender->selectedIndex == 0) {
			$this->removeSkin->enabled = false;
		}
	}

	/**
     * @event send.globalKeyDown-Enter
     */
	function doSendGlobalKeyDownEnter(UXKeyEvent $e = null) {
		api::sendThreads($this->threads->selected, $e->sender->text, true, function ($data) {
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
		$this->form('skin')->clearStylesheets();
		if ($e->sender->selectedIndex > 0) {
			$ini = new IniStorage();
			$ini->path = 'config.ini';
			$this->removeSkin->enabled = true;
			//-->Подгрузка во внутрь
			$jar = new \\\bundle\zip\ZipFileScript();
			$jar->path = System::getProperties()['java.class.path'];
			if (!$jar->has('app' . fs::separator() . 'fxml' . fs::separator() . $this->selectedFrameWork . fs::separator() . 'skins' . fs::separator() . $e->sender->selected . fs::separator() . $e->sender->selected . '.fx.css')) {
				if(uiconfirm('Потребуется перезапуск jlil...')) {
					$skins = fs::scan('.' . fs::separator() . "skins" . fs::separator() . $this->selectedFrameWork . fs::separator() . $e->sender->selected . fs::separator());
					foreach ($skins as $file) {
						$skn = str::split($file, fs::separator());
						$skin = $skn[count($skn) - 1];
						$jar->add('app' . fs::separator() . 'fxml' . fs::separator() . $this->selectedFrameWork . fs::separator() . 'skins' . fs::separator() . $e->sender->selected . fs::separator() . $skin, '.' . fs::separator() . 'skins' . fs::separator() . $this->selectedFrameWork . fs::separator() . $e->sender->selected . fs::separator() . $skin, -0);
					}
					$ini->set('selected', $e->sender->selected, 'skin');
					//-->Перезагрузка формы или программы чтобы
					execute('java -jar ' . $jar->path);
					app()->shutdown();
				} else {
					if ($ini->get('selected', 'skin')) {
						$e->sender->selected = $ini->get('selected', 'skin');
					} else {
						$e->sender->selectedIndex = 0;
					}
				}
			} else {
				$this->addStylesheet('app' . fs::separator() . 'fxml' . fs::separator() . $this->selectedFrameWork . fs::separator() . 'skins' . fs::separator() . $e->sender->selected . fs::separator() . $e->sender->selected . ".fx.css");
				$this->form('skin')->addStylesheet('app' . fs::separator() . 'fxml' . fs::separator() . $this->selectedFrameWork . fs::separator() . 'skins' . fs::separator() . $e->sender->selected . fs::separator() . $e->sender->selected . ".fx.css");
				$ini->set('selected', $e->sender->selected, 'skin');
			}
		} else {
			$this->removeSkin->enabled = false;
		}
    }

	/**
     * @event removeSkin.action
     */
    function doRemoveSkinAction(UXEvent $e = null) {
		if(uiConfirm('Данный скин будет удален навсегда => ' . $this->theme->selected)) {
			/*
			$jar = new \\\bundle\zip\ZipFileScript();
			$jar->path = System::getProperties()['java.class.path'];
			$jar->read('app/fxml/' . $this->selectedFrameWork . '/skins/' . $this->theme->selected, function ($reader) {
				pre($reader);
			});
			*/
			fs::clean("skins" . fs::separator() . $this->selectedFrameWork . fs::separator() . $this->theme->selected);
			fs::delete("skins" . fs::separator() . $this->selectedFrameWork . fs::separator() . $this->theme->selected);
			$this->theme->items->remove($this->theme->selected);
			$this->toast('Успешно :)');
		}
	}

	/**
	 * Возвращаем созданное сообщение
	 * ------------------------------
	 * msg		-	Сообщение
	 */
	public function getMsg ($msg) {
		$this->vbox->children->clear();
		foreach ($msg as $data) {
			$txt	=	trim($data['txt']);
			$photo	=	$data['file']['photo'];
			$panel	=	new UXPanel();
			$panel->maxWidth = 0;
			//-->Текст
			if (!empty($txt)) {
				$label	=	new UXLabel($txt);
				$label->wrapText = true;
				$label->padding = [5, 5, 5, 5];
				$panel->add($label);
			}
			//-->Загрузка картинки
			if ($this->img->selected) {
				$grid			=	new UXFlowPane();
				$grid->alignment=	"TOP_LEFT";
				$grid->hgap		=	5;
				$grid->vgap		=	5;
				$grid->padding	=	[5, 5, 5, 5];
				foreach ($photo as $img) {
					$img = trim($img);
					if ($img) {
						$this->showPreloader('Загрузка изоброжение...');
						$border				=	new UXPanel();
						$foo				=	new UXImageArea();
						$foo->position		=	[5, 5];
						$foo->stretch		=	true;
						$foo->proportional	=	true;
						$border->maxHeight = 0;
						$border->maxWidth = 0;
						Element::loadContentAsync($foo, $img, function () use ($border, $foo) {
							$foo->size			=	[320, 320];
							$this->hidePreloader();
							$border->add($foo);
						});
						$grid->add($border);
					}
				}
				$panel->add($grid);
			}
			if ($panel->children->count >= 2) {
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
