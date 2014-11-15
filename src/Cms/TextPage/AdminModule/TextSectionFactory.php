<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms\TextPage\AdminModule;

use Venne\Cms\AdminModule\PageFormService;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TextSectionFactory extends \Nette\Object implements \Venne\Cms\SectionFactory
{

	/** @var \Venne\Cms\AdminModule\PageFormService */
	private $pageFormService;

	public function __construct(PageFormService $pageFormService)
	{
		$this->pageFormService = $pageFormService;
	}

	/**
	 * @param integer $pageId
	 * @return \Nette\Application\UI\Form
	 */
	public function create($pageId)
	{
		return $this->pageFormService
			->getFormFactory($pageId)
			->create();
	}

}
