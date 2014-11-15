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
use Venne\Cms\Domain;
use Venne\System\Components\AdminGrid\Form;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DomainsTableFactory
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $domainRepository;

	/** @var \Venne\Cms\AdminModule\DomainFormService */
	private $domainFormService;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	/** @var \Nette\Localization\ITranslator */
	private $translator;

	public function __construct(
		EntityManager $entityManager,
		DomainFormService $domainFormService,
		IAdminGridFactory $adminGridFactory,
		ITranslator $translator
	) {
		$this->domainRepository = $entityManager->getRepository(Domain::class);
		$this->domainFormService = $domainFormService;
		$this->adminGridFactory = $adminGridFactory;
		$this->translator = $translator;
	}

	/**
	 * @return \Venne\System\Components\AdminGrid\AdminGrid
	 */
	public function create()
	{
		$admin = $this->adminGridFactory->create($this->domainRepository);

		$table = $admin->getTable();
		$table->setTranslator($this->translator);
		$table->setPrimaryKey('domain');

		$table->addColumnText('domain', 'Domain')
			->setSortable()
			->getCellPrototype()->width = '40%';

		$table->addColumnText('name', 'Name')
			->setSortable()
			->getCellPrototype()->width = '60%';

		$form = $admin->addForm('domain', 'Domain', function (Domain $domain = null) {
			return $this->domainFormService->getFormFactory($domain !== null ? $domain->getDomain() : null);
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
