<?php require 'config.php'; need(['doctor']); check();
$id=(int)($_GET['visit']??0);$s=$pdo->prepare('SELECT v.*,p.first_name,p.last_name,p.patient_no,p.dob,p.gender FROM visits v JOIN patients p ON p.id=v.patient_id WHERE v.id=?');$s->execute([$id]);$v=$s->fetch();
if(!$v)die('Visit not found.');fee_block($id);
$t=$pdo->prepare('SELECT * FROM triage WHERE visit_id=?');$t->execute([$id]);$t=$t->fetch();$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $map=['discharge'=>'billing','lab'=>'lab','pharmacy'=>'pharmacy','admit'=>'billing','refer'=>'billing'];$o=$_POST['outcome']??'';
  if(trim($_POST['diagnosis'])===''||!isset($map[$o]))$err='Enter a diagnosis and choose an outcome.';elseif($o=='lab'&&empty($_POST['tests']))$err='Choose at least one lab test.';elseif($o=='pharmacy'&&!array_filter($_POST['drug']??[]))$err='Add at least one prescription.';
  else{$pdo->prepare('REPLACE INTO consultations(visit_id,history,examination,diagnosis,icd10,plan,outcome,doctor_id) VALUES(?,?,?,?,?,?,?,?)')
    ->execute([$id,$_POST['history'],$_POST['examination'],trim($_POST['diagnosis']),strtoupper(trim($_POST['icd10'])),$_POST['plan'],$o,user()['id']]);
    foreach($_POST['tests']??[] as $t)$pdo->prepare('INSERT INTO lab_orders(visit_id,test_id,ordered_by) VALUES(?,?,?)')->execute([$id,(int)$t,user()['id']]);
    foreach($_POST['drug']??[] as $k=>$dg){$qt=(int)($_POST['qty'][$k]??0);if($dg&&$qt>0)$pdo->prepare('INSERT INTO prescriptions(visit_id,drug_id,dose,duration,qty,prescribed_by) VALUES(?,?,?,?,?,?)')->execute([$id,(int)$dg,$_POST['dose'][$k]??'',$_POST['dur'][$k]??'',$qt,user()['id']]);}
    order_extra($id);advance($id,$map[$o]);audit("consultation visit $id");header('Location: slip.php?kind=consult&visit='.$id);exit;}
}
head('Consultation: '.$v['first_name'].' '.$v['last_name'].' ('.$v['patient_no'].')');if($err)echo "<p class=err>".e($err)."</p>";
$age=(new DateTime($v['dob']))->diff(new DateTime())->y;?>
<div class=card><b><?=$age?> yrs, <?=e($v['gender'])?></b><br>
<?php if($t){$bmi=($t['weight']&&$t['height'])?round($t['weight']/pow($t['height']/100,2),1):'-';
echo 'Complaint: '.e($t['complaint']).'<br>Temp '.e($t['temp']).' | BP '.e($t['bp']).' | Pulse '.e($t['pulse']).' | RR '.e($t['resp']).' | SpO2 '.e($t['spo2']).' | Weight '.e($t['weight']).' | BMI '.$bmi;}else echo 'No vitals recorded.';?></div>
<div class=card><h3>Past diagnoses</h3><?php $h=$pdo->prepare('SELECT c.diagnosis,c.created_at FROM consultations c JOIN visits x ON x.id=c.visit_id WHERE x.patient_id=? AND c.visit_id<>? ORDER BY c.id DESC LIMIT 5');$h->execute([$v['patient_id'],$id]);
$h=$h->fetchAll();if(!$h)echo 'None on record.';foreach($h as $r)echo '<div>'.substr($r['created_at'],0,10).': '.e($r['diagnosis']).'</div>';?></div>
<div class=card><form method=post><?=field()?>
<label>History</label><textarea name=history></textarea><label>Examination</label><textarea name=examination></textarea>
<label>Diagnosis</label><input name=diagnosis required><label>ICD-10 code (optional)</label><input name=icd10 maxlength=10>
<label>Treatment plan</label><textarea name=plan></textarea>
<label>Order lab tests</label><div style="display:flex;flex-wrap:wrap;gap:12px"><?php foreach($pdo->query('SELECT * FROM lab_tests ORDER BY name') as $t)echo '<label style="font-weight:400"><input type=checkbox name="tests[]" value='.$t['id'].' style="width:auto"> '.e($t['name']).'</label>';?></div>
<?=extra_form()?><label>Prescriptions (drug, dose, duration, quantity)</label><?php $dr=$pdo->query('SELECT id,name,stock FROM drugs ORDER BY name')->fetchAll();for($k=0;$k<3;$k++){echo '<div class=g><select name="drug[]"><option value="">- drug -</option>';foreach($dr as $d)echo '<option value='.$d['id'].'>'.e($d['name']).' (stock '.$d['stock'].')</option>';echo '</select><input name="dose[]" placeholder="Dose, e.g. 1 tab 3 times a day"><input name="dur[]" placeholder="Duration"><input name="qty[]" type=number min=1 placeholder="Qty"></div>';}?>
<label>Next step</label><select name=outcome><option value=discharge>Discharge, go to billing<option value=lab>Send to laboratory<option value=pharmacy>Send to pharmacy<option value=admit>Admit (billing for now)<option value=refer>Refer (billing for now)</select>
<button>Save consultation</button></form></div><?php foot();
