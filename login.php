<?php require 'config.php'; require 'art.php'; check();
if(user()){header('Location: index.php');exit;}
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $s=$pdo->prepare('SELECT * FROM users WHERE email=? AND active=1');$s->execute([trim($_POST['email'])]);$u=$s->fetch();
  if($u&&$u['locked_until']&&strtotime($u['locked_until'])>time())$err='Too many failed attempts. This account is locked for 15 minutes.';
  elseif($u&&password_verify($_POST['password'],$u['password'])){
    $pdo->prepare('UPDATE users SET failed_logins=0,locked_until=NULL WHERE id=?')->execute([$u['id']]);
    session_regenerate_id(true);unset($_SESSION['otp_sent'],$_SESSION['resends']);
    $_SESSION['pre']=['id'=>$u['id'],'t'=>time()];header('Location: verify.php');exit;}
  else{usleep(400000);$err='Wrong email or password.';
    if($u){$f=$u['failed_logins']+1;$lock=$f>=5;$pdo->prepare('UPDATE users SET failed_logins=?,locked_until=IF(?,DATE_ADD(NOW(),INTERVAL 15 MINUTE),NULL) WHERE id=?')->execute([$lock?0:$f,$lock?1:0,$u['id']]);}}
}
head('Sign in',true);echo art_css();?>
<div class="split r"><section class=rt><?=art_bubbles()?><?=art_orbit()?></section><section class=lf><h2>Hospital System</h2><p class=sub>Sign in with your staff account</p>
<?php if($err)echo "<p class=err>".e($err)."</p>";?>
<form method=post><?=field()?><label>Email</label><input name=email type=email required autofocus><label>Password</label><input name=password type=password required><button>Continue</button></form>
<p><a href=forgot.php>Forgot password?</a></p><p class=sub style="font-size:13px">A one-time code will be sent to you after your password is checked.</p></section></div>
