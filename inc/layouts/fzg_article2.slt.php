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

$layout_css = 'div.block {  margin:5px 0px;}
div.element a {  margin:1px;  border:0px solid #333;}
div.element div { text-align:justify; }
div.element textarea,
div.element input,
div.element select {
    border:1px solid #ccc;
}
h3.element input { font-family:inherit;}
div.element textarea { width:100%; height:7em; }
div#element3 textarea { height:20em; }
div#element4 textarea { height:25em; }
#element1 input { float:none; width:98%; }

div.image img {
	border:1px solid #777;
	text-align:left;
	font-size:1em;
	margin:5px 5px 5px 0px;
}
div.image a {
	border:0px;
	text-decoration:none;
	margin:0px;
}
/* elm_count_* gives the number of elements in the block
*   That is how the varying width of the elements is handled */
div.elm_count_1 div { width:100%; }
div.elm_count_2 div { width:47%; margin-right:2%;}
div.elm_count_3 div { width:33%; }
div.elm_count_4 div { width:24.5%; }
/* give full width to divs inside of .elements */
div.block div div { width:auto; }

div.el1 { float:left; width:auto; }
';

$layout_string =
'<div id="block1" class="block floatbox elm_count_1">
	<h3 id="element1" class="element el1 floatbox">
		<span class="heading">%%1%%</span>
	</h3>  <!-- /#element1 -->
</div>  <!-- /#block1 -->' .
'
<div id="block2" class="block floatbox elm_count_2">
	<div id="element2" class="element el1 floatbox">
		<div class="desc">%%2%%</div>
		<div class="image">%%3%%</div>
	</div>  <!-- /#element2 -->
	<div id="element3" class="element el2 floatbox">
		<div class="text">%%4%%</div>
	</div>  <!-- /#element3 -->
</div>  <!-- /#block2 -->'
.'
<div id="block3" class="block floatbox elm_count_1">
	<div id="element4" class="element el1 floatbox">
		<div class="text">%%5%%</div>
	</div>  <!-- /#element4 -->
</div>  <!-- /#block3 -->
';
?>