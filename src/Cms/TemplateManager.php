<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms;

use Nette\Utils\Finder;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TemplateManager extends \Nette\Object
{

	/** @var mixed[] */
	private $packages;

	/**
	 * @param mixed[] $modules
	 */
	public function __construct($modules)
	{
		$this->packages = &$modules;
	}

	/**
	 * @param string $package
	 * @return array
	 */
	public function getLayoutsByPackage($package)
	{
		$data = array();
		$path = $this->packages[$package]['path'] . '/Resources/layouts';

		if (file_exists($path)) {
			foreach (Finder::findDirectories('*')->in($path) as $file) {
				if (file_exists($file->getPathname() . '/@layout.latte')) {
					$package = str_replace('/', '.', $package);
					$data[$file->getBasename()] = sprintf(
						'@%s/%s/@layout.latte',
						$package,
						$file->getBasename()
					);
				}
			}
		}

		return $data;
	}

	/**
	 * @param $package
	 * @param null $layout
	 * @param null $subdir
	 * @return array
	 */
	public function getTemplatesByPackage($package, $layout = null, $subdir = null)
	{
		$data = array();

		$prefix = ($layout ? '/' . $layout : '');
		$suffix = ($subdir ? '/' . $subdir : '');
		$path = $this->packages[$package]['path'] . '/Resources/layouts' . $prefix . $suffix;

		if (file_exists($path)) {
			foreach (Finder::find('*')->in($path) as $file) {
				if ($file->getBasename() === '@layout.latte' || !is_file($file->getPathname())) {
					continue;
				}
				$p = str_replace('/', '.', $subdir);
				$data[($p ? $p . '.' : '') . substr($file->getBasename(), 0, -6)] = sprintf(
					'@%sModule%s%s/%s',
					$package,
					$prefix,
					$suffix,
					$file->getBasename()
				);
			}
		}

		return $data;
	}

	/**
	 * Get layouts formated for selectbox.
	 *
	 * @return array
	 */
	public function getLayouts()
	{
		$data = array();

		foreach ($this->packages as $name => $item) {
			if ($layouts = $this->getLayoutsByPackage($name)) {
				$data[$name] = array();
				foreach ($layouts as $layout => $file) {
					$data[$name][$file] = $layout;
				}
			}
		}

		return $data;
	}

}
