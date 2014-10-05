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
 * @ORM\Table(name="page_admin_permission")
 */
class AdminPermission extends \Venne\Cms\BasePermission
{

	/**
	 * @var \Venne\Security\Role[]
	 *
	 * @ORM\ManyToMany(targetEntity="\Venne\Security\Role", indexBy="name")
	 * @ORM\JoinTable(name="page_admin_permission_roles",
	 *      joinColumns={@ORM\JoinColumn(name="pagePermission_id", referencedColumnName="id", onDelete="CASCADE")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
	 *      )
	 **/
	protected $roles;

	/**
	 * @var \Venne\Cms\Page
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Cms\Page", inversedBy="adminPermissions")
	 * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $page;

}
