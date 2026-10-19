<?php require 'config.php'; need(); check();$me=(int)user()['id'];$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $to=$pdo->prepare('SELECT id FROM users WHERE id=? AND active=1 AND id<>?');$to->execute([(int)$_POST['to'],$me]);
  if(!$to->fetch()||trim($_POST['subject'])===''||trim($_POST['body'])==='')$err='Choose a recipient and fill in the subject and message.';
  else{$pdo->prepare('INSERT INTO messages(from_id,to_id,subject,body) VALUES(?,?,?,?)')->execute([$me,(int)$_POST['to'],trim($_POST['subject']),trim($_POST['body'])]);header('Location: messages.php?sent=1');exit;}}
$open=null;if(isset($_GET['open'])){$o=$pdo->prepare('SELECT m.*,u.name fn FROM messages m JOIN users u ON u.id=m.from_id WHERE m.id=? AND m.to_id=?');$o->execute([(int)$_GET['open'],$me]);$open=$o->fetch();
  if($open)$pdo->prepare('UPDATE messages SET read_at=NOW() WHERE id=? AND read_at IS NULL')->execute([$open['id']]);}
head('Messages');if($err)echo "<p class=err>".e($err)."</p>";if(isset($_GET['sent']))echo '<p class=ok>Message sent.</p>';
if($open)echo '<div class=card><b>'.e($open['subject']).'</b><br><small>From '.e($open['fn']).', '.substr($open['created_at'],0,16).'</small><p>'.nl2br(e($open['body'])).'</p></div>';?>
<div class=g><div class=card><h3>New message</h3><form method=post><?=field()?><label>To</label><select name=to><?php foreach($pdo->query("SELECT id,name,role FROM users WHERE active=1 AND id<>$me ORDER BY name") as $u)echo '<option value='.$u['id'].'>'.e($u['name'].' ('.$u['role'].')');?></select>
<label>Subject</label><input name=subject required><label>Message</label><textarea name=body required></textarea><button>Send</button></form></div>
<div class=card><h3>Inbox</h3><table><?php foreach($pdo->query("SELECT m.*,u.name fn FROM messages m JOIN users u ON u.id=m.from_id WHERE m.to_id=$me ORDER BY m.id DESC LIMIT 30") as $m)
echo '<tr><td>'.($m['read_at']?'':'<b>').'<a href="messages.php?open='.$m['id'].'">'.e($m['subject']).'</a><br><small>'.e($m['fn']).', '.substr($m['created_at'],0,16).'</small>';?></table></div></div><?php foot();
