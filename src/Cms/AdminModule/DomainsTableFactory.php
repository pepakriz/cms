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
	)
	{
		$this->domainRepository = $entityManager->getRepository(Domain::class);
		$this->domainFormService = $domainFormService;
		$this->adminGridFactory = $adminGridFactory;
		$this->translator = $translator;
	}

	public function create()
	{
		$admin = $this->adminGridFactory->create($this->domainRepository);

		// columns
		$table = $admin->getTable();
		$table->setTranslator($this->translator);
		$table->setPrimaryKey('domain');

		$table->addColumnText('domain', 'Domain')
			->setSortable()
			->getCellPrototype()->width = '100%';

		$table->addActionEvent('edit', 'Edit')
			->getElementPrototype()->class[] = 'ajax';

		$form = $admin->addForm('domain', 'Domain', function (Domain $domain = null) {
			return $this->domainFormService->getFormFactory($domain !== null ? $domain->getDomain() : null);
		}, Form::TYPE_LARGE);
		$admin->connectFormWithAction($form, $table->getAction('edit'));

		// Toolbar
		$toolbar = $admin->getNavbar();
		$toolbar->addSection('new', 'Create', 'file');
		$admin->connectFormWithNavbar($form, $toolbar->getSection('new'));

		$table->addActionEvent('delete', 'Delete')
			->getElementPrototype()->class[] = 'ajax';
		$admin->connectActionAsDelete($table->getAction('delete'));

		return $admin;
	}

}
