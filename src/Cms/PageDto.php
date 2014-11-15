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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read integer $id
 * @property-read string $name
 * @property-read string|null $notation
 * @property-read string $text
 * @property-read string $domain
 * @property-read string $navigationTitle
 * @property-read string $url
 * @property-read boolean $showInNavigation
 */
class PageDto extends \Venne\DataTransfer\DataTransferObject
{

	public function getDomain()
	{
		$domain = $this->getRawValue('domain');

		if (is_string($domain)) {
			return $domain;
		}

		return $domain !== null ? $domain->getName() : '';
	}

}
