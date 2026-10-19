<?php require 'config.php'; need(['receptionist','records','nurse','doctor','cashier']); check();
$pid=(int)($_GET['patient']??$_POST['patient']??0);$p=$pdo->prepare('SELECT * FROM patients WHERE id=?');$p->execute([$pid]);$p=$p->fetch();if(!$p)die('Patient not found.');
$o=$pdo->prepare("SELECT id,status FROM visits WHERE patient_id=? AND status<>'done' ORDER BY id DESC LIMIT 1");$o->execute([$pid]);$open=$o->fetch();
$prob=['General illness, fever or cough'=>'general-opd','Tooth or mouth problem'=>'dental','Skin problem'=>'dermatology','Ear, nose or throat'=>'ent','Fracture, injury or accident'=>'orthotrauma','Plaster or cast care'=>'plaster-room','Pregnancy check (antenatal)'=>'antenatal-ward','In labour'=>'labour-ward','After delivery (postnatal)'=>'postnatal-ward','Newborn baby problem'=>'nbu','HIV or chronic care (CCC)'=>'ccc','X-ray'=>'xray','CT scan'=>'ct-scan','Other'=>'general-opd'];
$ids=[];foreach(depts() as $d)$ids[$d['slug']]=(int)$d['id'];$err='';
if($_SERVER['REQUEST_METHOD']==='POST'&&!$open){$to=(int)$_POST['dept'];$det=trim($_POST['details']);
  if(!in_array($to,$ids,true)||$det==='')$err='Describe the problem and choose a service point.';
  else{$why=substr(trim($_POST['problem']).': '.$det,0,250);$pdo->prepare('INSERT INTO visits(patient_id,dept_id,reason) VALUES(?,?,?)')->execute([$pid,$to,$why]);$vid=$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO patient_transfers(visit_id,from_dept,to_dept,moved_by) VALUES(?,NULL,?,?)')->execute([$vid,$to,user()['id']]);start_fee($vid,$pid);audit("visit $vid started: $why");
    $iv=$pdo->prepare('SELECT id FROM invoices WHERE visit_id=? ORDER BY id LIMIT 1');$iv->execute([$vid]);$iv=$iv->fetchColumn();
    $go=($iv&&in_array(user()['role'],['cashier','receptionist','admin']))?'pay.php?invoice='.$iv:'trace.php?pid='.$pid;header('Location: '.$go);exit;}}
head('Start visit: '.$p['first_name'].' '.$p['last_name'].' ('.$p['patient_no'].')');if($err)echo '<p class=err>'.e($err).'</p>';
if($open){echo '<div class=card><p>This patient already has an active visit (#'.$open['id'].', stage '.e($open['status']).').</p><a href="trace.php?pid='.$pid.'">Open it</a></div>';foot();exit;}?>
<div class=card style="max-width:620px"><form method=post><?=field()?><input type=hidden name=patient value=<?=$pid?>>
<label>What is the problem?</label><select name=problem id=pb><?php foreach($prob as $l=>$s)echo '<option value="'.e($l).'" data-d="'.($ids[$s]??'').'">'.e($l);?></select>
<label>Service point (picked from the problem, you can change it)</label><select name=dept id=dp><?php foreach(depts() as $d)echo '<option value='.$d['id'].'>'.e($d['name']);?></select>
<label>Details of the complaint</label><textarea name=details required placeholder="e.g. fever for 3 days, headache, vomiting"></textarea>
<button>Start visit and go to payment</button></form></div>
<script>var pb=document.getElementById("pb"),dp=document.getElementById("dp");function sync(){var d=pb.selectedOptions[0].dataset.d;if(d)dp.value=d}pb.onchange=sync;sync();</script><?php foot();
