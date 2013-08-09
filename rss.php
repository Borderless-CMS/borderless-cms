<?php
/**
 * HTTP-HEADER anpassen, Content-Type:application/rss+xml und Accept-Charset:
 * utf-8
 *
 * Datumsformat anpassen:
 * http://www.w3.org/TR/NOTE-datetime 2002-10-02T10:00:00-05:00
 *
 * @author ahe <aheusingfeld@borderlesscms.de>
 * @since 0.11.???
 */

define('BORDERLESS',true);
define('BCMS_VERSION','(Version 0.13.183)');
define('LOG_ERROR_LEVEL',(E_ALL ^ E_NOTICE));
define('BASEPATH',dirname(__FILE__));
error_reporting(LOG_ERROR_LEVEL);

// include path setzen
require_once 'includes/set_inc_path.inc.php';

// Klassen initialisieren (incl. DB) und zusaetzliche Init-Dateien laden
require_once 'init_part1.inc.php';
require_once 'init_part2.inc.php';

$category = BcmsFactory::getInstanceOf('Parser')->getGetParameter('rsscat');
// TODO make rss-feed plugin dependent

// SQL-Query for getting content and author
$articleDAL = new Article_DAL();
$aArticles = $articleDAL->getRssArticles($category);
$catRssText = null;
// prepare strings
if($category!=null){
	$catRssText = ' - '.Factory::getObject('Dictionary')->getTrans('cont.categoryname')
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
      <title>'.$title.' ('.$author.', '.$pubdate.')></title>
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
