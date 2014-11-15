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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageType extends \Nette\Object
{

	/** @var string */
	private $name;

	/** @var string */
	private $entityClass;

	/** @var \Venne\Cms\SectionFactory[][] */
	private $sections = array();

	/**
	 * @param string $name
	 * @param string $entityClass
	 * @param \Venne\Cms\SectionFactory[] $sections
	 */
	public function __construct($name, $entityClass, array $sections = array())
	{
		$this->name = $name;
		$this->entityClass = $entityClass;

		foreach ($sections as $entityName => $section) {
			foreach ($section as $name => $factory) {
				$this->addSection($name, $entityName, $factory);
			}
		}
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getEntityClass()
	{
		return $this->entityClass;
	}

	/**
	 * @param string $name
	 * @param string $entityName
	 * @param \Venne\Cms\SectionFactory $factory
	 */
	private function addSection($name, $entityName, SectionFactory $factory)
	{
		$this->sections[$entityName][$name] = $factory;
	}

	/**
	 * @param string $name
	 * @param string $entityName
	 * @return boolean
	 */
	public function hasSection($name, $entityName)
	{
		return isset($this->sections[$entityName][$name]);
	}

	/**
	 * @param string $name
	 * @param string $entityName
	 * @return \Venne\Cms\SectionFactory
	 */
	public function getSection($name, $entityName)
	{
		return $this->sections[$entityName][$name];
	}

	/**
	 * @param string $entityName
	 * @return \Venne\Cms\SectionFactory[]
	 */
	public function getSections($entityName)
	{
		return (array) $this->sections[$entityName];
	}

}
