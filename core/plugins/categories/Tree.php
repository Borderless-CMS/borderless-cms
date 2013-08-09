<?php if(!defined('BORDERLESS')) { header('Location: / ',true,403); exit(); }
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * Ist fuer die Erstellung des Baummenues auf der linken Bildschirmseite
 * zustaendig.
 *
 * @author ahe <aheusingfeld@borderless-cms.de>
 * @since 0.5
 * @class Tree
 * @ingroup categories
 * @package categories
 */
class Tree extends GuiUtility {
	public $gfxMainPath = null;
	public $gfxTreePath = null;
	private$treeWidth = "100%";
	private $gfxWidth = 19;
	private $gfxHeight = 18;
	private $gfxFolderSrc;
	private $gfxJustDownSrc;
	private $gfxDownRightSrc;
	private $gfxJustRightSrc;
	private $NoOfTreeElements2Draw = array ();
	private $MenuObj;

	function __construct(& $menuObj) {
		$this->MenuObj = $menuObj;
		$this->gfxMainPath = BcmsConfig::getInstance()->completeSiteUrl.'/inc/gfx/';
		$this->gfxTreePath = $this->gfxMainPath.'treeicons/';
		$this->gfxFolderSrc = $this->gfxTreePath . 'ordner.gif';
		$this->gfxJustDownSrc = $this->gfxTreePath . 'nix_unten.gif';
		$this->gfxDownRightSrc = $this->gfxTreePath . 'plus_rechts_unten.gif';
		$this->gfxJustRightSrc = $this->gfxTreePath . 'plus_rechts.gif';
	}

	/**
	* Image-Tag fuer ein Edit-Symbol erstellen
	*
	* @param int $aMenuID ID des aktuellen Menues
	* @access private
	* @return string anchor- und img-Tag
	*/
	function createEditImg($aTechname) {
		// @todo use dictionary for i18n
		return $this->createMenuImgAnchor($aTechname, 'edit'
			, 'category_edit.png', 16, 16, 'Rubrik bearbeiten');
	}

	/**
	* Image-Tag fuer ein Delete-Symbol erstellen
	*
	* @param int $aMenuID ID des aktuellen Menues
	* @access private
	* @return string anchor- und img-Tag
	*/
	function createDeleteImg($aTechname) {
		// @todo use dictionary for i18n
		return $this->createMenuImgAnchor($aTechname, 'del'
			, 'bin_closed.png', 16, 16, 'Rubrik loeschen');
	}

	/**
	* Image-Tag fuer das "Create-Menu-Above"-Symbol erstellen
	*
	* @param int $aMenuID ID des aktuellen Menues
	* @access private
	* @return string anchor- und img-Tag
	*/
	function createInsertMenuAboveImg($aTechname) {
		// @todo use dictionary for i18n
		return $this->createMenuImgAnchor($aTechname, 'add_top'
			, 'create_top.png', 16, 16, 'Rubrik ueber dieser Rubrik erstellen');
	}

	/**
	* Image-Tag fuer das "Create-Menu-Beyond"-Symbol erstellen
	*
	* @param int $aMenuID ID des aktuellen Menues
	* @access private
	* @return string anchor- und img-Tag
	*/
	function createInsertMenuBeyondImg($aTechname) {
		// @todo use dictionary for i18n
		return $this->createMenuImgAnchor($aTechname, 'add_bottom'
			, 'create_bottom.png', 16, 16, 'Rubrik unter dieser Rubrik erstellen');
	}

	/**
	* Image-Tag fuer das "Create-Menu-Above"-Symbol erstellen
	*
	* @param int $aMenuID ID des aktuellen Menues
	* @access private
	* @return string anchor- und img-Tag
	*/
	private function createEditMenuLayoutImg($aTechname) {
		// @todo use dictionary for i18n
		return $this->createMenuImgAnchor($aTechname, 'edit_layout'
			, 'article_layout.png', 16, 16, 'Rubrik Layoutstrukturen zuordnen');
	}

	private function createMenuImgAnchor($aTechname, $func, $src, $width, $height, $alt) {
		$image = $this->MenuObj->createImageTag(
			array (
				'src' => $this->gfxMainPath.'silk/'.$src, // Filename
				'width' => $width,
				'height' => $height,
				'style' => 'float:left; margin:0px;', // float
				'alt' => $alt // ALT text/ description
			));
		$anchor = $this->createAnchorTag($func.'/'.$aTechname, $image,0,null,0,$alt);
		return $anchor;
	}

	// --- Ende der Hilfsfunktionen ---

	function DrawTreeContent($parent_id = 0) {
		// INIT
		$menuTree = $this->MenuObj->getMenuFullTree();
		$allRows = '';
		for ($i = 0; $i < count($menuTree); $i++) {
			$folderImg = $this->MenuObj->createImageTag(
				array (
					'src' => $this->gfxMainPath.'silk/folder.png', // Filename
					'width' => 16,
					'height' => 16,
					'style' => 'margin:0px 3px 0px 0px;', // float
					'alt' => '' // ALT text/ description
				));
			$theText = '<span class="tree_categoryname" style="margin-left:'
				.$menuTree[$i]['level'].'em; border:0px;">'
				.$folderImg.$menuTree[$i]['categoryname'].'</span>';

			// Tabellenzeile schreiben
			$treeContent = $this->createTdTag($theText, null, 20);
			$treeContent .= $this->createTdTag(
				$this->createEditImg($menuTree[$i]['techname']), null, 20);
			$treeContent .= $this->createTdTag(
				$this->createDeleteImg($menuTree[$i]['techname']), null, 20);
			$treeContent .= $this->createTdTag(
				$this->createInsertMenuAboveImg($menuTree[$i]['techname'])
				, null, 20);
			$treeContent .= $this->createTdTag(
				$this->createInsertMenuBeyondImg($menuTree[$i]['techname'])
				, null, 20);
			$treeContent .= $this->createTdTag(
				$this->createEditMenuLayoutImg($menuTree[$i]['techname'])
				, null, 20);

			$allRows .= $this->createTrTag($treeContent
				, 'class="line'.(($i%2) + 1).'"', 18);
		}
		return $allRows;
	}

	function createTdTag($aTdContent, $optAttribs=null, $numOfSpaces=0) {
		$retval = $this->createSpaces($numOfSpaces);
		return $retval.$this->fillTemplate('td_tpl',array($optAttribs,$aTdContent));
	}

	function createTrTag($aTrContent, $optAttribs=null, $numOfSpaces=0) {
		$spaces = $this->createSpaces($numOfSpaces);
		return $this->fillTemplate('tr_tpl',array($optAttribs,$aTrContent,$spaces));
	}

	function drawTree() {
		echo '
		    <table id="categorytree_table">
		      <tr>
		        <th id="menu_categories"><span>Navigation</span></th>
		        <th id="menu_edit"><span>Edit</span></th>
		        <th id="menu_delete"><span>Del</span></th>
		        <th id="menu_create_above"><span>ca</span></th>
		        <th id="menu_create_beyond"><span>cb</span></th>
		        <th id="menu_edit_layout_zo"><span>lo</span></th>
		      </tr>' . "\n";
		echo $this->DrawTreeContent();
		echo "\n" . '    </table>';
	}

}
?>