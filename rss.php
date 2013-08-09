<?php
/* Borderless CMS - the easiest and most flexible way to a valid website
 *   (c) 2004-2007 Alexander Heusingfeld <aheusingfeld@borderlesscms.de>
 *   Distributed under the terms and conditions of the GPL as stated in /license.txt
 * EXCLUSION:
 *   The files in the folder /pear/* are part of the PHP PEAR Project and are therefore
 *   distributed under the terms and conditions of the PHP License as stated in /pear/LICENSE
 */

/**
 * @file rss.php
 * Quick'n'dirty RSS generator for Borderless CMS
 *
 * HTTP-HEADER anpassen, Content-Type:application/rss+xml und Accept-Charset:
 * utf-8
 *
 * Datumsformat anpassen:
 * http://www.w3.org/TR/NOTE-datetime 2002-10-02T10:00:00-05:00
 *
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @since 0.11
 * @todo Build an rss module!
 * @ingroup plugins
 */
require_once('global_defines.inc.php');

try {
	// System initialisieren und allgemeine Funktionen laden
	require_once 'inc/init.inc.php';
	BcmsSystem::init();

} catch (MissingConfigFileException $ex) {
	header("location: install/install.php");
} catch (Exception $ex) {
	// \bug URGENT handle this exception
}

// pre initialize default system plugins
BcmsSystem::initSystemPlugins();
$category = BcmsSystem::getParser()->getGetParameter('rsscat');
// @todo make rss-feed plugin dependent

//@todo workaround until a RssManager is being used!
$GLOBALS['bcms_classes']['ContentManager'] = 'content/ContentManager.php';
// SQL-Query for getting content and author
$articleDAL = PluginManager::getPlgInstance('ContentManager')->getArticleDalObj();// @todo make rss-feed plugin dependent!!!
$aArticles = $articleDAL->getRssArticles($category);
$catRssText = null;
// prepare strings
if($category!=null){
	$catRssText = ' - '.BcmsSystem::getDictionaryManager()->getTrans('cont.categoryname')
			.': '.$category;
}
$refBcmsConfig = BcmsConfig::getInstance();
$rss_title = html_entity_decode(strip_tags($refBcmsConfig->rss_title.$catRssText)
	,ENT_QUOTES,$refBcmsConfig->metaCharset);
$rss_desc = html_entity_decode(strip_tags($refBcmsConfig->rss_description.$catRssText)
	,ENT_QUOTES,$refBcmsConfig->metaCharset);
$rss_copy = html_entity_decode(strip_tags($refBcmsConfig->copyright_linktext)
	,ENT_QUOTES,$refBcmsConfig->metaCharset);

// parse date for rss output
$refDate = new cDate();

// print out xml
header('Content-Type: application/rss+xml', true);

echo '<?xml version="1.0" encoding="'.$refBcmsConfig->metaCharset.'"?>
<rss version="2.0"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:admin="http://webns.net/mvcb/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:wfw="http://wellformedweb.org/CommentAPI/"
  >
  <channel>
	<title>'.$rss_title.'</title>
	<link>http://'.$refBcmsConfig->siteUrl.'</link>
	<description>'.$rss_desc.'</description>
    <dc:language>'.$refBcmsConfig->langKey.'-de</dc:language>
	<dc:rights>'.$rss_copy.'</dc:rights>
	<webMaster>'.$refBcmsConfig->webmasterEmail.'</webMaster>
	<generator>BordeRleSS cms</generator>
	<ttl>30</ttl>';
for($i=0;$i<count($aArticles);$i++) {
	$title = html_entity_decode(strip_tags(
		$aArticles[$i]['heading']),ENT_QUOTES,$refBcmsConfig->metaCharset);
	$author = html_entity_decode(strip_tags(
		$aArticles[$i]['auth_name']),ENT_QUOTES,$refBcmsConfig->metaCharset);
	$pubdate = strip_tags($aArticles[$i]['created']);
	$description = stripcslashes(str_replace('\r\n','',$aArticles[$i]['description']));
	$link = 'http://'.$refBcmsConfig->siteUrl.'/'.$aArticles[$i]['category'].'/show.'
		.$aArticles[$i]['content_id'].'.html';
	$permalink = 'http://'.$refBcmsConfig->siteUrl.'/'.$aArticles[$i]['category'].'/history.'
		.$aArticles[$i]['history_id'].'.html';
	echo '
    <item>
      <title>'.$title.' ('.$author.', '.$pubdate.')</title>
      <category>'.$aArticles[$i]['category'].'</category>
      <author>nospam@example.com  ('.$author.')</author>
      <pubDate>'.$refDate->getDateAsRSSDate($pubdate).'</pubDate>
      <description>'.$description.'</description>
      <content:encoded><![CDATA['.$description.']]></content:encoded>
      <link>'.$link.'</link>
      <comments>'.$link.'#a_comments</comments>
      <guid isPermaLink="true">'.$permalink.'</guid>
    </item>';
}
echo '
  </channel>
</rss>',"\n";
?>
