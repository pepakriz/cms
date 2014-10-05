<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms\AdminModule;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class ContentPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/** @var \Venne\Cms\AdminModule\PagesTableFactory */
	private $tableFactory;

	public function __construct(PagesTableFactory $tableFactory)
	{
		$this->tableFactory = $tableFactory;
	}

	/**
	 * @return \Venne\System\Components\AdminGrid\AdminGrid
	 */
	protected function createComponentTable()
	{
		return $this->tableFactory->create();
	}

}
