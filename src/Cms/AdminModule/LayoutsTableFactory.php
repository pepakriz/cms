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

use Doctrine\ORM\EntityManager;
use Nette\Localization\ITranslator;
use Venne\Cms\Layout;
use Venne\System\Components\AdminGrid\Form;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LayoutsTableFactory
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $layoutRepository;

	/** @var \Venne\Cms\AdminModule\LayoutFormFactory */
	private $layoutFormService;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	/** @var \Nette\Localization\ITranslator */
	private $translator;

	public function __construct(
		EntityManager $entityManager,
		LayoutFormService $layoutFormService,
		IAdminGridFactory $adminGridFactory,
		ITranslator $translator
	)
	{
		$this->layoutRepository = $entityManager->getRepository(Layout::class);
		$this->layoutFormService = $layoutFormService;
		$this->adminGridFactory = $adminGridFactory;
		$this->translator = $translator;
	}

	public function create()
	{
		$admin = $this->adminGridFactory->create($this->layoutRepository);

		$table = $admin->getTable();
		$table->setTranslator($this->translator);

		$table->addColumnText('name', 'Name')
			->setSortable()
			->getCellPrototype()->width = '50%';

		$table->addColumnText('file', 'File')
			->setSortable()
			->getCellPrototype()->width = '50%';

		$form = $admin->addForm('layout', 'Layout', function (Layout $layout = null) {
			return $this->layoutFormService->getFormFactory($layout !== null ? $layout->getId() : null);
		}, Form::TYPE_LARGE);

		$toolbar = $admin->getNavbar();
		$newSection = $toolbar->addSection('new', 'Create', 'file');

		$editAction = $table->addActionEvent('edit', 'Edit');
		$editAction->getElementPrototype()->class[] = 'ajax';

		$deleteAction = $table->addActionEvent('delete', 'Delete');
		$deleteAction->getElementPrototype()->class[] = 'ajax';

		$admin->connectFormWithNavbar($form, $newSection);
		$admin->connectFormWithAction($form, $editAction);
		$admin->connectActionAsDelete($deleteAction);

		return $admin;
	}

}
