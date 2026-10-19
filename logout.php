<?php require 'config.php'; audit('logout'); session_destroy(); header('Location: login.php');
