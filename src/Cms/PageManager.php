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

use Nette\InvalidArgumentException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageManager extends \Nette\Object
{

	/** @var \Venne\Cms\PageType[] */
	private $pageTypes = array();

	/** @var \Venne\Cms\SectionFactory[] */
	private $globalSections = array();

	/**
	 * @param \Venne\Cms\PageType[] $pageTypes
	 * @param \Venne\Cms\SectionFactory[] $globalSections
	 */
	public function __construct(
		array $pageTypes = array(),
		array $globalSections = array()
	) {
		foreach ($pageTypes as $pageType) {
			$this->addPageType($pageType);
		}

		foreach ($globalSections as $name => $factory) {
			$this->addGlobalSection($name, $factory);
		}
	}

	public function addPageType(PageType $pageType)
	{
		$this->pageTypes[$pageType->getEntityClass()] = $pageType;
	}

	/**
	 * @param string $entityClass
	 * @return boolean
	 */
	public function hasPageType($entityClass)
	{
		return isset($this->pageTypes[$entityClass]);
	}

	/**
	 * @param string $entityClass
	 * @return \Venne\Cms\PageType
	 */
	public function getPageType($entityClass)
	{
		if (!$this->hasPageType($entityClass)) {
			throw new InvalidArgumentException(sprintf('Page type \'%s\' does not exist.', $entityClass));
		}

		return $this->pageTypes[$entityClass];
	}

	/**
	 * @return \Venne\Cms\PageType[]
	 */
	public function getPageTypes()
	{
		return $this->pageTypes;
	}

	/**
	 * @param string $name
	 * @param \Venne\Cms\SectionFactory $factory
	 */
	public function addGlobalSection($name, SectionFactory $factory)
	{
		$this->globalSections[$name] = $factory;
	}

	/**
	 * @param string $name
	 * @return boolean
	 */
	public function hasGlobalSection($name)
	{
		return isset($this->globalSections[$name]);
	}

	/**
	 * @param string $name
	 * @return \Venne\Cms\SectionFactory
	 */
	public function getGlobalSection($name)
	{
		return $this->globalSections[$name];
	}

	/**
	 * @return \Venne\Cms\SectionFactory[]
	 */
	public function getGlobalSections()
	{
		return $this->globalSections;
	}

}
