<?php

// basic classes

class Article_DAL extends DataAbstractionLayer {

	public $col = array(

		// unique row ID
		'content_id' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'Content-ID'
		),
		'fk_cat' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'Menue-ID'
		),
		'heading' => array(
			'type'    => 'varchar',
			'size'    => 80,
			'qf_label' => 'Artikeltitel',
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 80 Zeichen lang sein!',
					80
				)
			)
		),
		'techname' => array(
			'type'    => 'varchar',
			'size'    => 30,
			'qf_label' => 'Artikeltitel (ohne Sonder- und Leerzeichen)',
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 30 Zeichen lang sein!',
					30
				),
				'regex' => array(
					'Techname must only consist of chars in a-z, A-Z, 0-9, \'-\' and \'_\'!',
					'/^[\w|-|_]+$/' // TODO use dictionary here
				)
			),
			'qf_client' => true
		),
		'contenttext' => array(
			'type'    => 'clob',
			'require' => true,
			'qf_label' => 'Inhalt',
			'qf_type' => 'hidden'
		),
		'fk_creator' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'Autor-ID'
		),
		'created' => array(
			'type'    => 'timestamp',
			'require' => true,
			'qf_label' => 'Erstellungsdatum'
		),
		'description' => array(
			'type'    => 'clob',
			'require' => true,
			'qf_label' => 'Kurzzusammenfassung des Artikelinhalts',
			'qf_type' => 'textarea',
			'qf_attrs'  => array(
				'rows' => 2,
				'cols' => 30
			 )
		),
		'publish_begin' => array(
			'type'    => 'timestamp',
			'require' => true,
			'qf_label' => 'Ver&ouml;ffentlichungsbeginn'
		),
		'publish_end' => array(
			'type'    => 'timestamp',
			'require' => true,
			'qf_label' => 'Ver&ouml;ffentlichungsende'
		),
		'version' => array(
			'type'    => 'float',
			'qf_label' => 'Versionsnummer'
		),
		'language' => array(
			'type'    => 'varchar',
			'size' => 5,
			'require' => true,
			'qf_label' => 'Sprache des Artikels',
			'qf_type'    => 'select'
		),
		'meta_keywords' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 255 Zeichen lang sein!',
					255
				)
			)
		),
		'redirect_url' => array(
			'type'    => 'varchar',
			'size'    => 255,
			'qf_rules' => array(
				'maxlength' => array(
					'Der Inhalt darf maximal 255 Zeichen lang sein!',
					255
				)
			)
		),
		'layout_id' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_type'    => 'select'
		),
		'status' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'Status'
		),
		'hits' => array(
			'type'    => 'integer',
			'require' => true,
			'qf_label' => 'Hits',
			'qf_type' => 'hidden'
		),
		'prev_img_id' => array(
			'type'    => 'integer',
			'qf_label' => 'Vorschau-Bild'
		),
		'prev_img_float' => array(
			'type'    => 'varchar',
			'size'	=>	10,
			'qf_label' => 'Textumfluss/ Ausrichtung des Vorschau Bildes',
			'qf_type' => 'select',
			'qf_vals' => array(
				'none' => 'Text unter dem Bild',
				'lft' => 'Bild links vom Text',
				'rgt' => 'Bild rechts vom Text',
				'no_float' => 'Kein Umfluss-Attribut einbauen')
		)
	);

	public $idx = array(
		'content_id' => array(
			'type' => 'unique',
			'cols' => 'content_id'
		),
		'fk_cat' => array(
			'type' => 'normal',
			'cols' => 'fk_cat'
		)
	);

	public $sql = array(

		// multiple rows for a list
		'list' => array(
			'select' => 'content_id, heading, fk_creator, publish_begin',
			'order'  => 'publish_begin DESC'
		),
		'nextAndPrevious' => array(
			'select' => 'content_id, heading, techname',
			'fetchmode' => DB_FETCHMODE_ASSOC
		),
		'listallcolumns' => array(
			'select' => '*'
		),
	);

	public $uneditableElements = array (
		'fk_cat',
		'fk_creator',
		'created');

	public $elementsToFreeze = array (
		'content_id'
	);

	private $configInstance = null;


