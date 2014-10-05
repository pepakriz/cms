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

use Doctrine\ORM\EntityManager;
use Latte\MacroTokens;
use Nette\InvalidArgumentException;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LayoutManager extends Object
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $layoutRepository;

	public function __construct(EntityManager $entityManager)
	{
		$this->layoutRepository = $entityManager->getRepository(Layout::class);
	}

	/**
	 * @param $file
	 * @return array
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getElementsByFile($file)
	{
		if (!file_exists($file)) {
			throw new InvalidArgumentException(sprintf('File \'%s\' does not exist.', $file));
		}

		$ret = array();
		$tokenizer = new MacroTokens(file_get_contents($file));

		while (($word = $tokenizer->fetchWord()) !== false) {
			if ($word === '{element') {
				$name = trim($tokenizer->fetchWord(), '}\'"');
				$id = trim($tokenizer->fetchWord(), '}\'"');
				$ret[$id] = $name;
			}
		}

		return $ret;
	}

}
