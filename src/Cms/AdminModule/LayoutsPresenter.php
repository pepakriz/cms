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
class LayoutsPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/** @var LayoutsTableFactory */
	private $layoutsTableFactory;

	public function __construct(LayoutsTableFactory $layoutsTableFactory)
	{
		$this->layoutsTableFactory = $layoutsTableFactory;
	}

	protected function createComponentTable()
	{
		return $this->layoutsTableFactory->create();
	}

}
