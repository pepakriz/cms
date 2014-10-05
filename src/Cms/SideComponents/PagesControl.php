<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms\SideComponents;

use Doctrine\ORM\EntityManager;
use Nette\Http\Session;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\Cms\AdminModule\PageFormFactory;
use Venne\Cms\Page;
use Venne\Files\SideComponents\IBrowserControlFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PagesControl extends \Venne\System\UI\Control
{

	/** @var \Nette\Http\SessionSection */
	private $session;

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $pageRepository;

	/** @var \Venne\Files\SideComponents\IBrowserControlFactory */
	private $browserFactory;

	/** @var \Venne\Cms\AdminModule\PageFormFactory */
	private $pageFormFactory;

	/** @var \Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory */
	private $formFactoryFactory;

	public function __construct(
		EntityManager $entityManager,
		Session $session,
		IBrowserControlFactory $browserFactory,
		PageFormFactory $pageFormFactory,
		FormFactoryFactory $formFactoryFactory
	)
	{
		parent::__construct();

		$this->entityManager = $entityManager;
		$this->pageRepository = $entityManager->getRepository(Page::class);
		$this->session = $session->getSection('Venne.Cms.SideComponents.PagesControl');
		$this->browserFactory = $browserFactory;
		$this->pageFormFactory = $pageFormFactory;
		$this->formFactoryFactory = $formFactoryFactory;
	}

	public function render()
	{
		$this->template->render();
	}

	/**
	 * @param int $id
	 * @param bool $state
	 */
	public function setState($id, $state)
	{
		if (!isset($this->session->state)) {
			$this->session->state = array();
		}

		$this->session->state[$id] = $state;
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function getState($id)
	{
		return isset($this->session->state[$id]) ? $this->session->state[$id] : false;
	}

	/**
	 * @return \Venne\Files\SideComponents\BrowserControl
	 */
	protected function createComponentBrowser()
	{
		$browser = $this->browserFactory->create();
		$browser->setLoadCallback($this->getPages);
		$browser->setDropCallback($this->setPageParent);
		$browser->onClick[] = function ($key) {
			$this->getPresenter()->forward(':Admin:Cms:Edit:', array(
				'pageId' => $key,
				'do' => 'changePage',
			));
		};
		$browser->onExpand[] = $this->pageExpand;

		return $browser;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	public function createComponentPageForm()
	{
		$form = $this->formFactoryFactory
			->create($this->pageFormFactory)
			->setEntity($this->getCurrentEntity()->getExtendedPage())
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
		return $this->pageRepository->find(57);
	}

	/**
	 * @param string $key
	 * @param string $open
	 */
	public function pageExpand($key, $open)
	{
		$this->setState((int) $key, $open);
	}

	/**
	 * @param string $parent
	 * @return mixed[]
	 */
	public function getPages($parent = null)
	{
		$this->setState((int) $parent, true);

		$data = array();

		$dql = $this->pageRepository->createQueryBuilder('a')
			->orderBy('a.position', 'ASC');
		if ($parent) {
			$dql = $dql->andWhere('a.parent = ?1')->setParameter(1, $parent);
		} else {
			$dql = $dql->andWhere('a.parent IS NULL');
		}

		foreach ($dql->getQuery()->getResult() as $page) {
			$item = array('title' => $page->name, 'key' => $page->id);

			$item['folder'] = true;

			if (count($page->children) > 0) {
				$item['lazy'] = true;
			}

			if ($this->getState($page->id)) {
				$item['expanded'] = true;
				$item['children'] = $this->getPages($page->id);
			}

			$data[] = $item;
		}

		return $data;
	}

	/**
	 * @param string $from
	 * @param string $to
	 * @param string $dropMode
	 */
	public function setPageParent($from, $to, $dropMode)
	{
		$entity = $this->pageRepository->find($from);
		$target = $this->pageRepository->find($to);

		if ($target->parent === null && ($dropMode === 'before' || $dropMode === 'after')) {
			$entity->setAsRoot();
			$this->entityManager->flush();
		} else {
			if ($dropMode === 'before' || $dropMode === 'after') {
				$entity->setParent(
					$target->getParent(),
					true,
					$dropMode === 'after' ? $target : $target->getPrevious()
				);
			} else {
				$entity->setParent($target);
			}

			$this->entityManager->flush();
		}
	}

}
