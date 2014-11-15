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
class GlobalSection extends \Nette\Object
{

	/** @var string */
	private $name;

	/** @var string */
	private $factory;

	/**
	 * @param string $name
	 * @param \Nette\DI\Statement $factory
	 */
	public function __construct($name, Statement $factory)
	{
		$this->name = $name;
		$this->factory = $factory;
	}

	/**
	 * @return mixed[]
	 */
	public function getArguments()
	{
		return array(
			$this->name,
			$this->factory,
		);
	}

}
