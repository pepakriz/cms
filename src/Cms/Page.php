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
use Nette\InvalidArgumentException;
use Nette\Security\User as NetteUser;
use Nette\Utils\Callback;
use Nette\Utils\Random;
use Nette\Utils\Strings;
use Venne\Files\Dir;
use Venne\Files\File;
use Venne\Security\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\EntityListeners({"\Venne\Cms\Listeners\ExtendedPageListener"})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="page", indexes={
 * @ORM\Index(name="presenter_action_idx", columns={"presenter", "action"}),
 * @ORM\Index(name="url_idx", columns={"url"}),
 * @ORM\Index(name="expired_idx", columns={"expired"}),
 * @ORM\Index(name="released_idx", columns={"released"}),
 * @ORM\Index(name="class_idx", columns={"class"}),
 * @ORM\Index(name="positionString_idx", columns={"position_string"}),
 * })
 *
 * @method addSlavePage(\Venne\Cms\Page $page)
 * @method removeSlavePage(\Venne\Cms\Page $page)
 * @method setPresenter($presenter)
 * @method string getPresenter($presenter)
 * @method setAction($action)
 * @method string getAction($action)
 * @method \Venne\Cms\Page[] getChildren()
 * @method \Venne\Cms\Page getParent()
 */
class Page extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	const CHANGE_URL = 1;

	const CHANGE_PARENT = 2;

	const CHANGE_POSITION = 4;

	const CHANGE_DOMAIN = 8;

	const CHANGE_LAYOUT = 16;

	/** @var string[] */
	private static $robotsValues = array(
		'index, follow',
		'noindex, follow',
		'index, nofollow',
		'noindex, nofollow',
	);

	/** @var string[] */
	private static $changefreqValues = array(
		'always',
		'hourly',
		'daily',
		'weekly',
		'monthly',
		'yearly',
		'never',
	);

	/** @var integer[] */
	private static $priorityValues = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);

	/***************** URL & presenter arguments *******************/

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $presenter;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $action;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $url;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $localUrl;

	/***************** Associations *******************/

	/**
	 * @var \Venne\Cms\Domain
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Cms\Domain")
	 * @ORM\JoinColumn(referencedColumnName="domain", nullable=false, onDelete="CASCADE")
	 */
	private $domain;

	/**
	 * @var \Venne\Cms\Language
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Cms\Language")
	 * @ORM\JoinColumn(name="language_id", referencedColumnName="alias")
	 */
	protected $language;

	/**
	 * @var \Venne\Cms\Layout
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Cms\Layout", cascade={"persist"})
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $layout;

	/**
	 * @var \Venne\Cms\Layout
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Cms\Layout", cascade={"persist"})
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $childrenLayout;

	/**
	 * @var \Venne\Cms\PageTranslation[]|\Doctrine\Common\Collections\ArrayCollection
	 * @ORM\OneToMany(targetEntity="\Venne\Cms\PageTranslation", mappedBy="object", indexBy="language", cascade={"persist"}, fetch="EXTRA_LAZY")
	 */
	protected $translations;

	/**
	 * @var \Venne\Cms\Page
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Cms\Page", inversedBy="slavePages", cascade={"persist"})
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $masterPage;

	/**
	 * @var \Venne\Cms\Page[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="\Venne\Cms\Page", mappedBy="masterPage", cascade={"persist"})
	 */
	protected $slavePages;

	/***************** Tree *******************/

	/**
	 * @var \Venne\Cms\Page
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Cms\Page", inversedBy="children")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $parent;

	/**
	 * @var \Venne\Cms\Page|null
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Cms\Page", inversedBy="previous")  # ManyToOne is hack for prevent '1062 Duplicate entry update'
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	private $next;

	/**
	 * @var \Venne\Cms\Page[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="\Venne\Cms\Page", mappedBy="next")
	 */
	private $previous;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 */
	protected $position;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $positionString = '';

	/**
	 * @var \Venne\Cms\Page[]
	 *
	 * @ORM\OneToMany(targetEntity="\Venne\Cms\Page", mappedBy="parent", cascade={"persist"})
	 * @ORM\OrderBy({"position" = "ASC"})
	 */
	protected $children;

	/***************** Directory *******************/

	/**
	 * @var \Venne\Files\Dir
	 *
	 * @ORM\OneToOne(targetEntity="\Venne\Files\Dir", cascade={"all"})
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $dir;

	/***************** Publish & dates *******************/

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $published = false;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime")
	 */
	protected $created;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime")
	 */
	protected $released;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime")
	 */
	protected $updated;

	/**
	 * @var \DateTime|null
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $expired;

	/***************** Meta *******************/

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	private $name;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	private $optionString;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $text;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $notation;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $keywords;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $description;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	private $navigationTitle;

	/**
	 * @var \Venne\Security\User|null
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\User")
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	private $author;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $robots;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $changefreq;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $priority;

	/**
	 * @var \Venne\Files\File
	 *
	 * @ORM\OneToOne(targetEntity="\Venne\Files\File", cascade={"all"}, orphanRemoval=true)
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $photo;

	/***************** Extended page *******************/

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $class;

	/**
	 * @var \Venne\Cms\ExtendedPage
	 */
	protected $extendedPage;

	/**
	 * @var callable
	 */
	private $extendedPageCallback;

	/***************** Security *******************/

	/**
	 * @var \Venne\Cms\Permission[]|\Doctrine\Common\Collections\ArrayCollection
	 * @ORM\OneToMany(targetEntity="Permission", mappedBy="page", indexBy="name", orphanRemoval=true, cascade={"all"})
	 */
	protected $permissions;

	/**
	 * @var \Venne\Cms\AdminPermission[]|\Doctrine\Common\Collections\ArrayCollection
	 * @ORM\OneToMany(targetEntity="AdminPermission", mappedBy="page", indexBy="name", orphanRemoval=true, cascade={"all"})
	 */
	protected $adminPermissions;

	/***************** Options *******************/

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $showInNavigation = true;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $copyLayoutFromParent = true;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $copyCacheModeFromParent = true;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $copyLayoutToChildren = true;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $secured = false;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $adminSecured = false;

	/***************** Other *******************/

	/**
	 * @var \Venne\Cms\Language|null
	 */
	private $locale;

	/** @var array */
	private $isAllowed = array();

	/** @var array */
	private $isAllowedInAdmin = array();

	/** @var integer */
	private $changes = 0;

	/** @var boolean */
	private $isInSetNext = false;

	/** @var boolean */
	private $isInSetPrevious = false;

	public function __construct(ExtendedPage $page)
	{
		parent::__construct();

		$this->children = new ArrayCollection;
		$this->previous = new ArrayCollection;
		$this->permissions = new ArrayCollection;
		$this->adminPermissions = new ArrayCollection;
		$this->translations = new ArrayCollection;
		$this->created = new \DateTime;
		$this->updated = new \DateTime;
		$this->released = new \DateTime;
		$this->class = get_class($page);
		$this->translations = new ArrayCollection;

		$this->dir = new Dir;
		$this->dir->setInvisible(true);
		$this->dir->setName(Strings::webalize(self::class) . Random::generate());
	}

	/**
	 * @return string
	 */
	public function getOptionString()
	{
		return $this->optionString;
	}



	/**
	 * @param integer $change
	 */
	public function appendChange($change)
	{
		$this->changes |= $change;
//		$this->onPreUpdate();
	}

	/**
	 * @return \Venne\Cms\Language
	 */
	public function getLocale()
	{
		return $this->locale;
	}

	/**
	 * @param \Venne\Cms\Language|null $locale
	 */
	public function setLocale(Language $locale = null)
	{
		$this->locale = $locale;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->name . ' (' . $this->url . ')';
	}

	/**
	 * @param callable $extendedPageCallback
	 */
	public function setExtendedPageCallback($extendedPageCallback)
	{
		$this->extendedPageCallback = $extendedPageCallback;
	}

	/**
	 * @return \Venne\Cms\ExtendedPage
	 */
	public function getExtendedPage()
	{
		if (!$this->extendedPage) {
			$this->extendedPage = Callback::invoke($this->extendedPageCallback);
		}

		return $this->extendedPage;
	}

	/**
	 * @ORM\PreUpdate
	 * @ORM\PrePersist
	 */
	public function onPreUpdate()
	{
		if ($this->position === null) {
			$this->setPosition(1);
		}

		if (($this->changes & (self::CHANGE_DOMAIN | self::CHANGE_PARENT | self::CHANGE_URL)) !== 0) {
			$this->generateUrl(true);
		}

		if (($this->changes & (self::CHANGE_LAYOUT | self::CHANGE_PARENT)) !== 0) {
			$this->generateLayouts(true);
		}

		$this->changes = 0;
	}

	/**
	 * @ORM\PreRemove
	 */
	public function onPreRemove()
	{
		$this->removeFromPosition();
	}

	/**
	 * @param \Venne\Cms\Page|null $masterPage
	 */
	public function setMasterPage(Page $masterPage = null)
	{
		if ($this->masterPage === $masterPage) {
			return;
		}

		$this->masterPage->removeSlavePage($this);
		$this->masterPage = $masterPage;
		$this->masterPage->addSlavePage($this);
	}

	/**
	 * @param \Venne\Cms\Domain $domain
	 */
	public function setDomain(Domain $domain = null)
	{
		foreach ($this->children as $children) {
			$children->setDomain($domain, true);
		}

		$this->domain = $domain;
		$this->appendChange(self::CHANGE_DOMAIN);
	}

	/**
	 * @return Domain
	 */
	public function getDomain()
	{
		return $this->domain;
	}

	/**
	 * @return null|\Venne\Security\User
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * @param null|\Venne\Security\User $author
	 */
	public function setAuthor(User $author = null)
	{
		$this->author = $author;
	}

	/**
	 * @param \Venne\Cms\Page $next
	 * @internal
	 */
	public function setNext(Page $next = null)
	{
		if ($this->isInSetNext) {
			return;
		}

		$this->isInSetNext = true;

		if ($next === $this) {
			throw new InvalidArgumentException('Next page is the same as current page.');
		}

		if ($this->next !== null) {
			$this->next->setPrevious(null);
		}

		$this->next = $next;

		if ($this->next !== null) {
			$this->next->setPrevious($this);
		}

		$this->isInSetNext = false;
		$this->appendChange(self::CHANGE_POSITION);
	}

	/**
	 * @return \Venne\Cms\Page|null
	 */
	public function getNext()
	{
		return $this->next;
	}

	/**
	 * @param \Venne\Cms\Page $previous
	 * @internal
	 */
	public function setPrevious(Page $previous = null)
	{
		if ($this->isInSetPrevious) {
			return;
		}

		$this->isInSetPrevious = true;

		if ($previous === $this) {
			throw new InvalidArgumentException('Previous page is the same as current page.');
		}

		if (count($this->previous) > 0) {
			$this->previous->first()->setNext(null);
		}

		$this->previous->clear();

		if ($previous !== null) {
			$this->previous[] = $previous;
			$previous->setNext($this);
		}

		$this->isInSetPrevious = false;
		$this->appendChange(self::CHANGE_POSITION);
	}

	/**
	 * @return \Venne\Cms\Page|null
	 */
	public function getPrevious()
	{
		return $this->previous->count() > 0
			? $this->previous->first()
			: null;
	}

	/**
	 * @param \Venne\Files\File|null $photo
	 */
	public function setPhoto(File $photo = null)
	{
		$this->photo = $photo;

		if ($this->photo !== null) {
			$this->photo->setParent($this->dir);
			$this->photo->setInvisible(true);
		}
	}

	/**
	 * @param \Venne\Cms\Layout $layout
	 */
	public function setLayout(Layout $layout = null)
	{
		if ($layout === null && $this->layout === null) {
			return $this;
		}

		if ($layout && $this->layout && $layout === $this->layout) {
			return $this;
		}

		$this->layout = $layout;
		$this->appendChange(self::CHANGE_LAYOUT);
	}

	/**
	 * @param \Venne\Cms\Layout|null $childrenLayout
	 */
	public function setChildrenLayout(Layout $childrenLayout = null)
	{
		if ($childrenLayout === null && $this->childrenLayout === null) {
			return;
		}

		if ($childrenLayout && $this->childrenLayout && $childrenLayout === $this->childrenLayout) {
			return;
		}

		$this->childrenLayout = $childrenLayout;
		$this->appendChange(self::CHANGE_LAYOUT);
	}

	/**
	 * @param boolean $copyLayoutFromParent
	 */
	public function setCopyLayoutFromParent($copyLayoutFromParent)
	{
		if ($this->copyLayoutFromParent === $copyLayoutFromParent) {
			return;
		}

		$this->copyLayoutFromParent = (bool) $copyLayoutFromParent;
		$this->appendChange(self::CHANGE_LAYOUT);
	}

	/**
	 * @param boolean $copyLayoutToChildren
	 */
	public function setCopyLayoutToChildren($copyLayoutToChildren)
	{
		if ($this->copyLayoutToChildren == $copyLayoutToChildren) {
			return;
		}

		$this->copyLayoutToChildren = (bool) $copyLayoutToChildren;
		$this->appendChange(self::CHANGE_LAYOUT);
	}

	/**
	 * @param integer $position
	 */
	public function setPosition($position)
	{
		if ($this->position === $position && $this->parent !== null && $this->parent->getPositionString() === substr($this->positionString, 0, -4)) {
			return;
		}

		$this->position = $position;
		$this->positionString = ($this->parent ? $this->parent->getPositionString() . ';' : '') . str_pad($this->position, 3, '0', STR_PAD_LEFT);

		if ($this->next !== null) {
			$this->next->setPosition($position + 1);
		}

		if (count($this->children) > 0) {
			$this->children[0]->setPosition(1);
		}
	}

	/**
	 * Set this page as root.
	 */
	public function setAsRoot()
	{
		$main = $this->getRoot();
		$this->setParent(null);
		$main->setParent($this);

		foreach ($main->children as $item) {
			$item->setParent($this);
		}
	}

	public function addChild(Page $page)
	{
		$this->children[] = $page;
	}

	public function removeChild(Page $page)
	{
		$this->children->removeElement($page);
	}

	/**
	 * @param \Venne\Cms\Page|null $parent
	 * @param boolean $setPrevious
	 * @param \Venne\Cms\Page|null $previous
	 */
	public function setParent(Page $parent = null, $setPrevious = false, Page $previous = null)
	{
		if ($parent === $this->parent && !$setPrevious) {
			return;
		}

		if (!$parent && !$this->next && $this->getPrevious() === null && !$this->parent && !$setPrevious) {
			return;
		}

		if ($setPrevious && $previous === $this) {
			throw new InvalidArgumentException('Previous page is the same as current page.');
		}

		$oldParent = $this->parent;
		$oldPrevious = $this->getPrevious();
		$oldNext = $this->next;

		$this->removeFromPosition();

		if ($parent) {
			$this->parent = $parent;

			if ($setPrevious) {
				if ($previous) {
					$this->setNext($previous->getNext());
					$this->setPrevious($previous);
				} else {
					$pChildren = $parent->getChildren();
					$first = reset($pChildren);
					$this->setNext($first !== false ? $first : null);
					$this->setPrevious(null);
				}
			} else {
				$pChildren = $parent->getChildren();
				$last = end($pChildren);
				$this->setNext(null);
				$this->setPrevious($last !== false ? $last : null);
			}

			$parent->addChild($this);
		} else {
			if ($setPrevious) {
				if ($previous) {
					$this->setNext($previous->getNext());
					$this->setPrevious($previous);
				} else {
					$this->setNext($this->getRoot($oldNext ?: ($oldParent ?: ($oldPrevious))));
					$this->setPrevious(null);
				}
			} else {
				$this->parent = null;
				$this->previous->clear();
				$this->next = null;
			}
		}

		$this->setPosition($this->getPrevious() !== null ? $this->getPrevious()->getPosition() + 1 : 1);
		$this->appendChange(self::CHANGE_PARENT);
	}

	/**
	 * @param string $navigationTitle
	 */
	public function setNavigationTitle($navigationTitle)
	{
		$this->setTranslatedValue('navigationTitle', $navigationTitle);
	}

	/**
	 * @return string
	 */
	public function getNavigationTitle()
	{
		return $this->getTranslatedValue('navigationTitle');
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->getTranslatedValue('url');
	}

	/**
	 * @param $localUrl
	 */
	public function setLocalUrl($localUrl)
	{
		$this->setTranslatedValue('localUrl', $localUrl);
		$this->appendChange(self::CHANGE_URL);
	}

	/**
	 * @return string
	 */
	public function getLocalUrl()
	{
		return $this->getTranslatedValue('localUrl');
	}

	/**
	 * @param string $text
	 */
	public function setText($text)
	{
		if ($this->text === $text) {
			return;
		}

		$this->setTranslatedValue('text', $text);
		$this->generateDate();
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->getTranslatedValue('text');
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->setTranslatedValue('name', $name);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->getTranslatedValue('name');
	}

	/**
	 * @param string $notation
	 */
	public function setNotation($notation)
	{
		$this->setTranslatedValue('notation', $notation);
	}

	/**
	 * @return string
	 */
	public function getNotation()
	{
		return $this->getTranslatedValue('notation');
	}

	/**
	 * @param string $keywords
	 */
	public function setKeywords($keywords)
	{
		$this->setTranslatedValue('keywords', $keywords);
	}

	/**
	 * @return string
	 */
	public function getKeywords()
	{
		return $this->getTranslatedValue('keywords');
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->setTranslatedValue('title', $title);
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->getTranslatedValue('title');
	}

	/**
	 * @param \Venne\Cms\Permission $permission
	 */
	public function addPermissions(Permission $permission)
	{
		$this->permissions[] = $permission;
		$this->isAllowed = array();
	}

	/**
	 * @param \Nette\Security\User $user
	 * @param $permission
	 * @return bool
	 */
	private function baseIsAllowed(& $secured, & $source, & $cache, NetteUser $user, $permission)
	{
		if (!$secured) {
			return true;
		}

		if (!isset($cache[$user->id][$permission])) {

			if (!isset($cache[$user->id])) {
				$cache[$user->id] = array();
			}

			if ($user->isInRole('admin')) {
				$cache[$user->id][$permission] = true;

				return true;

			}

			if (isset($source[$permission])) {
				$permissionEntity = $source[$permission];

				if (!$user->isLoggedIn()) {
					$cache[$user->id][$permission] = false;

					return false;
				}

				if ($permissionEntity->getAll()) {
					$cache[$user->id][$permission] = true;

					return true;
				}

				foreach ($user->getRoles() as $role) {
					if (isset($permissionEntity->roles[$role])) {
						$cache[$user->id][$permission] = true;

						return true;
					}
				}
			}
			$cache[$user->id][$permission] = false;
		}

		return $cache[$user->id][$permission];
	}

	/**
	 * @param \Nette\Security\User $user
	 * @param $permission
	 * @return boolean
	 */
	public function isAllowed(NetteUser $user, $permission)
	{
		return $this->baseIsAllowed($this->secured, $this->permissions, $this->isAllowed, $user, $permission);
	}

	/**
	 * @param \Nette\Security\User $user
	 * @param $permission
	 * @return boolean
	 */
	public function isAllowedInBackend(NetteUser $user, $permission)
	{
		return $this->baseIsAllowed($this->adminSecured, $this->adminPermissions, $this->isAllowedInAdmin, $user, $permission);
	}

	/**
	 * @param \Venne\Cms\Page|null $entity
	 * @return \Venne\Cms\Page
	 */
	private function getRoot(Page $entity = null)
	{
		$entity = $entity ?: $this;

		while ($entity->getParent()) {
			$entity = $entity->parent;
		}

		while ($entity->getPrevious()) {
			$entity = $entity->getPrevious();
		}

		return $entity;
	}

	/**
	 * @internal
	 */
	public function removeFromPosition()
	{
		if ($this->getPrevious() === null && !$this->next && !$this->parent) {
			return;
		}

		$next = $this->next;
		$previous = $this->getPrevious();

		if ($this->parent !== null) {
			$this->parent->removeChild($this);
		}

		if ($next !== null) {
			$next->setPrevious($previous, false);
			$next->setPosition($previous !== null ? $previous->getPosition() + 1 : 1);
		}

		if ($previous !== null) {
			$previous->setNext($next, false);
		}

		$this->setPrevious(null);
		$this->parent = null;
		$this->setNext(null);
	}

	/**
	 * @param bool $recursively
	 */
	public function generateUrl($recursively = true)
	{
		if (!$this->parent) {
			$this->setTranslatedValue('url', '');
		} else {
			$l = $this->parent->getLocale();
			$this->parent->setLocale($this->locale);

			if ($this->domain && !$this->parent->domain) {
				$this->setTranslatedValue('url', '');
			} else {
				$this->setTranslatedValue('url', trim($this->parent->getUrl() . '/' . $this->getTranslatedValue('localUrl'), '/'));
			}

			$this->parent->setLocale($l);
		}

		$this->generateOptionString();

		if ($recursively) {
			foreach ($this->children as $child) {
				$child->generateUrl();
			}
		}
	}

	private function generateOptionString()
	{
		$floor = substr_count($this->positionString, ';');

		$this->optionString = $floor < 1
			? $this->__toString()
			: str_repeat('....', $floor - 1) . ($floor > 0 ? '+-..' : '') . $this->__toString();
	}

	/**
	 * @param boolean $recursively
	 */
	public function generateLayouts($recursively = true)
	{
		if ($this->copyLayoutFromParent) {
			$this->layout = $this->parent ? ($this->parent->copyLayoutToChildren ? $this->parent->layout : $this->parent->childrenLayout) : null;
		}

		if ($this->copyLayoutToChildren) {
			$this->childrenLayout = $this->layout;
		}

		if ($recursively) {
			foreach ($this->children as $child) {
				$child->generateLayouts();
			}
		}
	}

	private function generateDate()
	{
		$this->updated = new \DateTime();
	}

	/**
	 * @param string $field
	 * @param \Venne\Cms\Language|null $language
	 * @return string
	 */
	protected function getTranslatedValue($field, Language $language = null)
	{
		$language = $language ?: $this->locale;

		if ($language && $this->translations[$language->id]) {
			if (($ret = $this->translations[$language->id]->{$field}) !== null) {
				return $ret;
			}
		}

		return $this->{$field};
	}

	/**
	 * @param string $field
	 * @param string $value
	 * @param \Venne\Cms\Language|null $language
	 */
	protected function setTranslatedValue($field, $value, Language $language = null)
	{
		$language = $language ?: $this->locale;

		if ($language) {
			if (!isset($this->translations[$language->id])) {
				if ($value === null || $this->{$field} === $value) {
					return;
				}

				$this->translations[$language->id] = new PageTranslation($this, $language);
			}
			$this->translations[$language->id]->{$field} = $value ?: null;
		} else {
			$this->{$field} = $value;
		}
	}

	/**
	 * @param string $name
	 * @param string $value
	 */
	public function setValueForAllTranslations($name, $value)
	{
		$method = 'set' . ucfirst($name);

		$reflection = new \ReflectionMethod($this, $method);
		if (!$reflection->isPublic()) {
			throw new \RuntimeException('The called method is not public.');
		}

		$locale = $this->locale;
		$this->locale = null;
		call_user_func(array($this, $method), $value);
		foreach ($this->translations as $translation) {
			$this->locale = $translation->getLanguage();
			call_user_func(array($this, $method), $value);
		}
		$this->locale = $locale;
	}

	/**
	 * @return string[]
	 */
	public static function getChangefreqValues()
	{
		return self::$changefreqValues;
	}

	/**
	 * @return integer[]
	 */
	public static function getPriorityValues()
	{
		return self::$priorityValues;
	}

	/**
	 * @return string[]
	 */
	public static function getRobotsValues()
	{
		return self::$robotsValues;
	}

}
