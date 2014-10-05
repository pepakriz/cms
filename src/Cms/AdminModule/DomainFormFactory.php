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
class DomainFormFactory implements IFormFactory
{

	/** @var IFormFactory */
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

		$form->addGroup();
		$form->addText('domain', 'Domain')
			->addRule($form::FILLED);
		$form->addSelect('defaultLanguage', 'Default language')
			->setTranslator()
			->setOption(IComponentMapper::ITEMS_TITLE, 'name');

		$form->addGroup('Default metadata');
		$form->addText('defaultKeywords', 'Keywords');
		$form->addText('defaultDescription', 'Description');
		$form->addText('defaultAuthor', 'Author');
		$form->addText('titleMask', 'Title mask');

		return $form;
	}

}
