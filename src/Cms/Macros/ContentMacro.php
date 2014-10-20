<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Cms\Macros;

use Latte\CompileException;
use Latte\Compiler;
use Latte\IMacro;
use Latte\MacroNode;
use Venne\Packages\PathResolver;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ContentMacro extends \Nette\Object implements IMacro
{

	/** @var string */
	private $contentTypes = array('text', 'notation', 'name');

	public function initialize()
	{
	}

	public function finalize()
	{
	}

	/**
	 * New node is found. Returns FALSE to reject.
	 *
	 * @return bool
	 */
	public function nodeOpened(MacroNode $node)
	{
		$node->data->words = $words = $node->tokenizer->fetchWords();

		if (count($words) === 0) {
			throw new CompileException('Missing content part in {content}');
		}

		if (in_array($words['0'], $this->contentTypes, true)) {
			$node->attrCode = '<?php if (isset($_presenter->venneCmsEditation)) {?> data-venne-cms-content' . (isset($words[1])
					? '="' . $words[1] . '"'
					: ' ')
				. ' data-venne-cms-content-type="' . $words[0] . '" contenteditable="true"<?php } ?>';
			$node->openingCode = '<?php ob_start(); ?>';
		}
	}

	/**
	 * Node is closed.
	 *
	 * @return void
	 */
	public function nodeClosed(MacroNode $node)
	{
		$words = $node->data->words;

		if (count($words) === 0) {
			throw new CompileException('Missing content part in {content}');
		}

		if (in_array($words['0'], $this->contentTypes, true)) {
			if (isset($words[1])) {
				$res = '$__content_html = ob_get_clean();  $__content_dom = new DomDocument();

				if (!$page->text) {
					echo $__content_html;
				} else {
					$__content_dom->loadHtml(\'<meta http-equiv="content-type" content="text/html; charset=utf-8">\' . $page->text);
					$__content_node = $__content_dom->getElementById(\'venne-cms-content-' . $words[1] . '\');
					echo $__content_node && $__content_node->nodeValue ? $__content_node->nodeValue : $__content_html;
				}

				';
			} else {
				$res = '$__content_html = ob_get_clean(); echo $page->' . $words['0'] . ' ? $page->' . $words['0'] . ' : $__content_html';
			}
		}

		if (isset($res)) {
			$node->closingCode = '<?php ' . $res . ' ?>';

			return;
		}

		throw new CompileException(sprintf('Part %s is not supported by {content}', $words[0]));
	}

	/**
	 * @param \Latte\Compiler $compiler
	 */
	public static function install(Compiler $compiler)
	{
		$compiler->addMacro('content', new static);
	}

}
