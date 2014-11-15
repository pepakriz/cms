<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms\DI;

use Nette\DI\Statement;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageType extends \Nette\Object
{

	/** @var string */
	private $name;

	/** @var string */
	private $entityName;

	/** @var \Nette\DI\Statement[][] */
	private $sections = array();

	/**
	 * @param string $name
	 * @param string $entityName
	 */
	public function __construct($name, $entityName)
	{
		$this->name = $name;
		$this->entityName = $entityName;
	}

	/**
	 * @param string $name
	 * @param string $entityName
	 * @param \Nette\DI\Statement $factory
	 */
	public function addSection($name, $entityName, Statement $factory)
	{
		$this->sections[$entityName][$name] = $factory;
	}

	/**
	 * @return mixed[]
	 */
	public function getArguments()
	{
		return array(
			$this->name,
			$this->entityName,
			$this->sections,
		);
	}

}
