<?php require 'config.php'; need(['receptionist','records','nurse','doctor','cashier']); check();
$tab=$_GET['tab']??'trace';$pid=(int)($_GET['pid']??0);$msg='';
$q=function($sql,$a=[])use($pdo){$s=$pdo->prepare($sql);$s->execute($a);return $s->fetchAll();};
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['book'])){$when=$_POST['when'];if(!$when)$msg='Pick a date and time.';else{$pdo->prepare('INSERT INTO bookings(patient_id,dept_id,service,booked_for,notes,created_by) VALUES(?,?,?,?,?,?)')->execute([(int)$_POST['patient'],(int)$_POST['dept']?:null,trim($_POST['service']),str_replace('T',' ',$when),trim($_POST['notes']),user()['id']]);audit('booking');$msg='Booking saved.';}}
  elseif(isset($_POST['set'])){$b=$q("SELECT * FROM bookings WHERE id=? AND status='booked'",[(int)$_POST['id']]);$st=$_POST['set'];
    if($b&&in_array($st,['arrived','missed','cancelled'])){$b=$b[0];
      if($st=='arrived'){$pdo->prepare('INSERT INTO visits(patient_id,dept_id) VALUES(?,?)')->execute([$b['patient_id'],$b['dept_id']]);start_fee($pdo->lastInsertId(),$b['patient_id']);$msg='Patient arrived: visit started and the consultation fee invoice created.';}
      $pdo->prepare('UPDATE bookings SET status=? WHERE id=?')->execute([$st,$b['id']]);audit("booking {$b['id']} $st");}}
  $tab='book';}
