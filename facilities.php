<?php require 'config.php'; need(['admin']); check();$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['name'])){try{$pdo->prepare('INSERT INTO facilities(name,code,level) VALUES(?,?,?)')->execute([trim($_POST['name']),trim($_POST['code']),$_POST['level']]);}catch(PDOException $x){$err='That facility already exists.';}}
  else{$pdo->prepare('UPDATE users SET facility_id=? WHERE id=?')->execute([$_POST['facility']?:null,(int)$_POST['user']]);audit('assigned facility to user '.(int)$_POST['user']);}
}
head('Facilities');if($err)echo "<p class=err>".e($err)."</p>";$fs=$pdo->query('SELECT * FROM facilities ORDER BY name')->fetchAll();?>
<div class=card><h3>Add facility</h3><form method=post class=g><?=field()?><div><input name=name placeholder="Facility name" required></div><div><input name=code placeholder="MFL code"></div>
<div><select name=level><?php foreach(['Level 2','Level 3','Level 4','Level 5','Level 6'] as $l)echo "<option>$l";?></select></div><div><button style="margin:0">Add</button></div></form></div>
<div class=card><h3>Assign staff to a facility</h3><table><tr><th>Staff<th>Role<th>Facility</tr>
<?php foreach($pdo->query('SELECT id,name,role,facility_id FROM users ORDER BY name') as $u):?><tr><td><?=e($u['name'])?><td><?=e($u['role'])?><td><form method=post style="display:flex;gap:6px"><?=field()?><input type=hidden name=user value=<?=$u['id']?>>
<select name=facility><option value="">All / none<?php foreach($fs as $f)echo '<option value='.$f['id'].($f['id']==$u['facility_id']?' selected':'').'>'.e($f['name']);?></select><button style="margin:0">Save</button></form></tr><?php endforeach;?></table>
<p>Staff with a facility only see that facility's telemedicine requests and field reports. Admins see all.</p></div><?php foot();
