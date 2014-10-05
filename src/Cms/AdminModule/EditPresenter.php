<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms\AdminModule;

use Kdyby\Doctrine\EntityManager;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\Cms\Page;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class EditPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/**
	 * @var integer
	 *
	 * @persistent
	 */
	public $pageId;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $pageRepository;

	/** @var \Venne\Cms\AdminModule\PageFormService */
	private $pageFormService;

	public function __construct(EntityManager $entityManager, PageFormService $pageFormService)
	{
		$this->pageRepository = $entityManager->getRepository(Page::class);
		$this->pageFormService = $pageFormService;
	}

	public function handleChangePage()
	{
		$this->redirect('this');
		$this->redrawControl('content');
	}

	public function actionIframe()
	{

	}

	public function renderDefault()
	{
		$this->template->page = $this->getCurrentEntity();
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	public function createComponentPageForm()
	{
		$form = $this->pageFormService
			->getFormFactory($this->pageId)
			->create();

		$form->onSuccess[] = function () {
			$this->flashMessage('Page has been saved', 'success');
			$this->redirect('this');
			$this->redrawControl('content');
		};

		return $form;
	}

	/**
	 * @return \Venne\Cms\Page
	 */
	private function getCurrentEntity()
	{
		return $this->pageRepository->find($this->pageId);
	}

}
