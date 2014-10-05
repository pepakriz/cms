<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms\SideComponents;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
interface PagesControlFactory
{

	/**
	 * @return \Venne\Cms\SideComponents\PagesControl
	 */
	public function create();

}
