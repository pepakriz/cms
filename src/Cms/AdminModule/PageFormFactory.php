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
use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageFormFactory implements \Venne\Forms\IFormFactory
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
		$page->addText('name', 'Name')
			->setRequired();

		$page->addText('localUrl', 'URL')
			->setRequired();

		$page->addText('navigationTitle', 'Navigation title')
			->setRequired();

		$page->addSelect('domain', 'Domain')
			->setTranslator()
			->setOption(IComponentMapper::ITEMS_TITLE, 'domain')
			->setRequired();

		$page->addSelect('layout', 'Layout')
			->setTranslator()
			->setOption(IComponentMapper::ITEMS_TITLE, 'name')
			->setRequired();

		$page->addSelect('parent', 'Parent')
			->setTranslator()
			->setPrompt('')
			->setOption(IComponentMapper::ITEMS_TITLE, 'optionString')
			->setOption(IComponentMapper::ITEMS_ORDER, array('positionString' => 'ASC'));

		$page->addSelect('author', 'Author')
			->setTranslator()
			->setPrompt('')
			->setOption(IComponentMapper::ITEMS_TITLE, 'email');

		$page->addSelect('language', 'Language')
			->setTranslator()
			->setPrompt('All')
			->setOption(IComponentMapper::ITEMS_TITLE, 'name');

		$page->addTextArea('text', 'Text');
		return $form;
	}

}
