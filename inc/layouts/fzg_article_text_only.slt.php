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

$layout_css = 'textarea { width:100%; height:100px; };
div.element div { text-align:justify; }
';

$layout_string =
'<div id="block1" class="block">
	<div id="element1" class="element el1 elm_count_1">
		<div class="text">%%1%%</div>
	</div>  <!-- /#element1 -->
</div>  <!-- /#block1 -->';