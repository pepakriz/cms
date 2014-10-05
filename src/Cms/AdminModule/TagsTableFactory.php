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
use Venne\Cms\Tag;
use Venne\System\Components\AdminGrid\Form;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TagsTableFactory
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $tagRepository;

	/** @var \Venne\Cms\AdminModule\TagFormService */
	private $tagFormService;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	/** @var \Nette\Localization\ITranslator */
	private $translator;

	public function __construct(
		EntityManager $entityManager,
		TagFormService $tagFormService,
		IAdminGridFactory $adminGridFactory,
		ITranslator $translator
	)
	{
		$this->tagRepository = $entityManager->getRepository(Tag::class);
		$this->tagFormService = $tagFormService;
		$this->adminGridFactory = $adminGridFactory;
		$this->translator = $translator;
	}

	public function create()
	{
		$admin = $this->adminGridFactory->create($this->tagRepository);

		// columns
		$table = $admin->getTable();
		$table->setTranslator($this->translator);
		$table->addColumnText('name', 'Name')
			->setSortable()
			->getCellPrototype()->width = '100%';

		$table->addActionEvent('edit', 'Edit')
			->getElementPrototype()->class[] = 'ajax';

		$form = $admin->addForm('tag', 'Tag', function (Tag $tag) {
			return $this->tagFormService->getFormFactory($tag !== null ? $tag->getId() : null);
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
