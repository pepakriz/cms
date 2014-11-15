<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms\Components;

use Doctrine\ORM\EntityManager;
use Venne\Cms\Page;
use Venne\Cms\PageDto;
use Venne\Cms\PageService;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NavigationControl extends \Venne\System\UI\Control
{

	/** @var \Doctrine\ORM\EntityRepository */
	private $pageRepository;

	/** @var \Venne\Cms\PageService */
	private $pageService;

	public function __construct(
		EntityManager $entityManager,
		PageService $pageService
	) {
		parent::__construct();

		$this->pageRepository = $entityManager->getRepository(Page::class);
		$this->pageService = $pageService;
	}

	/**
	 * @param null $startDepth
	 * @param null $maxDepth
	 * @param Page $root
	 */
	public function renderDefault($startDepth = null, $maxDepth = null, Page $root = null)
	{
		$this->template->startDepth = $startDepth ?: 0;
		$this->template->maxDepth = $maxDepth ?: 2;
		$this->template->root = $root ?: null;
	}

	/**
	 * @return \Venne\Cms\PageDto|null
	 */
	public function findRoot()
	{
		return $this->pageService->findRootPageDto($this->getPresenter()->lang);
	}

	/**
	 * @param \Venne\Cms\PageDto $page
	 * @return integer
	 */
	public function countChildren(PageDto $page = null)
	{
		return $this->pageService->countChildren($page->id);
	}

	/**
	 * @param \Venne\Cms\PageDto $page
	 * @return \Venne\Cms\PageDto[]
	 */
	public function getChildren(PageDto $page)
	{
		return $this->pageService->getChildrenPageDto($page->id, $this->getPresenter()->lang);
	}

	/**
	 * @param Page $page
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	private function getChildrenQb(Page $page = null)
	{
		return $this->pageRepository->createQueryBuilder('a')
			->andWhere('a.parent = :parent')->setParameter('parent', $page !== null ? $page->id : null)
//			->andWhere('(a.language IS NULL OR a.language = :language)')//->setParameter('language', $this->presenter->language->id)
//			->andWhere('a.published = :true')->setParameter('true', true)
			->orderBy('a.position', 'ASC');
	}

	/**
	 * @param \Venne\Cms\PageDto $page
	 * @return boolean
	 */
	public function isActive(PageDto $page)
	{
		return $this->isUrlActive($this->getUrl($page));
	}

	/**
	 * @param $url
	 * @return boolean
	 */
	public function isUrlActive($url)
	{
		return true;

		$currentUrl = $this->presenter->slug;

		return (!$url && !$currentUrl) || ($url && strpos($currentUrl . '/', $url . '/') !== false);
	}

	/**
	 * @param $domain
	 * @return bool
	 */
	public function isDomainActive($domain)
	{
		//return $domain === $this->presenter->_domain;
	}

	/**
	 * @param \Venne\Cms\PageDto $page
	 * @return string
	 */
	public function getLink(PageDto $page)
	{
		return $this->presenter->link('Route', array('pageId' => $page->id));
	}

	/**
	 * @param \Venne\Cms\PageDto $page
	 * @return string
	 */
	public function getUrl(PageDto $page)
	{
		return $page->url;
	}

}
