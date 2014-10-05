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
use Venne\DataTransfer\DataTransferManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageService extends \Nette\Object
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $pageRepository;

	/** @var \Venne\DataTransfer\DataTransferManager */
	private $dataTransferManager;

	public function __construct(EntityManager $entityManager, DataTransferManager $dataTransferManager)
	{
		$this->pageRepository = $entityManager->getRepository(Page::class);
		$this->dataTransferManager = $dataTransferManager;
	}

	/**
	 * @param integer $id
	 * @return \Venne\Cms\PageDto
	 */
	public function findPageDto($id)
	{
		return $this->dataTransferManager
			->createQuery(PageDto::class, function () use ($id) {
				return $this->pageRepository->find($id);
			})
			->enableCache($id)
			->fetch();
	}

}
