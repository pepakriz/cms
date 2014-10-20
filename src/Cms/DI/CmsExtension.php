<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms\DI;

use Venne\System\DI\SystemExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CmsExtension extends \Nette\DI\CompilerExtension implements
	\Kdyby\Doctrine\DI\IEntityProvider,
	\Venne\System\DI\IPresenterProvider,
	\Kdyby\Translation\DI\ITranslationProvider
{

	/** @var array */
	public $defaults = array();

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$this->compiler->parseServices(
			$container,
			$this->loadFromFile(__DIR__ . '/services.neon')
		);

		$container->getDefinition('nette.latteFactory')
			->addSetup('?->onCompile[] = function($engine) { Venne\Cms\Macros\ContentMacro::install($engine->getCompiler()); }', array('@self'));

		$container->addDefinition($this->prefix('domainsPresenter'))
			->setClass('Venne\Cms\AdminModule\DefaultPresenter')
			->addTag(SystemExtension::TAG_ADMINISTRATION, array(
				'link' => 'Admin:Cms:Default:',
				'category' => 'Content',
				'name' => 'Content settings',
				'description' => 'Manage website domains, languages and tags',
				'priority' => 150,
			));

		$container->addDefinition($this->prefix('contentPresenter'))
			->setClass('Venne\Cms\AdminModule\ContentPresenter')
			->addTag(SystemExtension::TAG_ADMINISTRATION, array(
				'link' => 'Admin:Cms:Content:',
				'category' => 'Content',
				'name' => 'Content',
				'description' => 'Manage content',
				'priority' => 160,
			));

		$container->addDefinition($this->prefix('pagesControlFactory'))
			->setImplement('Venne\Cms\SideComponents\PagesControlFactory')
			->setInject(true)
			->addTag(SystemExtension::TAG_SIDE_COMPONENT, array(
				'name' => 'Pages',
				'description' => 'Pages',
				'args' => array(
					'icon' => 'fa fa-pencil',
				),
			));

		$container->addDefinition($this->prefix('frontRoute'))
			->setClass('Venne\Cms\Routers\PageRoute', array(''))
			->addTag(SystemExtension::TAG_ROUTE, array('priority' => 500));
	}

	/**
	 * @return array
	 */
	public function getEntityMappings()
	{
		return array(
			'Venne\Cms' => dirname(__DIR__) . '/*Entity.php',
		);
	}

	/**
	 * @return array
	 */
	public function getPresenterMapping()
	{
		return array(
			'Admin:Cms' => 'Venne\*\AdminModule\*Presenter',
			'Front:TextPage' => 'Venne\Cms\*\FrontModule\*Presenter',
		);
	}

	/**
	 * @return array
	 */
	public function getTranslationResources()
	{
		return array(
			__DIR__ . '/../../../Resources/lang',
		);
	}

}
