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
use Nette\Application\UI\Form;
use Venne\Cms\Language;
use Venne\Cms\Listeners\ExtendedPageListener;
use Venne\Cms\Page;
use Venne\Cms\PageManager;
use Venne\Cms\SideComponents\PagesControl;

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

	/**
	 * @var string
	 *
	 * @persistent
	 */
	public $section;

	/**
	 * @var string
	 *
	 * @persistent
	 */
	public $contentLanguageId;

	/** @var \Venne\Cms\Page */
	private $page;

	/** @var \Venne\Cms\Page */
	private $masterPage;

	/** @var \Venne\Cms\PageType */
	private $pageType;

	/** @var \Venne\Cms\Language */
	private $contentLanguage;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $pageRepository;

	/** @var \Venne\Cms\AdminModule\PageFormService */
	private $pageFormService;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $languageRepository;

	/** @var \Venne\Cms\PageManager */
	private $pageManager;

	/** @var \Venne\Cms\Listeners\ExtendedPageListener */
	private $pageListener;

	public function __construct(
		EntityManager $entityManager,
		PageFormService $pageFormService,
		PageManager $pageManager,
		ExtendedPageListener $pageListener
	) {
		$this->pageRepository = $entityManager->getRepository(Page::class);
		$this->languageRepository = $entityManager->getRepository(Language::class);
		$this->pageFormService = $pageFormService;
		$this->pageManager = $pageManager;
		$this->pageListener = $pageListener;
	}

	public function handleChangeLanguage()
	{
		$this->redirect('this');
		$this->redrawControl('contentToolbar');
		$this->redrawControl('contentEdit');
	}

	public function handleChangeSection()
	{
		$this->redirect('this');
		$this->redrawControl('contentToolbar');
		$this->redrawControl('contentEdit');
	}

	public function handleChangePage()
	{
		$this->redirect('this');
		$this->redrawControl('content');
	}

	public function renderDefault()
	{
		$this->template->page = $this->page;
		$this->template->section = $this->section;
		$this->template->contentLanguage = $this->contentLanguage;
		$this->template->pageType = $this->pageType;
		$this->template->sections = $this->pageType->getSections(get_class($this->page->getExtendedPage()));
		$this->template->globalSections = $this->pageManager->getGlobalSections();
		$this->template->languages = $this->getLanguages();
	}

	/**
	 * @return \Nette\Application\UI\Control
	 */
	public function createComponentSectionControl()
	{
		$sectionFactory = $this->pageType->hasSection($this->section, $this->page->getExtendedPageClass())
			? $this->pageType->getSection($this->section, $this->page->getExtendedPageClass())
			: $this->pageManager->getGlobalSection($this->section, $this->page->getExtendedPageClass());

		$control = $sectionFactory->create($this->pageId);

		if ($control instanceof Form) {
			$control->onSuccess[] = function () {
				$this->flashMessage('Page has been saved', 'success');
				$this->redirect('this');
				$this->redrawControl('content');

				$sideComponent = $this->getSideComponents()->getSideComponent();
				if ($sideComponent instanceof PagesControl) {
					$sideComponent->redrawContent();
				}
			};
		}

		return $control;
	}

	public function loadState(array $params)
	{
		parent::loadState($params);

		$this->page = $this->pageRepository->find($params['pageId']);

		if ($this->page === null) {
			$this->error();
		}

		$this->masterPage = $this->page->getMasterPage() !== null ? $this->page->getMasterPage() : $this->page;
		$this->pageType = $this->pageManager->getPageType($this->masterPage->getExtendedPageClass());

		$defaultLanguageAlias = $this->page->getDomain()->getDefaultLanguage()->getAlias();

		if (!isset($params['contentLanguageId'])) {
			$this->contentLanguageId = $params['contentLanguageId'] = $this->page->getLanguage() !== null ? $this->page->getLanguage()->getAlias() : $defaultLanguageAlias;
		}

		if (!isset($params['section'])) {
			$this->section = key($this->pageType->getSections($this->page->getExtendedPageClass()));
		} elseif (!$this->pageType->hasSection($params['section'], $this->page->getExtendedPageClass()) && !$this->pageManager->hasGlobalSection($params['section'])) {
			$this->error();
		}

		$this->contentLanguage = $this->languageRepository->find($params['contentLanguageId']);

		if ($this->contentLanguage === null) {
			$this->error();
		}

		if ($defaultLanguageAlias !== $this->contentLanguage->getAlias()) {
			$this->pageListener->setLocaleAlias($this->contentLanguage->getAlias());
		}
	}

	/**
	 * @return \Venne\Cms\Language[)
	 */
	private function getLanguages()
	{
		return $this->languageRepository->findAll();
	}

}
