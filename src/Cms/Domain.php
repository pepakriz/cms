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
	 * @var \Venne\Cms\Page
	 *
	 * @ORM\OneToOne(targetEntity="\Venne\Cms\Page")
	 */
	protected $mainPage;

	/**
	 * @var \Venne\Cms\Language
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Cms\Language", inversedBy="domains")
	 * @ORM\JoinColumn(name="language_id", nullable=false, referencedColumnName="alias")
	 */
	protected $defaultLanguage;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $defaultDescription;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $defaultKeywords;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $defaultAuthor;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $titleMask = '%t | %n';

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

	public function setMainPage(Page $page)
	{
		if ($this->mainPage && $this->mainPage !== $page) {
			$this->mainPage->mainRoute->setDomain(null);
		}

		$this->mainPage = $page;
		$this->mainPage->mainRoute->setDomain($this);
	}

}
