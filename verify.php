<?php require 'config.php'; require 'art.php'; check();
$p=$_SESSION['pre']??null;if(!$p||time()-$p['t']>600){unset($_SESSION['pre']);header('Location: login.php');exit;}
$u=$pdo->prepare('SELECT * FROM users WHERE id=? AND active=1');$u->execute([$p['id']]);$u=$u->fetch();if(!$u){unset($_SESSION['pre']);header('Location: login.php');exit;}
$err=$note='';$sent=$_SESSION['otp_sent']??'';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['channel'])){$ch=$_POST['channel']=='sms'?'sms':'email';
    if(($_SESSION['resends']??0)>=3){unset($_SESSION['pre']);header('Location: login.php');exit;}
    if($ch=='sms'&&!$u['phone'])$err='This account has no phone number. Choose email.';
    else{$otp=sprintf('%06d',random_int(0,999999));$pdo->prepare('UPDATE login_otps SET used=1 WHERE user_id=?')->execute([$u['id']]);
      $pdo->prepare('INSERT INTO login_otps(user_id,otp_hash,channel,expires_at) VALUES(?,?,?,DATE_ADD(NOW(),INTERVAL 5 MINUTE))')->execute([$u['id'],password_hash($otp,PASSWORD_DEFAULT),$ch]);
      $ok=($ch=='sms')?send_sms($u['phone'],"Your hospital login code is $otp. It expires in 5 minutes. Do not share it."):send_otp($u['email'],$otp);if(!$ok&&!DEV_LOG)$err='We could not send the code. Try the other option or tell the administrator.';
      if(!$err){$_SESSION['otp_sent']=$sent=$ch;$_SESSION['resends']=($_SESSION['resends']??0)+1;if(!$ok)$note='Test mode: sending failed ('.$GLOBALS['send_err'].'), so the code was written to otp.log.';}}
  }elseif(isset($_POST['code'])){
    $r=$pdo->prepare('SELECT * FROM login_otps WHERE user_id=? AND used=0 AND expires_at>NOW() ORDER BY id DESC LIMIT 1');$r->execute([$u['id']]);$r=$r->fetch();
    if(!$r||$r['attempts']>=5){unset($_SESSION['pre']);header('Location: login.php');exit;}
    elseif(!password_verify(trim($_POST['code']),$r['otp_hash'])){$pdo->prepare('UPDATE login_otps SET attempts=attempts+1 WHERE id=?')->execute([$r['id']]);$err='Wrong code.';}
    else{$pdo->prepare('UPDATE login_otps SET used=1 WHERE id=?')->execute([$r['id']]);session_regenerate_id(true);
      $_SESSION['u']=['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role'],'facility'=>$u['facility_id']];$_SESSION['last']=time();unset($_SESSION['pre'],$_SESSION['otp_sent'],$_SESSION['resends']);audit('login');header('Location: index.php');exit;}
  }
}
head('Verify',true);echo art_css();?>
<div class="split r"><section class=rt><?=art_bubbles()?><?=art_orbit()?></section><section class=lf><h2>Verify it is you</h2>
<?php if($err)echo "<p class=err>".e($err)."</p>";if($note)echo "<p class=ok>".e($note)."</p>";if(!$sent):?><p class=sub>Where should we send your one-time code?</p>
<form method=post><?=field()?><label class=opt><input type=radio name=channel value=email checked> Email <small><?=e(mask_to($u['email']))?></small></label>
<label class=opt><input type=radio name=channel value=sms <?=$u['phone']?'':'disabled'?>> Phone message (SMS) <small><?=$u['phone']?e(mask_to($u['phone'])):'no number saved'?></small></label><button>Send code</button></form>
<?php else:?><p class=sub>Code sent by <?=$sent=='sms'?'SMS to '.e(mask_to($u['phone'])):'email to '.e(mask_to($u['email']))?>. It expires in 5 minutes.</p>
<form method=post><?=field()?><label>6-digit code</label><input name=code inputmode=numeric maxlength=6 autocomplete=one-time-code required autofocus><button>Verify and sign in</button></form>
<form method=post style="margin-top:8px"><?=field()?><input type=hidden name=channel value="<?=e($sent)?>"><button style="background:#5b706e">Send a new code</button></form><?php endif;?>
<p><a href=login.php>Cancel</a></p></section></div>
