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

use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LanguageFormFactory implements \Venne\Forms\IFormFactory
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

		$form->addText('name', 'Name')
			->setOption('description', '(english, deutsch,...)')
			->addRule($form::FILLED, 'Please set name');

		$form->addText('short', 'Short')
			->setOption('description', '(en, de,...)')
			->addRule($form::FILLED, 'Please set short');

		$form->addText('alias', 'Alias')
			->setOption('description', '(www, en, de,...)')
			->addRule($form::FILLED, 'Please set alias');

		return $form;
	}

}
