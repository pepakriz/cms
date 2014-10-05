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
class ContentTypeSection extends Object
{

	/** @var string */
	private $name;

	/** @var callable */
	private $formFactory;

	/**
	 * @param string $name
	 * @param callable $formFactory
	 */
	public function __construct($name, $formFactory)
	{
		$this->name = $name;
		$this->formFactory = $formFactory;
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

}
