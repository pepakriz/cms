<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms\AdminModule\Insite;

use Venne\Cms\AdminModule\PageFormService;
use Venne\Cms\SideComponents\PagesControl;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InsiteControl extends \Venne\System\UI\Control
{

	/** @var \Venne\Cms\AdminModule\PageFormService */
	private $pageFormService;

	/** @var integer */
	private $pageId;

	/** @var boolean */
	private $preview = false;

	/**
	 * @param \Venne\Cms\AdminModule\PageFormService $pageFormService
	 * @param integer $pageId
	 */
	public function __construct(PageFormService $pageFormService, $pageId)
	{
		$this->pageFormService = $pageFormService;
		$this->pageId = $pageId;
	}

	/**
	 * @param boolean $preview
	 */
	public function setPreview($preview)
	{
		$this->preview = (bool) $preview;
	}

	public function handleIframe()
	{
		$this->template->iframe = true;
		$this->template->render();
		$this->getPresenter()->terminate();
	}

	public function render()
	{
		$this->template->preview = $this->preview;
		$this->template->pageId = $this->pageId;
		parent::render();
	}

	protected function createComponentForm()
	{
		$control = $this->pageFormService->getFormFactory($this->pageId)->create();

		$control->onSuccess[] = function () {
			$this->flashMessage('Page has been saved', 'success');
			$this->redirect('this');
			$this->redrawControl('content');

			$sideComponent = $this->getPresenter()->getSideComponents()->getSideComponent();
			if ($sideComponent instanceof PagesControl) {
				$sideComponent->redrawContent();
			}
		};

		return $control;
	}

	/**
	 * @return \Venne\System\Components\CssControl
	 */
	protected function createComponentAdminCss()
	{
		return $this->getPresenter()->getComponent('adminCss');
	}

	/**
	 * @return \Venne\System\Components\JsControl
	 */
	protected function createComponentAdminJs()
	{
		return $this->getPresenter()->getComponent('adminJs');
	}

}
