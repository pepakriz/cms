<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms;

use Doctrine\ORM\EntityManager;
use Venne\Cms\Listeners\ExtendedPageListener;
use Venne\DataTransfer\DataTransferManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageService extends \Nette\Object
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $pageRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $languageRepository;

	/** @var \Venne\DataTransfer\DataTransferManager */
	private $dataTransferManager;

	/** @var \Venne\Cms\Listeners\ExtendedPageListener */
	private $pageListener;

	public function __construct(
		EntityManager $entityManager,
		DataTransferManager $dataTransferManager,
		ExtendedPageListener $pageListener
	) {
		$this->pageRepository = $entityManager->getRepository(Page::class);
		$this->languageRepository = $entityManager->getRepository(Language::class);
		$this->dataTransferManager = $dataTransferManager;
		$this->pageListener = $pageListener;
	}



	/**
	 * @param integer $id
	 * @param string $alias
	 * @return \Venne\Cms\PageDto|null
	 */
	public function findPageDto($id, $alias)
	{
		return $this->dataTransferManager
			->createQuery(PageDto::class, function () use ($id) {
				return $this->pageRepository->find($id);
			})
			->enableCache(array($id, $alias))
			->fetch();
	}

	/**
	 * @param string $alias
	 * @return \Venne\Cms\PageDto|null
	 */
	public function findRootPageDto($alias)
	{
		return $this->dataTransferManager
			->createQuery(PageDto::class, function () {
				return $this->pageRepository->createQueryBuilder('a')
					->andWhere('a.parent IS NULL')
					->getQuery()->getOneOrNullResult();
			})
			->enableCache(array($alias))
			->fetch();
	}

	/**
	 * @param integer $pageId
	 * @return integer
	 */
	public function countChildren($pageId)
	{
		return $this->pageRepository->createQueryBuilder('a')
			->select('COUNT(a.id)')
			->andWhere('a.parent = :parent')->setParameter('parent', $pageId)
			->getQuery()
			->useResultCache(true)
			->getSingleScalarResult();
	}

	/**
	 * @param integer $pageId
	 * @param string $alias
	 * @return \Venne\Cms\PageDto[]
	 */
	public function getChildrenPageDto($pageId, $alias)
	{
		return $this->dataTransferManager
			->createQuery(PageDto::class, function () use ($pageId) {
				return $this->pageRepository->createQueryBuilder('a')
					->andWhere('a.parent = :parent')->setParameter('parent', $pageId)
					->orderBy('a.position', 'ASC')
					->getQuery()
					->getResult();
			})
			->enableCache(array($pageId, $alias))
			->fetchAll();
	}

	/**
	 * @param string $alias
	 */
	public function changeLanguage($alias)
	{
		$this->pageListener->setLocaleAlias($alias);
	}

}
