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

use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ContentType extends Object
{

	/** @var string */
	private $name;

	/** @var string */
	private $entityName;

	/** @var ContentTypeSection[] */
	private $sections = array();

	/** @var ContentTypeRoute[] */
	private $routes = array();

	public function __construct($name, $entityName)
	{
		$this->name = $name;
		$this->entityName = $entityName;
	}

	/**
	 * @param string $name
	 * @param callable $formFactory
	 */
	public function addSection($name, $formFactory)
	{
		$this->sections[$name] = new ContentTypeSection($name, $formFactory);
	}

	/**
	 * @param $name
	 * @param $formFactory
	 * @param $class
	 */
	public function addRoute($name, $formFactory, $class)
	{
		$this->routes[$name] = new ContentTypeRoute($name, $formFactory, $class);
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasSection($name)
	{
		return isset($this->sections[$name]);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return mixed
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * @return string
	 */
	public function getEntityName()
	{
		return $this->entityName;
	}

	/**
	 * @return array|ContentTypeSection[]
	 */
	public function getSections()
	{
		return $this->sections;
	}

	/**
	 * @return ContentTypeRoute[]
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

}
