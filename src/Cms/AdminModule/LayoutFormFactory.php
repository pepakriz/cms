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

use Venne\Cms\TemplateManager;
use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LayoutFormFactory implements \Venne\Forms\IFormFactory
{

	/** @var \Venne\Forms\IFormFactory */
	private $formFactory;

	/** @var TemplateManager */
	private $templateManager;

	public function __construct(IFormFactory $formFactory, TemplateManager $templateManager)
	{
		$this->formFactory = $formFactory;
		$this->templateManager = $templateManager;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	public function create()
	{
		$form = $this->formFactory->create();

		$form->addText('name', 'Name')
			->setRequired();

		$form->addSelect('file', 'file')
			->setItems($this->templateManager->getLayouts())
			->setRequired();

		return $form;
	}

}
