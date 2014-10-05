<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms\Listeners;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Venne\Cms\Page;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ExtendedPageListener
{

	/** @ORM\PostLoad */
	public function postLoadHandler(Page $page, LifecycleEventArgs $event)
	{
		$em = $event->getEntityManager();
		$page->setExtendedPageCallback(function () use ($em, $page) {
			return $em->getRepository($page->getClass())->findOneBy(array('page' => $page->id));
		});
	}

}
