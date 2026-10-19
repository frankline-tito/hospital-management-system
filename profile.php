<?php require 'config.php'; need(); check();$msg=$err='';
$m=$pdo->prepare('SELECT * FROM users WHERE id=?');$m->execute([user()['id']]);$m=$m->fetch();
if($_SERVER['REQUEST_METHOD']==='POST'){$em=trim($_POST['email']);$ph=trim($_POST['phone']);
  if(!password_verify($_POST['pw'],$m['password']))$err='Your current password is wrong.';
  elseif(!filter_var($em,FILTER_VALIDATE_EMAIL))$err='Enter a valid email address.';
  elseif($ph!==''&&!ke_phone($ph))$err='Enter a valid Kenyan number, for example 0712345678.';
  else try{$pdo->prepare('UPDATE users SET email=?,phone=? WHERE id=?')->execute([$em,$ph===''?null:ke_phone($ph),$m['id']]);audit('profile contact details changed');$m['email']=$em;$m['phone']=$ph===''?null:ke_phone($ph);$msg='Saved. Login codes will go to these details.';}
  catch(PDOException $x){$err='That email is already used by another account.';}}
head('My profile');if($err)echo '<p class=err>'.e($err).'</p>';if($msg)echo '<p class=ok>'.e($msg).'</p>';?>
<div class=card style="max-width:460px"><p>Login codes are sent to the email and phone below. Use your real ones.</p><form method=post><?=field()?>
<label>Email</label><input name=email type=email value="<?=e($m['email'])?>" required><label>Phone (for SMS codes)</label><input name=phone value="<?=e($m['phone'])?>" placeholder="0712345678">
<label>Current password (to confirm)</label><input name=pw type=password required><button>Save</button></form></div><?php foot();
