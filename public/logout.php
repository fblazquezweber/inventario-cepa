<?php
session_start();
session_unset();
session_destroy();
header("Location: index.php"); // O donde quieras redirigir
exit;