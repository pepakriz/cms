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

use Nette\DI\Statement;
use Venne\Cms\AdminModule\Insite\InsiteSectionFactory;
use Venne\Cms\AdminModule\Insite\PreviewSectionFactory;
use Venne\Cms\AdminModule\SeoSectionFactory;
use Venne\Cms\PageManager;
use Venne\Cms\TextPage\AdminModule\TextSectionFactory;
use Venne\Cms\TextPage\Page;
use Venne\System\DI\SystemExtension;
use Venne\Widgets\DI\WidgetsExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CmsExtension extends \Nette\DI\CompilerExtension implements
	\Kdyby\Doctrine\DI\IEntityProvider,
	\Venne\System\DI\IPresenterProvider,
	\Kdyby\Translation\DI\ITranslationProvider,
	\Venne\Cms\DI\PageTypeProvider,
	\Venne\Cms\DI\GlobalSectionProvider,
	\Venne\System\DI\ICssProvider
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

		$container->addDefinition($this->prefix('navigationControlFactory'))
			->setImplement('Venne\Cms\Components\NavigationControlFactory')
			->setInject(true)
			->addTag(WidgetsExtension::TAG_WIDGET, 'navigation');

		$container->addDefinition($this->prefix('pageManager'))
			->setClass(PageManager::class);

		$container->addDefinition($this->prefix('textSectionFactory'))
			->setClass(TextSectionFactory::class);

		$container->addDefinition($this->prefix('seoSectionFactory'))
			->setClass(SeoSectionFactory::class);

		$container->addDefinition($this->prefix('insiteSectionFactory'))
			->setClass(InsiteSectionFactory::class);

		$container->addDefinition($this->prefix('previewSectionFactory'))
			->setClass(PreviewSectionFactory::class);

		$this->registerPages();
	}

	private function registerPages()
	{
		$container = $this->getContainerBuilder();
		$config = $container->getDefinition($this->prefix('pageManager'));

		foreach ($this->compiler->extensions as $extension) {
			if ($extension instanceof PageTypeProvider) {
				foreach ($extension->getPageTypes() as $type) {
					$config->addSetup(
						'$service->addPageType($pageType = new Venne\Cms\PageType(?, ?, ?));',
						$type->getArguments()
					);
				}
			}
			if ($extension instanceof GlobalSectionProvider) {
				foreach ($extension->getGlobalSections() as $globalSection) {
					$config->addSetup(
						'$service->addGlobalSection(?, ?);',
						$globalSection->getArguments()
					);
				}
			}
		}
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
			'Front:Blog' => 'Venne\Cms\*\FrontModule\*Presenter',
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

	/**
	 * @return string[]
	 */
	public function getCssFiles()
	{
		return array(
			'@venne.cms/css/breadcrumb.min.css',
		);
	}

	/**
	 * @return \Venne\Cms\DI\PageType[]
	 */
	public function getPageTypes()
	{
		$pageType = new PageType('Static page', Page::class);
		$pageType->addSection('Text', Page::class, new Statement('@' . TextSectionFactory::class));

		$blog = new PageType('Blog', \Venne\Cms\Blog\Page::class);
		$blog->addSection('Text', \Venne\Cms\Blog\Page::class, new Statement('@' . TextSectionFactory::class));

		return array(
			$pageType,
			$blog,
		);
	}

	/**
	 * @return \Venne\Cms\DI\GlobalSection[]
	 */
	public function getGlobalSections()
	{
		return array(
			new GlobalSection('SEO', new Statement('@' . SeoSectionFactory::class)),
			new GlobalSection('In-site editation', new Statement('@' . InsiteSectionFactory::class)),
			new GlobalSection('Preview', new Statement('@' . PreviewSectionFactory::class)),
		);
	}

}
