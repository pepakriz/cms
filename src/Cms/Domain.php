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

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="domain")
 */
class Domain extends \Kdyby\Doctrine\Entities\BaseEntity
{

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(type="string")
	 */
	private $domain;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	private $name;

	/**
	 * @var \Venne\Cms\Page
	 *
	 * @ORM\OneToOne(targetEntity="\Venne\Cms\Page")
	 */
	private $mainPage;

	/**
	 * @var \Venne\Cms\Language
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Cms\Language", inversedBy="domains")
	 * @ORM\JoinColumn(name="language_id", nullable=false, referencedColumnName="alias")
	 */
	private $defaultLanguage;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $defaultDescription;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $defaultKeywords;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $defaultAuthor;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string")
	 */
	private $titleMask = '%t | %n';

	/**
	 * @param string $domain
	 */
	public function setDomain($domain)
	{
		$this->domain = $domain;
	}

	/**
	 * @return string
	 */
	public function getDomain()
	{
		return $this->domain;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = (string) $name;
	}

	public function setMainPage(Page $page)
	{
		if ($this->mainPage && $this->mainPage !== $page) {
			$this->mainPage->mainRoute->setDomain(null);
		}

		$this->mainPage = $page;
		$this->mainPage->mainRoute->setDomain($this);
	}

	/**
	 * @return Page
	 */
	public function getMainPage()
	{
		return $this->mainPage;
	}

	public function setDefaultLanguage(Language $defaultLanguage)
	{
		$this->defaultLanguage = $defaultLanguage;
	}

	/**
	 * @return Language
	 */
	public function getDefaultLanguage()
	{
		return $this->defaultLanguage;
	}

	/**
	 * @param string|null $defaultAuthor
	 */
	public function setDefaultAuthor($defaultAuthor)
	{
		$this->defaultAuthor = $defaultAuthor ?: null;
	}

	/**
	 * @return string|null
	 */
	public function getDefaultAuthor()
	{
		return $this->defaultAuthor;
	}

	/**
	 * @param string|null $defaultDescription
	 */
	public function setDefaultDescription($defaultDescription)
	{
		$this->defaultDescription = $defaultDescription ?: null;
	}

	/**
	 * @return string|null
	 */
	public function getDefaultDescription()
	{
		return $this->defaultDescription;
	}

	/**
	 * @param string|null $defaultKeywords
	 */
	public function setDefaultKeywords($defaultKeywords)
	{
		$this->defaultKeywords = $defaultKeywords ?: null;
	}

	/**
	 * @return string|null
	 */
	public function getDefaultKeywords()
	{
		return $this->defaultKeywords;
	}

	/**
	 * @param null|string $titleMask
	 */
	public function setTitleMask($titleMask)
	{
		$this->titleMask = (string) $titleMask;
	}

	/**
	 * @return null|string
	 */
	public function getTitleMask()
	{
		return $this->titleMask;
	}

}
