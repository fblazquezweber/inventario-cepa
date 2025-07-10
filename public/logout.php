<?php
session_start();
session_unset();
session_destroy();
header("Location: index.html"); // O donde quieras redirigir
exit;