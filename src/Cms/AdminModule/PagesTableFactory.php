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

use Doctrine\ORM\Query;
use Kdyby\Doctrine\EntityManager;
use Nette\Localization\ITranslator;
use Venne\Cms\Page;
use Venne\Cms\PageManager;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PagesTableFactory
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $pageRepository;

	/** @var \Venne\Cms\AdminModule\PageFormFactory */
	private $pageFormService;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	/** @var \Venne\Cms\PageManager */
	private $pageManager;

	/** @var \Nette\Localization\ITranslator */
	private $translator;

	public function __construct(
		EntityManager $entityManager,
		PageFormService $pageFormService,
		IAdminGridFactory $adminGridFactory,
		PageManager $pageManager,
		ITranslator $translator
	) {
		$this->pageRepository = $entityManager->getRepository(Page::class);
		$this->pageFormService = $pageFormService;
		$this->adminGridFactory = $adminGridFactory;
		$this->pageManager = $pageManager;
		$this->translator = $translator;
	}

	public function create()
	{
		$admin = $this->adminGridFactory->create($this->pageRepository);

		$table = $admin->getTable();
		$table->setTranslator($this->translator);
		$table->setPrimaryKey('id');

		$table->addColumnText('name', 'Name')
			->setSortable()
			->getCellPrototype()->width = '100%';

		$form = $admin->addForm('page', 'Page', function (Page $page = null) {
			return $this->pageFormService->getFormFactory($page !== null ? $page->getId() : null);
		});

		$toolbar = $admin->getNavbar();
		$newSection = $toolbar->addSection('new', 'Create', 'file');

		$editAction = $table->addActionHref('edit', 'Edit');
		$editAction->getElementPrototype()->class[] = 'ajax';
		$editAction->setCustomHref(function ($id) use ($admin) {
			$admin->getPresenter()->link(':Admin:Cms:Edit:', array(
				'pageId' => $id,
				'do' => 'changePage',
			));
		});

		$deleteAction = $table->addActionEvent('delete', 'Delete');
		$deleteAction->getElementPrototype()->class[] = 'ajax';

		$admin->connectFormWithNavbar($form, $newSection, $admin::MODE_PLACE);
		$admin->connectActionAsDelete($deleteAction);

		foreach ($this->pageManager->getPageTypes() as $type => $value) {
			$admin->connectFormWithNavbar(
				$form,
				$newSection->addSection(str_replace('\\', '_', $type), $value->getName()),
				$admin::MODE_PLACE
			);
		}

		return $admin;
	}

}
