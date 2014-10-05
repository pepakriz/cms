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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="language")
 */
class Language extends \Kdyby\Doctrine\Entities\BaseEntity
{

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(type="string", unique=true, length=32)
	 */
	private $alias;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", unique=true, length=32)
	 */
	private $name;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", unique=true, length=32)
	 */
	private $short;

	/**
	 * @var \Venne\Cms\Domain[]|\Doctrine\Common\Collections\ArrayCollection
	 * @ORM\OneToMany(targetEntity="\Venne\Cms\Domain", mappedBy="defaultLanguage")
	 */
	private $domains;

	public function __construct()
	{
		$this->domains = new ArrayCollection();
	}

	/**
	 * @param string $alias
	 */
	public function setAlias($alias)
	{
		$this->alias = $alias;
	}

	/**
	 * @return string
	 */
	public function getAlias()
	{
		return $this->alias;
	}

	public function __toString()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $short
	 */
	public function setShort($short)
	{
		$this->short = $short;
	}

	/**
	 * @return string
	 */
	public function getShort()
	{
		return $this->short;
	}

	public function addDomain(Domain $domain)
	{
		$this->domains[] = $domain;
	}

	public function removeDomain(Domain $domain)
	{
		$this->domains->removeElement($domain);
	}

	/**
	 * @return \Venne\Cms\Domain[]
	 */
	public function getDomains()
	{
		return $this->domains->toArray();
	}

}