/*
 * Declaration of methods
 */
	public function __construct() {
		$this->configInstance = BcmsConfig::getInstance();
		parent::__construct($GLOBALS['db'],	$this->configInstance->getTablename('articles'));
		$this->setListeCategorySQL();
		$this->setLatestArticlesSQL();
		$this->setLatestCommentsSQL();
		$this->setMostCommentedArticlesSQL();
		$this->setAllArticlesListSQL();
	    $this->col['status']['qf_vals'] = BcmsConfig::getInstance()->getTranslatedStatusList();
	}

	protected function getSearchableFieldsArray()
	{
		return array(
			'user.username' => 'LIKE',
			'user.nachname' => 'LIKE',
			'user.vorname' => 'LIKE',
			'menu.techname' => 'LIKE',
			'menu.categoryname' => 'LIKE',
			'class.name' => 'LIKE',
			'c.techname' => 'LIKE',
			'c.heading' => 'LIKE',
			'c.contenttext' => 'LIKE',
			'c.description' => 'LIKE',
			'c.meta_keywords' => 'LIKE',
			'c.redirect_url' => 'LIKE'
		);
	}

	private function setLatestArticlesSQL() {
		$this->sql['latestArticles'] = array(
			'select' =>
             'cont.content_id, cont.heading, cont.description,
             cont.created, author.username as auth_name,
             menu.techname as category, cont.hits, history.history_id ',
           'from' => $this->table.' as cont, '
				.$this->configInstance->getTablename('user').' as author, '
				.$this->configInstance->getTablename('history').' as history, '
				.$this->configInstance->getTablename('menu').' as menu ',
           'where' => 'cont.fk_creator=author.user_id AND
			  cont.content_id = history.content_id AND
			  cont.created = history.editdate AND
              cont.fk_cat=menu.cat_id AND
             (cont.status>='.$GLOBALS['ARTICLE_STATUS']['published'] // TODO use classifications for status!
             .') AND (menu.viewable4all=\'1\') AND
             (cont.publish_begin <= '.date('YmdHis',time()).') AND
             (cont.publish_end >= '.date('YmdHis',time()).') AND
             (menu.root_id > 0)',
           'limit' => BcmsConfig::getInstance()->no_of_latestentries,
			'fetchmode' => DB_FETCHMODE_ASSOC
		);
	}

	private function setLatestCommentsSQL() {
		$this->sql['latestComments'] = array(
			'select' =>
             'comm.fk_content, comm.heading, comm.contenttext,
             comm.created, author.username as auth_name,
             menu.techname, cont.hits ',
           'from' => $this->table.' as cont, '
				.$this->configInstance->getTablename('comments').' as comm, '
				.$this->configInstance->getTablename('user').' as author, '
				.$this->configInstance->getTablename('menu').' as menu ',
           'where' => 'comm.fk_author=author.user_id AND
				comm.fk_content=cont.content_id AND
				cont.fk_cat=menu.cat_id AND
				(comm.status>='.$GLOBALS['ARTICLE_STATUS']['published'] // TODO use classifications for status!
             .') AND (menu.viewable4all=\'1\') AND
             (cont.publish_begin <= '.date('YmdHis',time()).') AND
             (cont.publish_end >= '.date('YmdHis',time()).') AND
             (menu.root_id > 0)',
           'order' => 'comm.created desc',
           'limit' => BcmsConfig::getInstance()->no_of_latestentries,
			'fetchmode' => DB_FETCHMODE_ASSOC
		);
	}

	private function setMostCommentedArticlesSQL() {
		$this->sql['mostCommentedArticles'] = array(
			'select' =>
             'cont.content_id, cont.heading, cont.description,
             cont.publish_begin, author.username as auth_name,
             menu.techname, count(comm.comment_id) as comments ',
           'from' => $this->table.' as cont, '
				.$this->configInstance->getTablename('comments').' as comm, '
				.$this->configInstance->getTablename('user').' as author, '
				.$this->configInstance->getTablename('menu').' as menu ',
           'where' => 'cont.fk_creator=author.user_id AND
				comm.fk_content=cont.content_id AND
				cont.fk_cat=menu.cat_id AND
				(cont.status>='.$GLOBALS['ARTICLE_STATUS']['published'] // TODO use classifications for status!
             .') AND (menu.viewable4all=\'1\') AND
             (cont.publish_begin <= '.date('YmdHis',time()).') AND
             (cont.publish_end >= '.date('YmdHis',time()).') AND
             (menu.root_id > 0)',
			'group' => 'cont.content_id, cont.heading, cont.description,
             	cont.publish_begin, author.username, menu.techname',
           'order' => 'comments DESC',
           'limit' => BcmsConfig::getInstance()->no_of_latestentries,
			'fetchmode' => DB_FETCHMODE_ASSOC
		);
	}

	private function setListeCategorySQL() {
		$this->sql['listcat'] = array(
				'select' => 'c.content_id, c.heading, c.description, c.created' .
					', c. prev_img_id, c.prev_img_float, c.hits, c.status' .
					', c.version,  c.publish_begin, c.publish_end, user.username' .
					', user.email, menu.techname as categoryname',
				'from' => $this->table.' as c, ',
				'join' => $this->configInstance->getTablename('user').' as user, '
							.$this->configInstance->getTablename('menu').' as menu ',
				'where' => ' c.fk_creator = user.user_id ' .
						'AND c.fk_cat = menu.cat_id ',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);
	}

	private function setAllArticlesListSQL() {
		$this->sql['listall'] = array(
				'select' => 'c.content_id, menu.categoryname, c.heading, ' .
						'user.username, c.created, class.name as status' .
						', c.publish_end',
				'from' => $this->table.' as c, '
					.$this->configInstance->getTablename('user').' as user, '
					.$this->configInstance->getTablename('menu').' as menu, '
					.$this->configInstance->getTablename('classification').' as class, '
					.$this->configInstance->getTablename('systemschluessel').' as syskey ',
				'where' => ' c.fk_creator = user.user_id ' .
						'AND c.fk_cat = menu.cat_id '.
						'AND c.status = class.number '.
						'AND class.fk_syskey = syskey.id_schluessel '.
						'AND syskey.schluesseltyp = \'status\' ',
				'fetchmode' => DB_FETCHMODE_ASSOC
		);
	}

	/**
	 * handles filtering or values of special fields for insert or update
	 * @author ahe
	 */
	public function checkSpecialFields(&$p_aCols,$func,$p_iContentID=0) {

		if($func=='insert') $p_aCols['content_id'] = ($p_iContentID==0) ? $this->nextID() : $p_iContentID;
		$p_aCols['fk_creator'] = PluginManager::getPlgInstance('UserManager')->getLogic()->getUserID();
		$p_aCols['status'] = $GLOBALS['ARTICLE_STATUS']['published'];// TODO use classifications for status!
		$p_aCols['created'] = date('YmdHis');
	}

	public function getObject($id) {
		$this->sql['listallcolumns']['fetchmode'] = DB_FETCHMODE_ASSOC;
		$array = $this->select('listallcolumns','content_id = '.$id);
		return $array[0];
	}

	public function getArticleListByCategory($id,$status=null, $pubFilter=false,$order_by=null, $order_dir=null,$limit=null, $offset=null) {

		$where = '';
		if($id>0) $where .= 'fk_cat = '.$id;
		if($status>0) $where .= ' AND c.status >= '.$status;

		if($pubFilter) {
			$where .= ' AND (\''.date('Y-m-d H:i:s')
				.'\' BETWEEN c.publish_begin AND c.publish_end)';
		}

		if(!empty($order_by) && !empty($order_dir)) {
			$this->sql['listcat']['order'] = $order_by.' '.$order_dir;
		} else {
			$this->sql['listcat']['order'] = ' c.content_id ASC';
		}

		if(!empty($limit)) {
			$this->sql['listcat']['limit'] = $limit;
			$this->sql['listcat']['offset'] = $offset;
		}
		return $this->select('listcat',$where, null, $offset, $limit);
	}

	public function getAllArticlesList($filter=null, $order_by=null, $order_dir=null,$limit=null, $offset=null,$searchphrase=null) {

		if(!empty($order_by) && !empty($order_dir)) {
			$this->sql['listall']['order'] = $order_by.' '.$order_dir;
		} else {
			$this->sql['listall']['order'] = ' menu.lft ASC';
		}

		if(!empty($limit)) {
			$this->sql['listall']['limit'] = $limit;
			$this->sql['listall']['offset'] = $offset;
		}
		return parent::getList($offset,$limit,$filter, $order_by, $order_dir,$searchphrase,'cont.',null,'listall');
//		return $this->select('listall',$filter, null, $offset, $limit);
	}

	/**
	 * hier wird das sql latestArticles verwendet und nach publish_begin
	 * sortiert
	 */
	public function getLatestArticles() {
		$this->sql['latestArticles']['order'] = 'cont.created desc'; // for backwards compatibility
		$this->sql['latestArticles']['fetchmode'] = DB_FETCHMODE_ORDERED; // for backwards compatibility
		return $this->select('latestArticles',null,'cont.created desc',0
			,BcmsConfig::getInstance()->no_of_latestentries);
	}

	/**
	 * hier wird das sql "latestArticles" verwendet und nach cont.hits sortiert
	 */
	public function getMostViewedArticles() {
		$this->sql['latestArticles']['order'] = 'cont.hits desc'; // for backwards compatibility
		$this->sql['latestArticles']['fetchmode'] = DB_FETCHMODE_ORDERED; // for backwards compatibility
		return $this->select('latestArticles',null,null,0
			,BcmsConfig::getInstance()->no_of_latestentries);
	}

	public function getLatestComments() {
		$this->sql['latestComments']['fetchmode'] = DB_FETCHMODE_ORDERED; // for backwards compatibility
		return $this->select('latestComments',null,null,0
			,BcmsConfig::getInstance()->no_of_latestentries);
	}

	public function getMostCommentedArticles() {
		$this->sql['mostCommentedArticles']['fetchmode'] = DB_FETCHMODE_ORDERED; // for backwards compatibility
		return $this->select('mostCommentedArticles',null,null,0
			,BcmsConfig::getInstance()->no_of_latestentries);
	}

	/**
	 * Uses $categoryName if specified lft und rgt aus Menu-Table WHERE
	 * techname=$categoryName und dann als filter 'WHERE lft BETWEEN
	 * $menu_lft AND $menu_rgt'
	 *
	 * @param String category techname
	 * @return array the articles
	 * @author ahe
	 * @date 29.10.2006 01:07:00
	 * @package htdocs/plugins/content
	 */
	public function getRssArticles($categoryName=null) {
		$where=null;
		if($categoryName!=null){
			$where = PluginManager::getPlgInstance('CategoryManager')->getModel()->getWhereForTreelist($categoryName,null,0);
		}
		return $this->select('latestArticles',$where,'cont.created desc',0
			,BcmsConfig::getInstance()->rss_no_of_entries);

	}

 }
?>