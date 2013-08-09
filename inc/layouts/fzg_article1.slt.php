<?php
/**
 * This is a layout file of bordeRleSS cms.
 *
 * created on 12.09.2005 23:27:36 by ahe
 */
/*
 * +----------------------------------------------------------------------------+
 * | B O R D E R L E S S   C M S                                                |
 * +----------------------------------------------------------------------------+
 * | (c) Copyright 2004-2005                                                    |
 * |      by goldstift (mail@goldstift.de) - www.goldstift.de                   |
 * +----------------------------------------------------------------------------+
 * BORDERLESS: prevents execution of php scripts separetly from the main file
 */
if(!defined('BORDERLESS')) exit;

$layout_css = '
div.element a {  margin:1px;  border:0px solid #333;}
div.element div { text-align:justify; }
div.element textarea,
div.element input,
div.element select {
    border:1px solid #ccc;
}
div.element textarea { width:100%; height:24em; }
#element1 input { float:none; width:98%; }

h3.element { margin:0px; margin-bottom:10px; }
h3.element input { font-family:inherit;}
div.desc { font-weight:bold; font-size:1.05em; }
div.elm_count_1 { width:100%; }
';

$layout_string =
'<div id="block1" class="block">
	<h3 id="element1" class="element el1 elm_count_1 floatbox">
		<span class="heading">%%1%%</span>
	</h3>  <!-- /#element1 -->
</div>  <!-- /#block1 -->' .
'<div id="block2" class="block">
	<div id="element2" class="element el1 elm_count_1 floatbox">
		<div class="text">%%2%%</div>
	</div>  <!-- /#element2 -->
</div>  <!-- /#block2 -->';