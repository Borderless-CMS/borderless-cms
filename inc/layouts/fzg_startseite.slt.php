<?php
/**
 * This is a layout file of bordeRleSS cms.
 *
 * created on 23.08.2005 23:27:36 by ahe
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
div.element textarea { width:100%; height:7em; }
div#element3 textarea { height:25em; }
#element1 input { float:none; width:98%; }

h3.element { margin:0px; margin-bottom:15px; }
h3.element input { font-family:inherit;}
div.element div.desc,
div.element div.desc textarea
 { font-weight:bold; font-size:1.05em; }
div.element div.image {
	display:block;
	text-align:center;
	margin:1em 0px;
}
div.element div.image a {
	border:0px;
	text-decoration:none;
	margin:0px;
}
div.element div.image img {
	border:1px solid #777;
}
';

$layout_string =
'<div id="block1" class="block floatbox">
	<h3 id="element1" class="element el1 elm_count_1 floatbox">
		<span class="heading">%%1%%</span>
	</h3>  <!-- /#element1 -->
</div>  <!-- /#block1 -->' .
'
<div id="block2" class="block floatbox">
	<div id="element2" class="element el1 elm_count_1 floatbox">
		<div class="desc">%%2%%</div>
		<div class="image">%%3%%</div>
	</div>  <!-- /#element2 -->
</div>  <!-- /#block2 -->'
.'
<div id="block3" class="block floatbox">
	<div id="element3" class="element el1 elm_count_1 floatbox">
		<div class="text">%%4%%</div>
	</div>  <!-- /#element3 -->
</div>  <!-- /#block3 -->
';
?>