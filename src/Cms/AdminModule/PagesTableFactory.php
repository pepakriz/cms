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

	/** @var \Nette\Localization\ITranslator */
	private $translator;

	public function __construct(
		EntityManager $entityManager,
		PageFormService $pageFormService,
		IAdminGridFactory $adminGridFactory,
		ITranslator $translator
	)
	{
		$this->pageRepository = $entityManager->getRepository(Page::class);
		$this->pageFormService = $pageFormService;
		$this->adminGridFactory = $adminGridFactory;
		$this->translator = $translator;
	}

	public function create()
	{
		$admin = $this->adminGridFactory->create($this->pageRepository);

		// columns
		$table = $admin->getTable();
		$table->setTranslator($this->translator);
		$table->setPrimaryKey('id');

		$table->addColumnText('name', 'Name')
			->setSortable()
			->getCellPrototype()->width = '100%';

		$table->addActionEvent('edit', 'Edit')
			->getElementPrototype()->class[] = 'ajax';

		$form = $admin->addForm('page', 'Page', function (Page $page = null) {
			return $this->pageFormService->getFormFactory($page !== null ? $page->getId() : null);
		});
		$admin->connectFormWithAction($form, $table->getAction('edit'), $admin::MODE_PLACE);

		// Toolbar
		$toolbar = $admin->getNavbar();
		$toolbar->addSection('new', 'Create', 'file');
		$admin->connectFormWithNavbar($form, $toolbar->getSection('new'), $admin::MODE_PLACE);

		$table->addActionEvent('delete', 'Delete')
			->getElementPrototype()->class[] = 'ajax';
		$admin->connectActionAsDelete($table->getAction('delete'));

		return $admin;
	}

}
