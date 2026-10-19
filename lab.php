<?php require 'config.php'; need(['lab']); check();$msg=$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['test_name'])){try{$pdo->prepare('INSERT INTO lab_tests(name,price) VALUES(?,?)')->execute([trim($_POST['test_name']),max(0,(float)$_POST['price'])]);$msg='Test added.';}catch(PDOException $x){$err='That test already exists.';}}
  else{$o=$pdo->prepare("SELECT * FROM lab_orders WHERE id=? AND status='pending'");$o->execute([(int)$_POST['order']]);$o=$o->fetch();
    if(!$o||trim($_POST['result'])==='')$err='Enter the result.';
    else{$pdo->prepare("UPDATE lab_orders SET status='done',result=?,abnormal=?,done_by=?,done_at=NOW() WHERE id=?")->execute([trim($_POST['result']),isset($_POST['abnormal'])?1:0,user()['id'],$o['id']]);
      advance($o['visit_id'],'consultation');audit("lab result {$o['id']}");header('Location: slip.php?kind=lab&visit='.$o['visit_id'].'&back=lab.php');exit;}}
}
head('Laboratory');if($msg)echo "<p class=ok>".e($msg)."</p>";if($err)echo "<p class=err>".e($err)."</p>";?>
<div class=card><h3>Pending tests</h3><?php $r=$pdo->query("SELECT o.id,t.name test,p.patient_no,p.first_name,p.last_name FROM lab_orders o JOIN lab_tests t ON t.id=o.test_id JOIN visits v ON v.id=o.visit_id JOIN patients p ON p.id=v.patient_id WHERE o.status='pending' ORDER BY o.id")->fetchAll();
if(!$r)echo '<p>No pending tests.</p>';foreach($r as $o):?>
<form method=post class=g style="border-bottom:1px solid var(--line);padding-bottom:8px"><?=field()?><input type=hidden name=order value=<?=$o['id']?>>
<div><b><?=e($o['test'])?></b><br><?=e($o['patient_no'].' '.$o['first_name'].' '.$o['last_name'])?></div><div><input name=result placeholder="Result" required><label style="font-weight:400"><input type=checkbox name=abnormal style="width:auto"> Abnormal</label></div><div><button style="margin:0">Save result</button></div></form><?php endforeach;?></div>
<div class=card><h3>Add test to catalogue</h3><form method=post><?=field()?><input name=test_name placeholder="e.g. Malaria RDT" required><label>Price (KES)</label><input name=price type=number step=0.01 min=0 value=0><button>Add test</button></form></div><?php foot();
