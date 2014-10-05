<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms\TextPage\FrontModule;

use Nette\Application\UI\Presenter;
use Venne\Cms\PageService;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class DefaultPresenter extends Presenter
{

	use \Venne\System\UI\PresenterTrait;
	use \Venne\System\AjaxControlTrait;
	use \Venne\Widgets\WidgetsControlTrait;

	/**
	 * @var boolean
	 *
	 * @persistent
	 */
	public $venneCmsEditation;

	/** @var \Venne\Cms\PageService */
	private $pageService;

	public function __construct(PageService $pageService)
	{
		$this->pageService = $pageService;
	}

	protected function beforeRender()
	{
		parent::beforeRender();

		$this->template->venneCmsEditation = $this->venneCmsEditation;
		$this->template->page = $this->pageService->findPageDto($this->getParameter('pageId'));
	}

}
