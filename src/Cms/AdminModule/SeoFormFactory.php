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

use Kdyby\DoctrineForms\IComponentMapper;
use Venne\Cms\Page;
use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SeoFormFactory implements \Venne\Forms\IFormFactory
{

	/** @var \Venne\Forms\IFormFactory */
	private $formFactory;

	public function __construct(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	public function create()
	{
		$form = $this->formFactory->create();

		$page = $form->addContainer('page');

		$page->setCurrentGroup($form->addGroup('Metadata'));
		$page->addText('keywords', 'Keywords');
		$page->addText('description', 'Description');

		$page->addSelect('author', 'Author')
			->setTranslator()
			->setPrompt('')
			->setOption(IComponentMapper::ITEMS_TITLE, 'email');

		$page->addSelect('robots', 'Robots')
			->setItems(Page::getRobotsValues(), FALSE);

		$page->setCurrentGroup($form->addGroup('Information for sitemap.xml'));
		$page->addSelect('changefreq', 'Change frequency')
			->setItems(Page::getChangefreqValues(), FALSE)
			->setPrompt('');

		$page->addSelect('priority', 'Priority')
			->setItems(Page::getPriorityValues(), FALSE)
			->setPrompt('');

		return $form;
	}

}
