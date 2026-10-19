<?php require 'config.php'; check();
$lock=__DIR__.'/setup.lock';
if(file_exists($lock))die('Setup already done. To run it again, delete the file setup.lock. When finished, delete setup.php.');
$msg=$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=trim($_POST['email']);$pw=$_POST['password'];
  if(!filter_var($email,FILTER_VALIDATE_EMAIL))$err='Enter a valid email.';
  elseif(strlen($pw)<8||$pw!==$_POST['confirm'])$err='Password must be 8+ characters and match the confirmation.';
  else{$h=password_hash($pw,PASSWORD_DEFAULT);$s=$pdo->prepare('SELECT id FROM users WHERE email=?');$s->execute([$email]);
    if($s->fetch())$pdo->prepare("UPDATE users SET password=?,role='admin',active=1 WHERE email=?")->execute([$h,$email]);
    else $pdo->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,'admin')")->execute([trim($_POST['name']),$email,$h]);
    file_put_contents($lock,date('c'));$msg='Admin ready. Sign in with that email and password, then delete setup.php.';}
}
head('Admin setup');if($err)echo "<p class=err>".e($err)."</p>";if($msg)echo "<p class=ok>".e($msg)." <a href=login.php>Sign in</a></p>";
if(!$msg):?><div class=card style="max-width:380px"><p>Creates the admin account, or resets the password if the email already exists.</p>
<form method=post><?=field()?><label>Name</label><input name=name value="Administrator" required><label>Admin email</label><input name=email type=email value="admin@hospital.local" required>
<label>New password</label><input name=password type=password minlength=8 required><label>Confirm password</label><input name=confirm type=password required><button>Save admin</button></form></div><?php endif;foot();
