<?php
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax']);
session_start();
header('X-Frame-Options: DENY');header('X-Content-Type-Options: nosniff');header('Referrer-Policy: same-origin');
if(isset($_SESSION['u'])){if(time()-($_SESSION['last']??time())>1800){session_unset();session_destroy();session_start();}else $_SESSION['last']=time();}
$pdo=new PDO('mysql:host=localhost;dbname=hms;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function field(){$_SESSION['t']??=bin2hex(random_bytes(16));return '<input type=hidden name=t value="'.$_SESSION['t'].'">';}
function check(){if($_SERVER['REQUEST_METHOD']==='POST'&&!hash_equals($_SESSION['t']??'',$_POST['t']??''))die('Invalid request. Go back and retry.');}
function user(){return $_SESSION['u']??null;}
function need($roles=[]){if(!user()){header('Location: login.php');exit;}if($roles&&user()['role']!=='admin'&&!in_array(user()['role'],$roles))die('You do not have access to this page.');}
function audit($a){global $pdo;$pdo->prepare('INSERT INTO audit_log(user_id,action) VALUES(?,?)')->execute([user()['id']??null,$a]);}
function fscope(){$u=user();return($u&&$u['role']!='admin'&&!empty($u['facility']))?(int)$u['facility']:null;}
function head($t,$bare=false){global $pdo;$u=user();if($u){$GLOBALS['unread']=(int)$pdo->query('SELECT COUNT(*) FROM messages WHERE read_at IS NULL AND to_id='.(int)$u['id'])->fetchColumn();}echo '<!doctype html><meta charset=utf-8><meta name=viewport content="width=device-width,initial-scale=1"><title>'.e($t).'</title><style>
:root{--ink:#17302f;--brand:#0f5c5c;--bg:#f4f7f6;--line:#d5e0de;--warn:#a23b2a}
*{box-sizing:border-box}body{margin:0;font:15px/1.5 system-ui,sans-serif;background:var(--bg);color:var(--ink)}
nav{background:var(--brand);padding:10px 20px;display:flex;gap:18px;flex-wrap:wrap;align-items:center}nav a{color:#fff;text-decoration:none}nav b{color:#fff;margin-right:auto}
main{max-width:980px;margin:24px auto;padding:0 16px}h1{margin:0 0 16px}
.card{background:#fff;border:1px solid var(--line);border-radius:6px;padding:16px;margin-bottom:16px}
label{display:block;margin:8px 0 2px;font-weight:600}input,select,textarea{width:100%;padding:8px;border:1px solid var(--line);border-radius:4px;font:inherit}
button{background:var(--brand);color:#fff;border:0;padding:9px 16px;border-radius:4px;font:inherit;cursor:pointer;margin-top:12px}
table{width:100%;border-collapse:collapse}td,th{padding:8px;border-bottom:1px solid var(--line);text-align:left}
.g{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}.err{color:var(--warn);font-weight:600}.ok{color:var(--brand);font-weight:600}
</style>';
 if($u)echo shell_open();
 echo $bare?'':'<main><h1>'.e($t).'</h1>';}
function foot(){echo '</main>'.(user()?'</div></div>':'');}
function advance($vid,$after){global $pdo;$vid=(int)$vid;
  $l=(int)$pdo->query("SELECT COUNT(*) FROM lab_orders WHERE visit_id=$vid AND status='pending'")->fetchColumn();
  $p=(int)$pdo->query("SELECT COUNT(*) FROM prescriptions WHERE visit_id=$vid AND status='pending'")->fetchColumn();
  $c=function($t,$s)use($pdo,$vid){try{return (int)$pdo->query("SELECT COUNT(*) FROM $t WHERE visit_id=$vid AND status='$s'")->fetchColumn();}catch(Exception $x){return 0;}};
  $pdo->prepare('UPDATE visits SET status=? WHERE id=?')->execute([$l?'lab':($c('imaging_orders','queued')?'imaging':($c('procedure_orders','queued')?'procedure':($p?'pharmacy':$after))),$vid]);}
function order_extra($id){global $pdo;foreach($_POST['img']??[] as $x)$pdo->prepare('INSERT INTO imaging_orders(visit_id,exam_id,clinical_info,ordered_by) VALUES(?,?,?,?)')->execute([(int)$id,(int)$x,trim($_POST['imginfo']??''),user()['id']]);
  foreach($_POST['proc']??[] as $x)$pdo->prepare('INSERT INTO procedure_orders(visit_id,proc_id,indication,ordered_by) VALUES(?,?,?,?)')->execute([(int)$id,(int)$x,trim($_POST['procinfo']??''),user()['id']]);}
function extra_form(){global $pdo;$o='<label>Imaging (X-ray, ultrasound, CT)</label><div style="display:flex;flex-wrap:wrap;gap:12px">';foreach($pdo->query('SELECT id,name FROM imaging_exams ORDER BY modality,name') as $t)$o.='<label style="font-weight:400"><input type=checkbox name="img[]" value='.$t['id'].' style="width:auto"> '.e($t['name']).'</label>';
  $o.='</div><input name=imginfo placeholder="Clinical information for the radiographer"><label>Minor procedures</label><div style="display:flex;flex-wrap:wrap;gap:12px">';foreach($pdo->query('SELECT id,name FROM procedures_catalog ORDER BY name') as $t)$o.='<label style="font-weight:400"><input type=checkbox name="proc[]" value='.$t['id'].' style="width:auto"> '.e($t['name']).'</label>';
  return $o.'</div><input name=procinfo placeholder="Reason / indication for the procedure">';}
function mask_to($s){return strpos($s,'@')!==false?substr($s,0,1).'***'.substr($s,strpos($s,'@')):'*******'.substr($s,-3);}
const CONSULT_FEE=500;        // consultation fee in KES, change here
const FEE_BEFORE_TRIAGE=false; // true = patient must pay before triage too
function start_fee($vid,$pid){global $pdo;if(CONSULT_FEE<=0)return;
  $pdo->prepare('INSERT INTO invoices(patient_id,visit_id,total,created_by) VALUES(?,?,?,?)')->execute([$pid,$vid,CONSULT_FEE,user()['id']??null]);$iid=$pdo->lastInsertId();
  $pdo->prepare("INSERT INTO invoice_items(invoice_id,description,amount,source,ref_id) VALUES(?,?,?,'fee',?)")->execute([$iid,'Consultation fee',CONSULT_FEE,$vid]);}
function fee_paid($vid){global $pdo;$s=$pdo->prepare("SELECT i.paid,(SELECT amount FROM invoice_items WHERE invoice_id=i.id AND source='fee' LIMIT 1) f FROM invoices i WHERE i.visit_id=? ORDER BY i.id LIMIT 1");$s->execute([(int)$vid]);$r=$s->fetch();
  return !$r||$r['f']===null||$r['paid']>=$r['f']-0.001;}
function fee_block($vid){if(fee_paid($vid))return;head('Consultation fee not paid');echo '<div class=card><p class=err>This patient has not paid the consultation fee.</p><p>Send the patient to billing to pay (cash, M-Pesa or bank), then come back.</p></div>';foot();exit;}
function clean_html($h){$h=strip_tags($h,'<p><br><b><i><u><s><strong><em><ul><ol><li><h3><div>');return preg_replace('/<(\w+)[^>]*>/','<$1>',$h);}
require_once __DIR__.'/launcher.php';
require_once __DIR__.'/shell.php';
require_once __DIR__.'/print.php';
require_once __DIR__.(file_exists(__DIR__.'/secrets.php')?'/secrets.php':'/secrets.sample.php');
function dev_log($m){if(DEV_LOG)file_put_contents(__DIR__.'/otp.log',date('c')." $m\n",FILE_APPEND);}
function smtp_send($to,$subject,$body){
 $f=@stream_socket_client(SMTP_HOST.':'.SMTP_PORT,$en,$es,15);if(!$f){$GLOBALS['send_err']='Cannot reach the mail server: '.$es;return false;}
 stream_set_timeout($f,15);$rd=function()use($f){$r='';while(($l=fgets($f,515))!==false){$r.=$l;if(substr($l,3,1)==' ')break;}return $r;};
 $c=function($x,$ok)use($f,$rd){if($x!==null)fwrite($f,$x."\r\n");return strpos($rd(),(string)$ok)===0;};
 $good=$c(null,220)&&$c('EHLO localhost',250);
 if($good&&SMTP_PORT==587)$good=$c('STARTTLS',220)&&stream_socket_enable_crypto($f,true,STREAM_CRYPTO_METHOD_TLS_CLIENT)&&$c('EHLO localhost',250);
 $good=$good&&$c('AUTH LOGIN',334)&&$c(base64_encode(SMTP_USER),334)&&$c(base64_encode(SMTP_PASS),235)&&$c('MAIL FROM:<'.SMTP_FROM.'>',250)&&$c("RCPT TO:<$to>",250)&&$c('DATA',354);
 if($good){$m='From: '.SMTP_NAME.' <'.SMTP_FROM.">\r\nTo: $to\r\nSubject: $subject\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=utf-8\r\nDate: ".date('r')."\r\n\r\n".str_replace("\n","\r\n",$body)."\r\n.";$good=$c($m,250);}
 @fwrite($f,"QUIT\r\n");fclose($f);if(!$good)$GLOBALS['send_err']='The mail server refused the message. Check the username, app password and sender address.';return $good;}
function send_otp($email,$otp){$GLOBALS['send_err']='';
 if(substr($email,-11)==='@test.local'){file_put_contents(__DIR__.'/otp.log',date('c')." [TEST ACCOUNT] $email $otp\n",FILE_APPEND);return true;}
 if(!filter_var($email,FILTER_VALIDATE_EMAIL)){$GLOBALS['send_err']='That email address is not valid.';return false;}
 if(SMTP_USER==='your@gmail.com'||strpos(SMTP_PASS,'your-')===0){$GLOBALS['send_err']='Email is not set up yet (see secrets.php).';dev_log("[EMAIL NOT CONFIGURED] $email $otp");return false;}
 $ok=smtp_send($email,'Your hospital system code',"Your verification code is $otp.\nIt expires in a few minutes. Never share it with anyone.\nIf you did not ask for it, tell your administrator.");
 if(!$ok)dev_log('[EMAIL FAILED: '.$GLOBALS['send_err']."] $email $otp");return $ok;}
function ke_phone($p){$p=preg_replace('/\D/','',(string)$p);if(preg_match('/^(?:0|254)?([17]\d{8})$/',$p,$m))return '+254'.$m[1];return null;}
function send_sms($phone,$text){$GLOBALS['send_err']='';$to=ke_phone($phone);
 if(!$to){$GLOBALS['send_err']='That is not a valid Kenyan phone number.';return false;}
 if(SMS_KEY===''||!function_exists('curl_init')){$GLOBALS['send_err']='SMS is not set up yet (see secrets.php) or PHP curl is off.';dev_log("[SMS NOT SENT] $to $text");return false;}
 $u='https://api.'.(SMS_USER==='sandbox'?'sandbox.':'').'africastalking.com/version1/messaging';$d=['username'=>SMS_USER,'to'=>$to,'message'=>$text];if(SMS_SENDER!=='')$d['from']=SMS_SENDER;
 $h=curl_init($u);curl_setopt_array($h,[CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>http_build_query($d),CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>['apiKey: '.SMS_KEY,'Accept: application/json']]);
 $r=curl_exec($h);$code=curl_getinfo($h,CURLINFO_HTTP_CODE);$ce=curl_error($h);curl_close($h);$j=json_decode((string)$r,true);$st=$j['SMSMessageData']['Recipients'][0]['status']??'';
 $ok=$code>=200&&$code<300&&$st==='Success';if(!$ok){$GLOBALS['send_err']='SMS gateway said: '.($ce?:"$code $st");dev_log("[SMS FAILED] $to $text");}return $ok;}
