<?php require 'config.php'; need(['nurse','doctor','receptionist']); check();$msg=$err='';$admin=user()['role']=='admin';
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
  if($admin&&isset($_POST['ward'])){$pdo->prepare('INSERT INTO wards(name,daily_rate) VALUES(?,?)')->execute([trim($_POST['ward']),max(0,(float)$_POST['rate'])]);$msg='Ward added.';}
  elseif($admin&&isset($_POST['bed_no'])){$pdo->prepare('INSERT INTO beds(ward_id,bed_no) VALUES(?,?)')->execute([(int)$_POST['ward_id'],trim($_POST['bed_no'])]);$msg='Bed added.';}
  elseif(isset($_POST['admit'])){$vid=(int)$_POST['visit'];$bed=(int)$_POST['bed'];
    $b=$pdo->query("SELECT COUNT(*) FROM admissions WHERE discharged_at IS NULL AND (bed_id=$bed OR visit_id=$vid)")->fetchColumn();
    $v=$pdo->query("SELECT patient_id FROM visits WHERE id=$vid")->fetchColumn();
    if($b||!$v)$err='That bed is taken, or the patient is already admitted.';
    else{$pdo->prepare('INSERT INTO admissions(patient_id,visit_id,bed_id,reason,admitted_by) VALUES(?,?,?,?,?)')->execute([$v,$vid,$bed,$_POST['reason'],user()['id']]);$pdo->exec("UPDATE visits SET status='admitted' WHERE id=$vid");audit("admitted visit $vid");$msg='Patient admitted.';}}
  elseif(isset($_POST['discharge'])){$a=$pdo->prepare('SELECT visit_id FROM admissions WHERE id=? AND discharged_at IS NULL');$a->execute([(int)$_POST['discharge']]);$vid=$a->fetchColumn();
    if($vid){$pdo->prepare('UPDATE admissions SET discharged_at=NOW(),discharge_note=? WHERE id=?')->execute([$_POST['note'],(int)$_POST['discharge']]);$pdo->exec("UPDATE visits SET status='billing' WHERE id=$vid");audit("discharged visit $vid");$msg='Discharged. Sent to billing.';}}
  }catch(PDOException $x){$err='That name or bed number already exists.';}
}
head('Inpatient');if($msg)echo "<p class=ok>".e($msg)."</p>";if($err)echo "<p class=err>".e($err)."</p>";
$wards=$pdo->query('SELECT * FROM wards ORDER BY name')->fetchAll();
$free=$pdo->query('SELECT b.id,b.bed_no,w.name FROM beds b JOIN wards w ON w.id=b.ward_id WHERE b.id NOT IN (SELECT bed_id FROM admissions WHERE discharged_at IS NULL) ORDER BY w.name,b.bed_no')->fetchAll();
$vis=$pdo->query("SELECT v.id,p.patient_no,p.first_name,p.last_name FROM visits v JOIN patients p ON p.id=v.patient_id WHERE v.status NOT IN ('done','admitted') AND v.created_at>=NOW()-INTERVAL 3 DAY ORDER BY v.id DESC")->fetchAll();?>
<div class=card><h3>Admit patient</h3><?php if(!$free):echo '<p>No free beds. '.($admin?'Add beds below.':'Ask the admin to add beds.').'</p>';else:?>
<form method=post class=g><?=field()?><input type=hidden name=admit value=1><div><label>Visit</label><select name=visit><?php foreach($vis as $v)echo '<option value='.$v['id'].'>#'.$v['id'].' '.e($v['patient_no'].' '.$v['first_name'].' '.$v['last_name']);?></select></div>
<div><label>Bed</label><select name=bed><?php foreach($free as $b)echo '<option value='.$b['id'].'>'.e($b['name'].' - '.$b['bed_no']);?></select></div><div><label>Reason</label><input name=reason required></div><div><button>Admit</button></div></form><?php endif;?></div>
<div class=card><h3>Current inpatients</h3><table><tr><th>Patient<th>Ward and bed<th>Since<th>Discharge</tr>
<?php foreach($pdo->query('SELECT a.*,p.patient_no,p.first_name,p.last_name,b.bed_no,w.name wn FROM admissions a JOIN patients p ON p.id=a.patient_id JOIN beds b ON b.id=a.bed_id JOIN wards w ON w.id=b.ward_id WHERE a.discharged_at IS NULL ORDER BY a.id') as $a)
echo '<tr><td>'.e($a['patient_no'].' '.$a['first_name'].' '.$a['last_name']).'<td>'.e($a['wn'].' - '.$a['bed_no']).'<td>'.substr($a['admitted_at'],0,16).'<td><form method=post style="display:flex;gap:6px">'.field().'<input name=note placeholder="Discharge note" required><button name=discharge value='.$a['id'].' style="margin:0">Discharge</button></form>';?></table></div>
<?php if($admin):?><div class=card><h3>Wards and beds</h3><form method=post class=g><?=field()?><div><input name=ward placeholder="Ward name" required></div><div><input name=rate type=number step=0.01 placeholder="Daily rate (KES)"></div><div><button style="margin:0">Add ward</button></div></form>
<form method=post class=g><?=field()?><div><select name=ward_id><?php foreach($wards as $w)echo '<option value='.$w['id'].'>'.e($w['name']);?></select></div><div><input name=bed_no placeholder="Bed number" required></div><div><button style="margin:0">Add bed</button></div></form></div><?php endif;foot();
