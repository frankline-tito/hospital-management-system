<?php require 'config.php'; need(['admin']); check();$res='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['email']))$ok=send_otp(trim($_POST['email']),'123456');else $ok=send_sms($_POST['phone'],'Test message from the hospital system.');
  $res=$ok?'<p class=ok>Sent. Check the inbox or phone.</p>':'<p class=err>Failed: '.e($GLOBALS['send_err']).'</p>';}
head('Test email and SMS');echo $res;?>
<div class=g><div class=card><h3>Test email</h3><form method=post><?=field()?><input name=email type=email placeholder="you@example.com" required><button>Send test email</button></form></div>
<div class=card><h3>Test SMS</h3><form method=post><?=field()?><input name=phone placeholder="0712345678" required><button>Send test SMS</button></form></div></div><?php foot();
