<?php require 'config.php'; need(['nurse']); check();
$id=(int)($_GET['visit']??0);$s=$pdo->prepare('SELECT v.*,p.first_name,p.last_name,p.patient_no FROM visits v JOIN patients p ON p.id=v.patient_id WHERE v.id=?');$s->execute([$id]);$v=$s->fetch();
if(!$v)die('Visit not found.');if(FEE_BEFORE_TRIAGE)fee_block($id);$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $n=fn($k)=>($_POST[$k]??'')===''?null:$_POST[$k];
  if(trim($_POST['complaint'])==='')$err='Enter the main complaint.';
  elseif(($t=$n('temp'))!==null&&($t<30||$t>45))$err='Temperature looks wrong (30-45 C).';
  else{$pdo->prepare('REPLACE INTO triage(visit_id,temp,bp,pulse,resp,spo2,weight,height,complaint,nurse_id) VALUES(?,?,?,?,?,?,?,?,?,?)')
    ->execute([$id,$n('temp'),$n('bp'),$n('pulse'),$n('resp'),$n('spo2'),$n('weight'),$n('height'),trim($_POST['complaint']),user()['id']]);
    $pdo->prepare("UPDATE visits SET status='consultation' WHERE id=?")->execute([$id]);audit("triage visit $id");header('Location: slip.php?kind=triage&visit='.$id);exit;}
}
head('Triage: '.$v['first_name'].' '.$v['last_name'].' ('.$v['patient_no'].')');if($err)echo "<p class=err>".e($err)."</p>";?>
<div class=card><form method=post class=g><?=field()?>
<div><label>Temperature (C)</label><input name=temp type=number step=0.1></div><div><label>Blood pressure (e.g. 120/80)</label><input name=bp pattern="\d{2,3}/\d{2,3}"></div>
<div><label>Pulse (bpm)</label><input name=pulse type=number></div><div><label>Respiratory rate</label><input name=resp type=number></div>
<div><label>SpO2 (%)</label><input name=spo2 type=number max=100></div><div><label>Weight (kg)</label><input name=weight type=number step=0.1></div>
<div><label>Height (cm)</label><input name=height type=number step=0.1></div>
<div style="grid-column:1/-1"><label>Main complaint</label><textarea name=complaint required></textarea><button>Save and send to doctor</button></div></form></div><?php foot();
