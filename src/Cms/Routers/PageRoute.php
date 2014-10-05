<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms\Routers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Nette\Application\Request;
use Nette\Application\Routers\Route;
use Nette\Http\IRequest;
use Nette\Http\Url;
use Venne\Cms\Domain;
use Venne\Cms\Language;
use Venne\Cms\Page;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageRoute extends Route
{

	const CACHE = 'Venne.routing';

	const DEFAULT_MODULE = 'Cms';

	const DEFAULT_PRESENTER = 'Base';

	const DEFAULT_ACTION = 'default';

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $languageRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $pageRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $domainRepository;

	/** @var string[] */
	private $countLanguages;

	/** @var string */
	private $defaultLanguage;

	/** @var integer */
	private $countDomains;

	/** @var string */
	private $defaultDomain;

	/**
	 * @param string $routePrefix
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 */
	public function __construct(
		$routePrefix,
		EntityManager $entityManager
	)
	{
		$this->pageRepository = $entityManager->getRepository(Page::class);
		$this->languageRepository = $entityManager->getRepository(Language::class);
		$this->domainRepository = $entityManager->getRepository(Domain::class);

		$suffixes = array();
		if ($this->countLanguages() > 1 && strpos($routePrefix, '<lang>') === false) {
			$suffixes[] = 'lang=<lang>';
		}

		if ($this->countDomains() > 1 && strpos($routePrefix, '<domain>') === false) {
			$suffixes[] = 'domain=<domain>';
		}

		parent::__construct($routePrefix . '<slug .+>[/<module qwertzuiop>/<presenter qwertzuiop>]' . (count($suffixes) > 0 ? '?' . implode('&', $suffixes) : ''), array(
			'presenter' => self::DEFAULT_PRESENTER,
			'module' => self::DEFAULT_MODULE,
			'action' => self::DEFAULT_ACTION,
			'lang' => null,
			'slug' => array(
				self::VALUE => '',
				self::FILTER_IN => null,
				self::FILTER_OUT => null,
			),
			'domain' => array(
				self::VALUE => null,
				self::FILTER_IN => null,
				self::FILTER_OUT => null,
			),
		));
	}

	/**
	 * @param  \Nette\Http\IRequest
	 * @return \Nette\Application\Request|NULL
	 */
	public function match(IRequest $httpRequest)
	{
		if (($appRequest = parent::match($httpRequest)) === null) {
			return null;
		}

		$parameters = $appRequest->getParameters();

		if (!array_key_exists('slug', $parameters)) {
			return null;
		}

		try {
			$qb = $this->pageRepository->createQueryBuilder('a')
				->select('a.id, a.presenter, a.action')
				->andWhere('a.url = :url')->setParameter('url', $parameters['slug']);

			if ($this->countLanguages() > 1) {
				$qb = $qb
					->andWhere('a.language IS NULL OR a.language = :alias')
					->setParameter('alias', $parameters['lang'] !== null ? $parameters['lang'] : $this->getDefaultLanguage());
			}

			if ($this->countDomains() > 1) {
				$qb = $qb
					->andWhere('a.domain IS NULL OR a.domain = :domain')
					->setParameter('domain', $parameters['domain']);
			}

			$page = $qb
				->getQuery()
//				->useResultCache(true)
				->getSingleResult();
			$appRequest->setPresenterName($page['presenter']);
			$appRequest->setParameters($parameters + array(
					'pageId' => $page['id'],
					'action' => $page['action'],
				));

		} catch (NoResultException $e) {
			return null;
		}

		return $appRequest;
	}

	/**
	 * @param  \Nette\Application\Request
	 * @param  \Nette\Http\Url
	 * @return string|NULL
	 */
	public function constructUrl(Request $appRequest, Url $refUrl)
	{
		$parameters = $appRequest->getParameters();
		$pageId = $parameters['pageId'] instanceof Page ? $parameters['pageId']->getId() : $parameters['pageId'];
		unset($parameters['pageId']);
		$pageUrl = $this->pageRepository->createQueryBuilder('a')
			->select('a.url')
			->andWhere('a.id = :id')->setParameter('id', $pageId)
			->getQuery()
//			->useResultCache(true)
			->getSingleScalarResult();

		$appRequest->setPresenterName(self::DEFAULT_MODULE . ':' . self::DEFAULT_PRESENTER);
		$appRequest->setParameters(array(
				'module' => self::DEFAULT_MODULE,
				'presenter' => self::DEFAULT_PRESENTER,
				'action' => self::DEFAULT_ACTION,
				'slug' => $pageUrl,
				'lang' => $this->countLanguages() > 1
					? (isset($parameters['lang']) ? $parameters['lang'] : $this->getDefaultLanguage())
					: null,
				'domain' => $this->countDomains() > 1
					? (isset($parameters['domain']) ? $parameters['domain'] : $this->getDefaultDomain())
					: null,
			) + $parameters);

		return parent::constructUrl($appRequest, $refUrl);
	}

	/**
	 * @return integer
	 */
	private function countLanguages()
	{
		if ($this->countLanguages === null) {
			$this->countLanguages = $this->languageRepository->createQueryBuilder('a')
				->select('COUNT(a.alias)')
				->getQuery()
//				->useResultCache(true)
				->getSingleScalarResult();
		}

		return $this->countLanguages;
	}

	/**
	 * @return integer
	 */
	private function countDomains()
	{
		if ($this->countDomains === null) {
			$this->countDomains = $this->domainRepository->createQueryBuilder('a')
				->select('COUNT(a.domain)')
				->getQuery()
//				->useResultCache(true)
				->getSingleScalarResult();
		}

		return $this->countDomains;
	}

	/**
	 * @return string
	 */
	private function getDefaultLanguage()
	{
		if ($this->defaultLanguage === null) {
			$this->defaultLanguage = $this->languageRepository->createQueryBuilder('l')
				->select('l.alias')
				->join('l.domains', 'd')
				->getQuery()
//				->useResultCache(true)
				->getSingleScalarResult();
		}

		return $this->defaultLanguage;
	}

	/**
	 * @return string
	 */
	private function getDefaultDomain()
	{
		if ($this->defaultDomain === null) {
			$this->defaultDomain = $this->domainRepository->createQueryBuilder('a')
				->select('a.domain')
				->getQuery()
//				->useResultCache(true)
				->getSingleScalarResult();
		}

		return $this->defaultDomain;
	}

}
