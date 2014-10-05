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
use Venne\Doctrine\Entities\NamedEntityTrait;
use Venne\Security\Role;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property Role[] $roles
 * @property Page $page;
 * @property bool $all
 */
class BasePermission extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use NamedEntityTrait;

	/**
	 * @var \Venne\Security\Role[]
	 *
	 * @ORM\ManyToMany(targetEntity="\Venne\Security\Role", indexBy="name")
	 * @ORM\JoinTable(name="page_permission_roles",
	 *      joinColumns={@ORM\JoinColumn(name="pagePermission_id", referencedColumnName="id", onDelete="CASCADE")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
	 *      )
	 **/
	protected $roles;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", name="allowAll")
	 */
	protected $all = true;

	/**
	 * @var \Venne\Cms\Page
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Cms\Page", inversedBy="permissions")
	 * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $page;

	public function __construct()
	{
		$this->roles = new ArrayCollection;
	}

}
