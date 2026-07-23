<?php
session_start();
session_destroy();
header('Location: /evospace/index.php');
exit;