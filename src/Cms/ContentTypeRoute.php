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
class ContentTypeRoute extends Object
{

	/** @var string */
	private $name;

	/** @var callable */
	private $formFactory;

	/** @var string */
	private $class;

	/**
	 * @param string $name
	 * @param callable $formFactory
	 * @param string $class
	 */
	public function __construct($name, $formFactory, $class)
	{
		$this->name = $name;
		$this->formFactory = $formFactory;
		$this->class = $class;
	}

	/**
	 * @return callable
	 */
	public function getFormFactory()
	{
		return $this->formFactory;
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
	public function getClass()
	{
		return $this->class;
	}

}
