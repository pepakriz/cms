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
use Kdyby\Doctrine\Entities\BaseEntity;
use Kdyby\DoctrineForms\EntityFormMapper;
use Nette\Application\UI\Form;
use Venne\Cms\TextPage\Page;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageFormService extends \Venne\System\DoctrineFormService
{

	public function __construct(
		PageFormFactory $pageFormFactory,
		EntityManager $entityManager,
		EntityFormMapper $entityFormMapper
	)
	{
		parent::__construct($pageFormFactory, $entityManager, $entityFormMapper);
	}

	/**
	 * @return string
	 */
	protected function getEntityClassName()
	{
		return Page::class;
	}

	protected function save(Form $form, $entity)
	{
		$this->getEntityManager()->beginTransaction();
		try {
			$this->getEntityFormMapper()->save($entity, $form);

			$this->getEntityManager()->persist($entity->getPage());
			$this->getEntityManager()->flush($entity->getPage());
			$this->getEntityManager()->persist($entity);
			$this->getEntityManager()->flush($entity);

			$this->getEntityManager()->commit();
		} catch (\Exception $e) {
			$this->getEntityManager()->rollback();

			$this->error($form, $e);
		}
	}

	protected function error(Form $form, \Exception $e)
	{
		if ($e instanceof \Kdyby\Doctrine\DuplicateEntryException) {
			$form['name']->addError($form->getTranslator()->translate('Name must be unique.'));

			return;
		}

		parent::error($form, $e);
	}

}
