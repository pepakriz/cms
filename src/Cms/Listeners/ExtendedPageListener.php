<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms\Listeners;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\EntityRepository;
use Venne\Cms\Language;
use Venne\Cms\Page;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ExtendedPageListener extends \Nette\Object
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $languageRepository;

	/** @var null|\Venne\Cms\Language */
	private $locale;

	/** @var \Venne\Cms\Page[] */
	private $pages = array();

	public function __construct(EntityManager $entityManager)
	{
		$this->languageRepository = $entityManager->getRepository(Language::class);
	}

	/**
	 * @param string|null $alias
	 */
	public function setLocaleAlias($alias)
	{
		$this->locale = $alias !== null ? $this->languageRepository->find($alias) : null;

		foreach ($this->pages as $page) {
			$this->setup($page);
		}
	}

	/**
	 * @ORM\PrePersist
	 */
	public function prePersist(Page $page, LifecycleEventArgs $args)
	{
		$this->setup($page);
	}

	/**
	 * @ORM\PreRemove
	 */
	public function preRemove(Page $page, LifecycleEventArgs $args)
	{
		$this->setup($page);
	}

	/**
	 * @ORM\PostLoad
	 */
	public function postLoad(Page $page, LifecycleEventArgs $event)
	{
		$this->pages[$page->getId()] = $page;
		$this->setup($page);

		$em = $event->getEntityManager();
		$page->setExtendedPageCallback(function () use ($em, $page) {
			return $em->getRepository($page->getExtendedPageClass())->findOneBy(array(
				'page' => $page->id,
			));
		});
	}

	private function setup(Page $page)
	{
		if ($this->locale) {
			$page->setLocale($this->locale);
		}
	}

}