head('Patient Tracing & Booking');if($msg)echo '<p class=ok>'.e($msg).'</p>';
echo '<p><a href="trace.php">Tracing</a> | <a href="trace.php?tab=book">Bookings</a></p>';
if($tab=='book'):$pts=$q('SELECT id,patient_no,first_name,last_name FROM patients ORDER BY id DESC LIMIT 200');?>
<div class=card><h3>New booking</h3><form method=post class=g><?=field()?><input type=hidden name=book value=1>
<div><label>Patient</label><select name=patient><?php foreach($pts as $p)echo '<option value='.$p['id'].'>'.e($p['patient_no'].' '.$p['first_name'].' '.$p['last_name']);?></select></div>
<div><label>Service point</label><select name=dept><option value=0>Any<?php foreach(depts() as $d)echo '<option value='.$d['id'].'>'.e($d['name']);?></select></div>
<div><label>Date and time</label><input type=datetime-local name=when required></div><div><label>Service / reason</label><input name=service placeholder="e.g. Review, ANC visit, vaccination" required></div>
<div><label>Notes</label><input name=notes></div><div><button>Save booking</button></div></form></div>
<div class=card><h3>Bookings</h3><table><tr><th>When<th>Patient<th>Service point<th>Service<th>Status<th></tr>
<?php foreach($q("SELECT b.*,p.patient_no,p.first_name,p.last_name,d.name dn FROM bookings b JOIN patients p ON p.id=b.patient_id LEFT JOIN departments d ON d.id=b.dept_id ORDER BY b.status='booked' DESC,b.booked_for LIMIT 60") as $b):$late=$b['status']=='booked'&&strtotime($b['booked_for'])<time();?>
<tr><td><?=substr($b['booked_for'],0,16)?><?=$late?' <b class=err>overdue</b>':''?><td><?=e($b['patient_no'].' '.$b['first_name'].' '.$b['last_name'])?><td><?=e($b['dn']?:'Any')?><td><?=e($b['service'])?><td><?=$b['status']?>
<td><?php if($b['status']=='booked'):?><form method=post style="display:flex;gap:4px"><?=field()?><input type=hidden name=id value=<?=$b['id']?>><button name=set value=arrived style="margin:0">Arrived</button><button name=set value=missed style="margin:0;background:#a23b2a">Missed</button><button name=set value=cancelled style="margin:0;background:#5b706e">Cancel</button></form><?php endif;?></tr><?php endforeach;?></table></div>
<?php else:
$sq=trim($_GET['q']??'');?>
<div class=card><form style="display:flex;gap:8px"><input name=q value="<?=e($sq)?>" placeholder="Find a patient to trace: name, patient no, ID or phone"><button style="margin:0">Trace</button></form>
<?php if($sq!==''){$l="%$sq%";foreach($q('SELECT id,patient_no,first_name,last_name,phone FROM patients WHERE first_name LIKE ? OR last_name LIKE ? OR patient_no LIKE ? OR id_number LIKE ? OR phone LIKE ? LIMIT 10',[$l,$l,$l,$l,$l]) as $p)echo '<div><a href="trace.php?pid='.$p['id'].'">'.e($p['patient_no'].' '.$p['first_name'].' '.$p['last_name'].' '.$p['phone']).'</a></div>';}?></div>
<?php if($pid){$p=$q('SELECT * FROM patients WHERE id=?',[$pid]);if(!$p)die('Patient not found');$p=$p[0];audit("traced patient $pid");
 $cur=$q("SELECT v.*,d.name dn FROM visits v LEFT JOIN departments d ON d.id=v.dept_id WHERE v.patient_id=? AND v.status<>'done' ORDER BY v.id DESC LIMIT 1",[$pid]);?>
<div class=card><h3><?=e($p['first_name'].' '.$p['last_name'].' ('.$p['patient_no'].')')?></h3>
<?php if($cur){$c=$cur[0];echo '<p>Visit #'.$c['id'].' | stage <b>'.e($c['status']).'</b>'.($c['dn']?' | service point <b>'.e($c['dn']).'</b>':'').'</p><p>Reason for visit: <b>'.e($c['reason']?:'not recorded').'</b></p>';
echo fee_paid($c['id'])?'<p class=ok>Consultation fee paid.</p>':'<p class=err>Consultation fee not paid.</p>';
foreach($q('SELECT y.id,y.amount,y.method,y.mpesa_ref,y.bank_ref FROM payments y JOIN invoices i ON i.id=y.invoice_id WHERE i.visit_id=? ORDER BY y.id',[$c['id']]) as $y)echo '<div>Payment KES '.number_format($y['amount'],2).' ('.e($y['method'].' '.$y['mpesa_ref'].$y['bank_ref']).') <a href="receipt.php?payment='.$y['id'].'" target=_blank>Receipt</a></div>';
if(in_array(user()['role'],['doctor','admin'])&&fee_paid($c['id']))echo '<p><a href="consult.php?visit='.$c['id'].'">Consult</a> | <a href="orders.php?visit='.$c['id'].'">Orders and results</a> | <a href="notes.php?visit='.$c['id'].'">Notes</a></p>';}else echo '<p>No active visit.</p>';?>
<a href="patient.php?id=<?=$pid?>">Open full record</a></div>
<div class=card><h3>Journey</h3><div class=tl>
<?php $S=[["SELECT v.created_at t,'🚪' i,CONCAT('Visit #',v.id,' started') d FROM visits v WHERE v.patient_id=?"],
["SELECT x.created_at,'🩺',CONCAT('Triage: ',COALESCE(x.complaint,'')) FROM triage x JOIN visits v ON v.id=x.visit_id WHERE v.patient_id=?"],
["SELECT c.created_at,'⚕️',CONCAT('Consultation: ',c.diagnosis) FROM consultations c JOIN visits v ON v.id=c.visit_id WHERE v.patient_id=?"],
["SELECT o.created_at,'🧪',CONCAT('Lab ordered: ',t.name) FROM lab_orders o JOIN lab_tests t ON t.id=o.test_id JOIN visits v ON v.id=o.visit_id WHERE v.patient_id=?"],
["SELECT o.done_at,'📄',CONCAT('Lab result: ',t.name) FROM lab_orders o JOIN lab_tests t ON t.id=o.test_id JOIN visits v ON v.id=o.visit_id WHERE v.patient_id=? AND o.status='done'"],
["SELECT x.created_at,'💊',CONCAT('Prescribed: ',d.name) FROM prescriptions x JOIN drugs d ON d.id=x.drug_id JOIN visits v ON v.id=x.visit_id WHERE v.patient_id=?"],
["SELECT y.created_at,'💳',CONCAT('Paid KES ',y.amount,' by ',y.method) FROM payments y JOIN invoices i ON i.id=y.invoice_id WHERE i.patient_id=?"],
["SELECT a.admitted_at,'🛏️','Admitted to ward' FROM admissions a WHERE a.patient_id=?"],
["SELECT a.discharged_at,'🏠','Discharged from ward' FROM admissions a WHERE a.patient_id=? AND a.discharged_at IS NOT NULL"],
["SELECT t.created_at,'🔀',CONCAT('Moved to ',d.name) FROM patient_transfers t JOIN departments d ON d.id=t.to_dept JOIN visits v ON v.id=t.visit_id WHERE v.patient_id=?"],
["SELECT o.reported_at,'🩻',CONCAT('Imaging report: ',e.name) FROM imaging_orders o JOIN imaging_exams e ON e.id=o.exam_id JOIN visits v ON v.id=o.visit_id WHERE v.patient_id=? AND o.status='reported'"],
["SELECT o.done_at,'🩹',CONCAT('Procedure done: ',c.name) FROM procedure_orders o JOIN procedures_catalog c ON c.id=o.proc_id JOIN visits v ON v.id=o.visit_id WHERE v.patient_id=? AND o.status='done'"],
["SELECT i.given_at,'💉',CONCAT(i.item,' ',COALESCE(i.dose_no,'')) FROM immunization_records i WHERE i.patient_id=?"],
["SELECT n.created_at,'📝',CONCAT('Note: ',n.title) FROM patient_notes n WHERE n.patient_id=?"]];
$ev=$q(implode(' UNION ALL ',array_column($S,0)).' ORDER BY t DESC LIMIT 100',array_fill(0,count($S),$pid));
if(!$ev)echo '<p>Nothing recorded yet.</p>';foreach($ev as $x)echo '<div><b>'.$x['i'].'</b><small>'.substr($x['t'],0,16).'</small><br>'.e($x['d']).'</div>';?></div></div>
<?php }else{?><div class=card><h3>Where is everyone now?</h3><table><tr><th>Patient<th>Stage<th>Service point<th>Waiting<th>Fee<th></tr>
<?php foreach($q("SELECT v.*,p.patient_no,p.first_name,p.last_name,d.name dn,TIMESTAMPDIFF(MINUTE,v.created_at,NOW()) w FROM visits v JOIN patients p ON p.id=v.patient_id LEFT JOIN departments d ON d.id=v.dept_id WHERE v.status<>'done' ORDER BY v.id") as $v)
echo '<tr><td>'.e($v['patient_no'].' '.$v['first_name'].' '.$v['last_name']).'<td>'.e($v['status']).'<td>'.e($v['dn']?:'-').'<td>'.floor($v['w']/60).'h '.($v['w']%60).'m<td>'.(fee_paid($v['id'])?'Paid':'<b class=err>Unpaid</b>').'<td><a href="trace.php?pid='.$v['patient_id'].'">Trace</a>';?></table></div><?php }
endif;foot();
