<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms\FrontModule;

use Venne\Cms\Listeners\ExtendedPageListener;
use Venne\Cms\PageService;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class PagePresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\UI\PresenterTrait;
	use \Venne\System\AjaxControlTrait;
	use \Venne\Widgets\WidgetsControlTrait;

	/**
	 * @var string
	 *
	 * @persistent
	 */
	public $lang;

	/**
	 * @var string
	 *
	 * @persistent
	 */
	public $domain;

	/**
	 * @var string
	 *
	 * @persistent
	 */
	public $pageId;

	/**
	 * @var boolean
	 *
	 * @persistent
	 */
	public $venneCmsEditation;

	/** @var \Venne\Cms\PageService */
	private $pageService;

	/** @var \Venne\Cms\Listeners\ExtendedPageListener */
	private $pageListener;

	public function injectPagePresenter(
		PageService $pageService,
		ExtendedPageListener $pageListener
	) {
		$this->pageService = $pageService;
		$this->pageListener = $pageListener;
	}

	protected function startup()
	{
		$this->pageService->changeLanguage($this->lang);
		parent::startup();
	}

	protected function beforeRender()
	{
		parent::beforeRender();

		$this->template->venneCmsEditation = $this->venneCmsEditation;
		$this->template->page = $this->pageService->findPageDto(
			$this->pageId,
			$this->lang
		);
	}

}
