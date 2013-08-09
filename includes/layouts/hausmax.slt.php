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

$layout_css = 'div.storyinfo {
	clear:both;
}
div.element a {
  margin:1px;
  border:0px solid #000;
}
div.element img {
  border:1px solid #000;
}

div.el1 { background-color:#fa0; }
div.el2 { background-color:#af0; }
div.el3 { background-color:#a0f; }

div.block {
  width:99%;
  clear:both;
  float:left;
}
div.element {
  height:auto;
  float:left;
}
div.element img, div.element table {
	text-align:left;
	font-size:1em;
	margin:15px 0px;
}
div.element table {
	width:100%;

	}
div.element table th {
	width:50%;
	}
div.element table td {
	border-bottom:1px solid #000;

}

/* elm_count_* gives the number of elements in the block
*   That´s how the varying width of the elements is handled
*/
div.elm_count_1 { width:100%; }
div.elm_count_2 { width:49.5%; }
div.elm_count_3 { width:33%; }
div.elm_count_4 { width:24.5%; }

/* el1, el2, etc. defines the ordernumber of the element */
div.el1, div.el2, div.el3 {
}
';

$layout_string =
"<div id=\"block1\" class=\"block\">
	<h3 id=\"element1\" class=\"element el1 elm_count_1\">
		<span class=\"heading\">%%1%%</span>
	</h3>  <!-- /#element1 -->
</div>  <!-- /#block1 -->" .
"
<div id=\"block2\" class=\"block\">
	<div id=\"element2\" class=\"element el1 elm_count_1\">
		<span class=\"desc\">%%2%%</span>
		<span class=\"image\">%%3%%</span>
	</div>  <!-- /#element2 -->
</div>  <!-- /#block2 -->" .
"
<div id=\"block3\" class=\"block\">
	<div id=\"element3\" class=\"element el1 elm_count_2\">
		<table class=\"article_table\" summary=\"%%27%%\">
			<caption>%%4%%</caption>
			<tr class=\"table_line1\">
				<th>%%5%%</th>
				<td>%%6%%</td>
			</tr>
			<tr class=\"table_line2\">
				<th>%%7%%</th>
				<td>%%8%%</td>
			</tr>
			<tr class=\"table_line3\">
				<th>%%9%%</th>
				<td>%%10%%</td>
			</tr>
			<tr class=\"table_line4\">
				<th>%%11%%</th>
				<td>%%12%%</td>
			</tr>
			<tr class=\"table_line5\">
				<th>%%13%%</th>
				<td>%%14%%</td>
			</tr>
			<tr class=\"table_line6\">
				<th>%%15%%</th>
				<td>%%16%%</td>
			</tr>
			<tr class=\"table_line7\">
				<th>%%17%%</th>
				<td>%%18%%</td>
			</tr>
			<tr class=\"table_line8\">
				<th>%%19%%</th>
				<td>%%20%%</td>
			</tr>
</table>
	</div>  <!-- /#element3 -->
	<div id=\"element4\" class=\"element el2 elm_count_2\">
		<span class=\"desc\">%%21%%</span>
		<span class=\"image\">%%22%%</span>
	</div>  <!-- /#element4 -->
</div>  <!-- /#block3 -->" .
"
<div id=\"block4\" class=\"block\">
	<div id=\"element5\" class=\"element el1 elm_count_2\">
		<span class=\"desc\">%%23%%</span>
		<span class=\"image\">%%24%%</span>
	</div>  <!-- /#element5 -->
	<div id=\"element6\" class=\"element el2 elm_count_2\">
		<span class=\"desc\">%%25%%</span>
		<span class=\"image\">%%26%%</span>
	</div>  <!-- /#element6 -->
</div>  <!-- /#block4 -->";
?>