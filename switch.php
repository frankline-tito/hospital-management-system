<?php require 'config.php'; need(['doctor','nurse']);
$s=$_GET['d']??'';foreach(depts() as $d)if($d['slug']==$s){$_SESSION['ctx']=$s;audit("switched to $s");header('Location: '.dept_link($s));exit;}
header('Location: index.php');
