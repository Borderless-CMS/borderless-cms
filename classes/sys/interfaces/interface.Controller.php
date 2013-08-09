<?php
/**
 * Created on 14.10.2006
 */
interface Controller extends Singleton {
	public function getModel();
	public function getView();
	public function getLogic();
	
}
?>
