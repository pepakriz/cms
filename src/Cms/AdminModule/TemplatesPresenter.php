<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Admin\Content;

use Venne\Cms\TemplateManager;
use Venne\Packages\Helpers;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class TemplatesPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/** @persistent */
	public $key;

	/** @var TemplateManager */
	private $templateManager;

	/** @var array */
	private $layouts;

	/** @var LayoutFormFactory */
	private $layoutFormFactory;

	/** @var LayouteditFormFactory */
	private $layouteditFormFactory;

	/** @var OverloadFormFactory */
	private $overloadFormFactory;

	/** @var Helpers */
	private $moduleHelpers;

	/**
	 * @param TemplateManager $templateManager
	 * @param LayoutFormFactory $layoutForm
	 * @param LayouteditFormFactory $layouteditForm
	 * @param OverloadFormFactory $overloadFormFactory
	 * @param Helpers $moduleHelpers
	 */
	public function __construct(
		TemplateManager $templateManager,
		LayoutFormFactory $layoutForm,
		LayouteditFormFactory $layouteditForm,
		OverloadFormFactory $overloadFormFactory,
		Helpers $moduleHelpers
	) {
		$this->templateManager = $templateManager;
		$this->layoutFormFactory = $layoutForm;
		$this->layouteditFormFactory = $layouteditForm;
		$this->overloadFormFactory = $overloadFormFactory;
		$this->moduleHelpers = $moduleHelpers;
	}

	protected function getScannedLayouts()
	{
		if ($this->layouts === null) {
			$this->layouts = array();

			foreach ($this->context->parameters['modules'] as $name => $item) {
				$this->layouts[$name] = $this->templateManager->getLayouts($name);
			}
		}

		return $this->layouts;
	}

	protected function createComponentForm()
	{
		$form = $this->layouteditFormFactory->invoke();
		$form->onSuccess[] = $this->formSuccess;

		return $form;
	}

	protected function createComponentOverloadForm()
	{
		$form = $this->overloadFormFactory->invoke();
		$form->onSuccess[] = $this->overloadFormSuccess;

		return $form;
	}

	public function overloadFormSuccess(Form $form)
	{
		if ($form->isSubmitted() === $form->getSaveButton() && !$form->errors) {
			$this->redirect('default');
		}
	}

	public function formSuccess($form)
	{
		$this->flashMessage($this->translator->translate('Layout has been added.'), 'success');

		if (!$this->isAjax()) {
			$this->redirect('edit', array('key' => $form->data));
		}
		$this->invalidateControl('content');
		$this->payload->url = $this->link('edit', array('key' => $form->data));
		$this->setView('edit');
		$this->changeAction('edit');
		$this->key = $form->data;

		// refresh left panel
		$this['panel']->invalidateControl('content');
	}

	protected function createComponentFormedit()
	{
		$form = $this->layouteditFormFactory->invoke();
		$form->setData($this->key);
		$form->onSuccess[] = $this->formeditSuccess;

		return $form;
	}

	public function formeditSuccess($form)
	{
		$this->flashMessage($this->translator->translate('Layout has been saved.'), 'success');

		if (!$this->isAjax()) {
			$this->redirect('edit', array('key' => $form->data));
		}
		$this->invalidateControl('content');
		$this->payload->url = $this->link('edit', array('key' => $form->data));
		$this->key = $form->data;

		if (!$this->isAjax()) {
			$this->redirect('this');
		}
	}

	/**
	 * @secured(privilege="remove")
	 */
	public function handleDelete($key)
	{
		$path = $this->moduleHelpers->expandPath($key, 'Resources/layouts');

		unlink($path);

		if (substr($path, -14) === '/@layout.latte') {
			File::rmdir(dirname($path), true);

			$this->flashMessage($this->translator->translate('Layout has been removed.'), 'success');
		} else {
			$this->flashMessage($this->translator->translate('Template has been removed.'), 'success');
		}

		if (!$this->isAjax()) {
			$this->redirect('this', array('key' => null));
		}
		$this->invalidateControl('content');
		$this['panel']->invalidateControl('content');
		$this->payload->url = $this->link('this', array('key' => null));
	}

	public function renderDefault()
	{
		$this->template->templateManager = $this->templateManager;
	}

}
