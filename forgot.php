<?php require 'config.php'; check();
$step=$_GET['step']??'1';$msg='';$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=trim($_POST['email']??'');
  if($step=='1'){
    $s=$pdo->prepare('SELECT id FROM users WHERE email=? AND active=1');$s->execute([$email]);$u=$s->fetch();
    if($u){$otp=sprintf('%06d',random_int(0,999999));
      $pdo->prepare('UPDATE password_resets SET used=1 WHERE user_id=?')->execute([$u['id']]);
      $pdo->prepare('INSERT INTO password_resets(user_id,otp_hash,expires_at) VALUES(?,?,DATE_ADD(NOW(),INTERVAL 10 MINUTE))')->execute([$u['id'],password_hash($otp,PASSWORD_DEFAULT)]);
      send_otp($email,$otp);}
    header('Location: forgot.php?step=2&sent=1&email='.urlencode($email));exit; // same reply whether or not the email exists
  }else{
    $s=$pdo->prepare('SELECT r.* FROM password_resets r JOIN users u ON u.id=r.user_id WHERE u.email=? AND r.used=0 AND r.expires_at>NOW() ORDER BY r.id DESC LIMIT 1');$s->execute([$email]);$r=$s->fetch();
    $pw=$_POST['password']??'';
    if(!$r||$r['attempts']>=5)$err='Code expired or too many attempts. Request a new code.';
    elseif(strlen($pw)<8)$err='Password must be at least 8 characters.';
    elseif(!password_verify(trim($_POST['otp']),$r['otp_hash'])){$pdo->prepare('UPDATE password_resets SET attempts=attempts+1 WHERE id=?')->execute([$r['id']]);$err='Wrong code.';}
    else{$pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($pw,PASSWORD_DEFAULT),$r['user_id']]);$pdo->prepare('UPDATE password_resets SET used=1 WHERE id=?')->execute([$r['id']]);
      $msg='Password changed. You can sign in now.';}
  }
}
head('Reset password');?>
<div class=card style="max-width:380px"><?php if($err)echo "<p class=err>".e($err)."</p>";if($msg)echo "<p class=ok>".e($msg)."</p>";?>
<?php if($step=='1'):?>
<form method=post><?=field()?><label>Your account email</label><input name=email type=email required><button>Send code</button></form>
<?php elseif(!$msg):?>
<?php if(isset($_GET['sent']))echo '<p>If that email has an account, a 6-digit code was sent. It expires in 10 minutes.</p>';?>
<form method=post action="forgot.php?step=2"><?=field()?><label>Email</label><input name=email type=email value="<?=e($_GET['email']??'')?>" required><label>6-digit code</label><input name=otp inputmode=numeric maxlength=6 required><label>New password</label><input name=password type=password minlength=8 required><button>Change password</button></form>
<?php endif;?><p><a href=login.php>Back to sign in</a></p></div><?php foot();
