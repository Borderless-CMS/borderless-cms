<?php /* Smarty version 2.6.18, created on 2007-05-16 16:52:49
         compiled from std_header.tpl */ ?>
<?php echo '<?xml'; ?>
 version="1.0" encoding="<?php echo $this->_tpl_vars['configInstance']->metaCharset; ?>
"<?php echo '?>'; ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->_tpl_vars['configInstance']->langKey; ?>
" lang="<?php echo $this->_tpl_vars['configInstance']->langKey; ?>
">
  <head>
    <title><?php echo $this->_tpl_vars['page']['title']; ?>
</title>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=<?php echo $this->_tpl_vars['configInstance']->metaCharset; ?>
" />
    <meta http-equiv="Content-Script-Type" content="javascript" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta name="content-language" content="<?php echo $this->_tpl_vars['configInstance']->langKey; ?>
" />
    <meta name="description" content="<?php echo $this->_tpl_vars['page']['metaDescription']; ?>
" />
    <meta name="keywords" content="<?php echo $this->_tpl_vars['page']['metaKeywords']; ?>
" />
    <meta name="author" content="<?php echo $this->_tpl_vars['page']['metaAuthor']; ?>
" />
    <meta name="copyright" content="<?php echo $this->_tpl_vars['configInstance']->copyright_linktext; ?>
" />
    <meta name="distributor" content="<?php echo $this->_tpl_vars['configInstance']->metaDistributor; ?>
" />
    <meta name="revisit-after" content="<?php echo $this->_tpl_vars['configInstance']->metaRevisitAfter; ?>
 days" />
    <meta name="generator" content="Borderless CMS - http://www.borderlesscms.de/" />
    <meta name="robots" content="<?php echo $this->_tpl_vars['page']['robots']; ?>
" />

    <link rel="start" href="/" title="Startseite von '<?php echo $this->_tpl_vars['configInstance']->page_title; ?>
'" />
    <link rel="bookmark" title="Zum Seitenanfang" href="#top" />
    <link rel="bookmark" title="Zur Navigation" href="#mmenu" />
    <link rel="bookmark" title="Zu den optionalen Komponenten" href="#optcomp" />
    <link rel="content" href="#content" title="Zum Inhaltsbereich" />
    <link rel="imprint" href="<?php echo $this->_tpl_vars['configInstance']->completeSiteUrl; ?>
<?php echo $this->_tpl_vars['configInstance']->imprintCategoryName; ?>
/" title="Das Impressum dieser Seite" />
    <link rev="made" href="<?php echo $this->_tpl_vars['configInstance']->completeSiteUrl; ?>
<?php echo $this->_tpl_vars['configInstance']->contactCategoryName; ?>
/" title="Kontakt zum Betreiber dieser Seite" />
    
<?php if ($this->_tpl_vars['page']['rss']['global']['url'] != ""): ?>
    <link rel="alternate" type="application/rss+xml" title="<?php echo $this->_tpl_vars['page']['rss']['global']['title']; ?>
" href="<?php echo $this->_tpl_vars['page']['rss']['global']['url']; ?>
" />
<?php endif; ?>
<?php if ($this->_tpl_vars['page']['rss']['category']['url'] != ""): ?>
    <link rel="alternate" type="application/rss+xml" title="<?php echo $this->_tpl_vars['page']['rss']['category']['title']; ?>
" href="<?php echo $this->_tpl_vars['page']['rss']['category']['url']; ?>
" />
<?php endif; ?>
<?php if ($this->_tpl_vars['page']['cssUrl'] != ""): ?>
    <link rel="stylesheet" title="default stylesheet" type="text/css" media="all" href="<?php echo $this->_tpl_vars['page']['cssUrl']; ?>
" />
<?php endif; ?>
	<style type="text/css">/*<![CDATA[*/ 
		<?php echo $this->_tpl_vars['page']['cssInline']; ?>

	/*]]>*/</style>
     <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
  </head>
  <body>