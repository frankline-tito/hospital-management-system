<?php require 'config.php'; need(['doctor','nurse','records','receptionist','cashier']);
$id=(int)($_GET['id']??0);$p=$pdo->prepare('SELECT * FROM patients WHERE id=?');$p->execute([$id]);$p=$p->fetch();if(!$p)die('Patient not found.');
audit("viewed record of patient $id");head('Patient record: '.$p['first_name'].' '.$p['last_name']);
$age=(new DateTime($p['dob']))->diff(new DateTime())->y;
echo '<div class=card><b>'.e($p['patient_no']).'</b> | '.$age.' yrs, '.e($p['gender']).' | ID '.e($p['id_number']).' | Phone '.e($p['phone']).'<br>Next of kin: '.e($p['next_of_kin']).' '.e($p['nok_phone']).'</div>';
$q=function($sql,$a=[])use($pdo){$s=$pdo->prepare($sql);$s->execute($a);return $s->fetchAll();};
foreach($q('SELECT * FROM visits WHERE patient_id=? ORDER BY id DESC',[$id]) as $v){$vid=$v['id'];echo '<div class=card><h3>Visit #'.$vid.' - '.substr($v['created_at'],0,10).' ('.e($v['status']).')</h3>';
  foreach($q('SELECT * FROM triage WHERE visit_id=?',[$vid]) as $t)echo '<p>Complaint: '.e($t['complaint']).'<br>Temp '.e($t['temp']).', BP '.e($t['bp']).', Pulse '.e($t['pulse']).', SpO2 '.e($t['spo2']).', Weight '.e($t['weight']).'</p>';
  foreach($q('SELECT * FROM consultations WHERE visit_id=?',[$vid]) as $c)echo '<p><b>Diagnosis:</b> '.e($c['diagnosis']).' '.e($c['icd10']).'<br>Plan: '.e($c['plan']).'</p>';
  foreach($q('SELECT t.name,o.result,o.abnormal FROM lab_orders o JOIN lab_tests t ON t.id=o.test_id WHERE o.visit_id=? AND o.status="done"',[$vid]) as $o)echo '<div>Lab: '.e($o['name']).': '.e($o['result']).($o['abnormal']?' (abnormal)':'').'</div>';
  foreach($q('SELECT d.name,x.dose,x.duration,x.status FROM prescriptions x JOIN drugs d ON d.id=x.drug_id WHERE x.visit_id=?',[$vid]) as $x)echo '<div>Drug: '.e($x['name'].' - '.$x['dose'].' '.$x['duration'].' ('.$x['status'].')').'</div>';
  foreach($q('SELECT a.*,b.bed_no,w.name wn FROM admissions a JOIN beds b ON b.id=a.bed_id JOIN wards w ON w.id=b.ward_id WHERE a.visit_id=?',[$vid]) as $a)echo '<div>Admitted: '.e($a['wn'].' '.$a['bed_no']).' from '.substr($a['admitted_at'],0,10).($a['discharged_at']?' to '.substr($a['discharged_at'],0,10):' (still in)').'</div>';
  echo '</div>';}
foreach($q('SELECT * FROM referrals WHERE patient_id=? ORDER BY id DESC',[$id]) as $r)echo '<div class=card>Referral '.e($r['direction']).': '.e($r['facility']).' ('.e($r['urgency']).', '.e($r['status']).')<br>'.e($r['reason']).'</div>';
foot();
