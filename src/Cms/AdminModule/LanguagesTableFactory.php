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
use Venne\Cms\Language;
use Venne\System\Components\AdminGrid\Form;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LanguagesTableFactory
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $languageRepository;

	/** @var \Venne\Cms\AdminModule\LanguageFormService */
	private $languageFormService;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	/** @var \Nette\Localization\ITranslator */
	private $translator;

	public function __construct(
		EntityManager $entityManager,
		LanguageFormService $languageFormService,
		IAdminGridFactory $adminGridFactory,
		ITranslator $translator
	) {
		$this->languageRepository = $entityManager->getRepository(Language::class);
		$this->languageFormService = $languageFormService;
		$this->adminGridFactory = $adminGridFactory;
		$this->translator = $translator;
	}

	public function create()
	{
		$admin = $this->adminGridFactory->create($this->languageRepository);

		$table = $admin->getTable();
		$table->setTranslator($this->translator);
		$table->setPrimaryKey('alias');

		$table->addColumnText('name', 'Name')
			->setSortable()
			->getCellPrototype()->width = '50%';

		$table->addColumnText('alias', 'Alias')
			->setSortable()
			->getCellPrototype()->width = '20%';

		$table->addColumnText('short', 'Short')
			->setSortable()
			->getCellPrototype()->width = '30%';

		$form = $admin->addForm('language', 'Language', function (Language $language = null) {
			return $this->languageFormService->getFormFactory($language !== null ? $language->getAlias() : null);
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
