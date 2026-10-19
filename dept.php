<?php require 'config.php'; need(['doctor','nurse','receptionist','records']); check();
$d=null;foreach(depts() as $x)if($x['slug']==($_GET['d']??''))$d=$x;if(!$d)die('Unknown department.');
$_SESSION['ctx']=$d['slug'];$msg='';$r=user()['role'];
if($_SERVER['REQUEST_METHOD']==='POST'){$vid=(int)$_POST['visit'];$to=(int)$_POST['to'];$ok=false;foreach(depts() as $x)if($x['id']==$to)$ok=true;
  $f=$pdo->prepare('SELECT dept_id FROM visits WHERE id=?');$f->execute([$vid]);$f=$f->fetch();
  if($ok&&$f){$pdo->prepare('INSERT INTO patient_transfers(visit_id,from_dept,to_dept,moved_by) VALUES(?,?,?,?)')->execute([$vid,$f['dept_id'],$to,user()['id']]);$pdo->prepare('UPDATE visits SET dept_id=? WHERE id=?')->execute([$to,$vid]);audit("moved visit $vid to dept $to");$msg='Patient moved.';}}
audit('viewed department '.$d['slug']);head($d['icon'].' '.$d['name']);
if($d['slug']=='ccc')echo '<p class=err>Confidential department. Every access is logged.</p>';if($msg)echo '<p class=ok>'.e($msg).'</p>';
$q=function($sql,$a=[])use($pdo){$s=$pdo->prepare($sql);$s->execute($a);return $s->fetchAll();};
$in=$q("SELECT v.*,p.patient_no,p.first_name,p.last_name FROM visits v JOIN patients p ON p.id=v.patient_id WHERE v.dept_id=? AND v.status<>'done' ORDER BY v.id",[$d['id']]);
$cand=$q("SELECT v.id,p.patient_no,p.first_name,p.last_name FROM visits v JOIN patients p ON p.id=v.patient_id WHERE v.status<>'done' AND v.created_at>=NOW()-INTERVAL 7 DAY AND (v.dept_id IS NULL OR v.dept_id<>?) ORDER BY v.id DESC",[$d['id']]);?>
<div class=card><h3>Receive a patient here</h3><form method=post style="display:flex;gap:8px"><?=field()?><input type=hidden name=to value=<?=$d['id']?>><select name=visit><?php foreach($cand as $c)echo '<option value='.$c['id'].'>#'.$c['id'].' '.e($c['patient_no'].' '.$c['first_name'].' '.$c['last_name']);?></select><button style="margin:0">Receive</button></form></div>
<div class=card><h3>Patients in <?=e($d['name'])?></h3><?php if(!$in):echo '<p>No patients here right now.</p>';else:?><table><tr><th>Patient<th>Stage<th>Fee<th>Actions<th>Move to</tr>
<?php foreach($in as $v):?><tr><td><?=e($v['patient_no'].' '.$v['first_name'].' '.$v['last_name'])?><td><?=e($v['status'])?><td><?=fee_paid($v['id'])?'Paid':'<b class=err>Unpaid</b>'?>
<td><a href="notes.php?visit=<?=$v['id']?>">Notes</a><?php if($r=='doctor'&&fee_paid($v['id']))echo ' | <a href="consult.php?visit='.$v['id'].'">Consult</a> | <a href="orders.php?visit='.$v['id'].'">Orders</a>';?>
<td><form method=post style="display:flex;gap:6px"><?=field()?><input type=hidden name=visit value=<?=$v['id']?>><select name=to><?php foreach(depts() as $x)if($x['id']!=$d['id'])echo '<option value='.$x['id'].'>'.e($x['name']);?></select><button style="margin:0">Move</button></form></tr><?php endforeach;?></table><?php endif;?></div><?php foot();
