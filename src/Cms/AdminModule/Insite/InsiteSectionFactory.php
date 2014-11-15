<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms\AdminModule\Insite;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InsiteSectionFactory extends \Nette\Object implements
	\Venne\Cms\SectionFactory,
	\Venne\Cms\SectionIcon
{

	/** @var \Venne\Cms\AdminModule\Insite\InsiteControlFactory */
	private $insiteControlFactory;

	public function __construct(InsiteControlFactory $insiteControlFactory)
	{
		$this->insiteControlFactory = $insiteControlFactory;
	}

	/**
	 * @param integer $pageId
	 * @return \Venne\Cms\AdminModule\Insite\InsiteControl
	 */
	public function create($pageId)
	{
		return $this->insiteControlFactory->create($pageId);
	}

	/**
	 * @return string
	 */
	public function getIcon()
	{
		return 'glyphicon glyphicon-globe';
	}

}
