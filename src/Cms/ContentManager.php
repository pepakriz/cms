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

use Nette\DI\Container;
use Nette\InvalidArgumentException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ContentManager extends \Nette\Object
{

	/** @var Container */
	private $context;

	/** @var callable[] */
	private $contentTypes = array();

	/** @var array */
	private $pageComponents = array();

	/** @var array */
	private $routeComponents = array();

	/** @var array */
	private $administrationPages = array();

	public function __construct(Container $context)
	{
		$this->context = $context;
	}

	public function addContentType($type, $name, ContentType $contentType)
	{
		$this->contentTypes[$type] = array(
			'name' => $name,
			'factory' => $contentType
		);
	}

	public function addPageComponent($name, $factory)
	{
		$this->pageComponents[$name] = $factory;
	}

	public function addRouteComponent($name, $factory)
	{
		$this->routeComponents[$name] = $factory;
	}

	public function addAdministrationPage($name, $description, $category, $link, $administrationPageFactory)
	{
		$this->administrationPages[$link] = array(
			'name' => $name,
			'description' => $description,
			'category' => $category,
			'factory' => $administrationPageFactory
		);
	}

	/**
	 * Get Content Types as array.
	 *
	 * @return ContentType[]
	 */
	public function getContentTypes($tree = false)
	{
		$ret = array();

		foreach ($this->contentTypes as $type => $item) {
			if ($tree && ($p = strpos($item['name'], '.')) !== false) {
				$name = $item['name'];
				$r = &$ret;

				while (($p = strpos($name, '.')) !== false) {
					$key = substr($name, 0, $p);
					$name = substr($name, $p + 1);

					if (!isset($r[$key])) {
						$r[$key] = array();
					}

					$r = &$r[$key];
				}

				$r[$type] = $name;

			} else {
				$ret[$type] = $item['name'];
			}

		}

		return $ret;
	}

	/**
	 * Has content type.
	 *
	 * @param string $link
	 * @return IContentType
	 */
	public function hasContentType($type)
	{
		return isset($this->contentTypes[$type]);
	}

	/**
	 * Get content type.
	 *
	 * @param string $link
	 * @return IContentType
	 */
	public function getContentType($type)
	{
		return $this->contentTypes[$type]['factory'];
	}

	/**
	 * @return array
	 */
	public function getPageComponents()
	{
		return $this->pageComponents;
	}

	/**
	 * @return array
	 */
	public function getRouteComponents()
	{
		return $this->routeComponents;
	}

	public function getRouteComponent($name)
	{
		if (!isset($this->routeComponents[$name])) {
			throw new InvalidArgumentException(sprintf('RouteComponent \'%s\' does not exist.', $name));
		}

		return $this->routeComponents[$name]->create();
	}

	/**
	 * Get Administration pages as array
	 *
	 * @return array
	 */
	public function getAdministrationPages()
	{
		$ret = array();

		foreach ($this->administrationPages as $link => $item) {
			$ret[$item['category']][$link] = $item;
		}

		return $ret;
	}

}
