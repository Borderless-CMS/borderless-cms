<?xml version="1.0" encoding="{$configInstance->metaCharset}"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$configInstance->langKey}" lang="{$configInstance->langKey}">
  <head>
    <title>{$page.title}</title>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset={$configInstance->metaCharset}" />
    <meta http-equiv="Content-Script-Type" content="javascript" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta name="content-language" content="{$configInstance->langKey}" />
    <meta name="description" content="{$page.metaDescription}" />
    <meta name="keywords" content="{$page.metaKeywords}" />
    <meta name="author" content="{$page.metaAuthor}" />
    <meta name="copyright" content="{$configInstance->copyright_linktext}" />
    <meta name="distributor" content="{$configInstance->metaDistributor}" />
    <meta name="revisit-after" content="{$configInstance->metaRevisitAfter} days" />
    <meta name="generator" content="Borderless CMS - http://www.borderlesscms.de/" />
    <meta name="robots" content="{$page.robots}" />

    <link rel="start" href="/" title="Startseite von '{$configInstance->page_title}'" />
    <link rel="bookmark" title="Zum Seitenanfang" href="#top" />
    <link rel="bookmark" title="Zur Navigation" href="#mmenu" />
    <link rel="bookmark" title="Zu den optionalen Komponenten" href="#optcomp" />
    <link rel="content" href="#content" title="Zum Inhaltsbereich" />
    <link rel="imprint" href="{$configInstance->completeSiteUrl}{$configInstance->imprintCategoryName}/" title="Das Impressum dieser Seite" />
    <link rev="made" href="{$configInstance->completeSiteUrl}{$configInstance->contactCategoryName}/" title="Kontakt zum Betreiber dieser Seite" />
    
{if $page.rss.global.url ne ""}
    <link rel="alternate" type="application/rss+xml" title="{$page.rss.global.title}" href="{$page.rss.global.url}" />
{/if}
{if $page.rss.category.url ne ""}
    <link rel="alternate" type="application/rss+xml" title="{$page.rss.category.title}" href="{$page.rss.category.url}" />
{/if}
{if $page.cssUrl ne ""}
    <link rel="stylesheet" title="default stylesheet" type="text/css" media="all" href="{$page.cssUrl}" />
{/if}
	<style type="text/css">/*<![CDATA[*/ 
		{$page.cssInline}
	/*]]>*/</style>
     <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
  </head>
  <body>