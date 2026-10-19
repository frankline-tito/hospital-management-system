<?php require 'config.php'; need(['admin']); check();
$msg=$err='';$roles=['admin','receptionist','nurse','doctor','lab','pharmacist','cashier','records','chp','radiographer'];
if($_SERVER['REQUEST_METHOD']==='POST'){
  $id=(int)($_POST['id']??0);
  if(isset($_POST['add'])){
    if(strlen($_POST['password'])<8||!in_array($_POST['role'],$roles))$err='Use a password of 8+ characters and a valid role.';
    else try{$pdo->prepare('INSERT INTO users(name,email,phone,password,role) VALUES(?,?,?,?,?)')->execute([trim($_POST['name']),trim($_POST['email']),$_POST['phone'],password_hash($_POST['password'],PASSWORD_DEFAULT),$_POST['role']]);audit('added user '.$_POST['email']);$msg='User added.';}
    catch(PDOException $x){$err='That email is already in use.';}
  }elseif(isset($_POST['toggle'])&&$id!=user()['id']){$pdo->prepare('UPDATE users SET active=1-active WHERE id=?')->execute([$id]);audit("toggled user $id");}
  elseif(isset($_POST['reset'])&&strlen($_POST['password'])>=8){$pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($_POST['password'],PASSWORD_DEFAULT),$id]);audit("reset password user $id");$msg='Password reset.';}
}
head('Users');if($msg)echo "<p class=ok>".e($msg)."</p>";if($err)echo "<p class=err>".e($err)."</p>";?>
<div class=card><h3>Add staff</h3><form method=post class=g><?=field()?><input type=hidden name=add value=1>
<div><label>Full name</label><input name=name required></div><div><label>Email</label><input name=email type=email required></div><div><label>Phone</label><input name=phone></div>
<div><label>Role</label><select name=role><?php foreach($roles as $r)echo "<option>$r";?></select></div><div><label>Temporary password</label><input name=password type=password minlength=8 required></div>
<div><button>Add user</button></div></form></div>
<div class=card><table><tr><th>Name<th>Email<th>Role<th>Status<th></tr>
<?php foreach($pdo->query('SELECT * FROM users ORDER BY id') as $u):?><tr><td><?=e($u['name'])?><td><?=e($u['email'])?><td><?=e($u['role'])?><td><?=$u['active']?'Active':'Disabled'?>
<td><form method=post style="display:flex;gap:6px"><?=field()?><input type=hidden name=id value=<?=$u['id']?>><input name=password type=password placeholder="New password" style="width:130px">
<button name=reset value=1 style="margin:0">Reset</button><?php if($u['id']!=user()['id'])echo '<button name=toggle value=1 style="margin:0">'.($u['active']?'Disable':'Enable').'</button>';?></form></tr><?php endforeach;?></table></div><?php foot();
