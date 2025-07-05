<?php
require 'config.php';
session_unset();
session_destroy();
setcookie('manter_conectado', '', time()-3600, '/');
header('Location: login.php');
exit;
?>
