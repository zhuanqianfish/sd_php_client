<?php
require_once("config.php");
$cid = $config['clientId'];
$api_domain = $config['SDServerUrl'];
require_once("lang/".$config['lang'].".php");
include("template/index.tlp.html");