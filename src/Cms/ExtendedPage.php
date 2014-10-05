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
use Nette\Security\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\MappedSuperclass
 */
abstract class ExtendedPage extends \Kdyby\Doctrine\Entities\BaseEntity
{

	const PRIVILEGE_SHOW = 'show';

	const ADMIN_PRIVILEGE_SHOW = 'admin_show';

	const ADMIN_PRIVILEGE_PERMISSIONS = 'admin_permissions';

	const ADMIN_PRIVILEGE_ROUTES = 'admin_routes';

	const ADMIN_PRIVILEGE_PUBLICATION = 'admin_publication';

	const ADMIN_PRIVILEGE_PREVIEW = 'admin_preview';

	const ADMIN_PRIVILEGE_BASE = 'admin_base';

	const ADMIN_PRIVILEGE_REMOVE = 'admin_remove';

	const ADMIN_PRIVILEGE_CHANGE_STRUCTURE = 'admin_change_structure';

	/**
	 * @var \Venne\Cms\Page
	 *
	 * @ORM\Id
	 * @ORM\OneToOne(targetEntity="\Venne\Cms\Page", cascade={"ALL"})
	 * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $page;

	/**
	 * @var \Venne\Cms\Language|null
	 */
	protected $locale;

	public function __construct()
	{
		$this->page = $this->createPage();
		$this->page->setPresenter($this->getPresenterName());
		$this->page->setAction($this->getActionName());
		$this->startup();
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->page;
	}

	protected function startup()
	{
	}

	/**
	 * @return string
	 */
	abstract protected function getPresenterName();

	/**
	 * @return string
	 */
	protected function getActionName()
	{
		return 'default';
	}

	/**
	 * @return \Venne\Cms\Page
	 */
	public function getPage()
	{
		return $this->page;
	}

	/**
	 * @return \Venne\Cms\Page
	 */
	private function createPage()
	{
		return new Page($this);
	}

	/**
	 * @param \Venne\Cms\Language|null $locale
	 */
	public function setLocale(Language $locale = null)
	{
		$this->page->locale = $this->locale = $locale;
	}

	/**
	 * @return \Venne\Cms\Language|null
	 */
	public function getLocale()
	{
		return $this->locale;
	}

	/**
	 * @param \Nette\Security\User $user
	 * @param $permission
	 * @return boolean
	 */
	public function isAllowed(User $user, $permission)
	{
		return $this->page->isAllowed($user, $permission);
	}

	/**
	 * @param \Nette\Security\User $user
	 * @param $permission
	 * @return boolean
	 */
	public function isAllowedInBackend(User $user, $permission)
	{
		return $this->page->isAllowedInBackend($user, $permission);
	}

	/**
	 * @return string[]
	 */
	public function getPrivileges()
	{
		return array(
			self::PRIVILEGE_SHOW => 'show page',
		);
	}

	/**
	 * @return string[]
	 */
	public function getAdminPrivileges()
	{
		return array(
			self::ADMIN_PRIVILEGE_SHOW => 'show page',
			self::ADMIN_PRIVILEGE_PERMISSIONS => 'permissios',
			self::ADMIN_PRIVILEGE_PUBLICATION => 'publication',
			self::ADMIN_PRIVILEGE_PREVIEW => 'preview page',
			self::ADMIN_PRIVILEGE_ROUTES => 'edit routes',
			self::ADMIN_PRIVILEGE_BASE => 'edit base form',
			self::ADMIN_PRIVILEGE_REMOVE => 'remove page',
			self::ADMIN_PRIVILEGE_CHANGE_STRUCTURE => 'change page structure',
		);
	}

}
