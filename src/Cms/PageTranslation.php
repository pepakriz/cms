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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="page_translation")
 */
class PageTranslation extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	/**
	 * @var \Venne\Cms\Page
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Cms\Page", inversedBy="translations")
	 * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $object;

	/**
	 * @var \Venne\Cms\Language
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Cms\Language")
	 * @ORM\JoinColumn(name="language", referencedColumnName="alias", onDelete="CASCADE")
	 */
	protected $language;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $url;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $localUrl;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $name;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $notation;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $title;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $keywords;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $description;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $text;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $navigationTitle;

	/**
	 * @param Page $object
	 * @param Language $language
	 */
	public function __construct(Page $object, Language $language)
	{
		$this->object = $object;
		$this->language = $language;
	}

}
