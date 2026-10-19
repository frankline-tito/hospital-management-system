<?php require 'config.php'; need(['cashier','receptionist']); check();$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $vid=(int)$_POST['visit'];$fee=max(0,(float)$_POST['fee']);$v=$pdo->prepare('SELECT * FROM visits WHERE id=?');$v->execute([$vid]);$v=$v->fetch();
  if($v){$pdo->beginTransaction();
    $i=$pdo->prepare('SELECT id FROM invoices WHERE visit_id=?');$i->execute([$vid]);$iid=$i->fetchColumn();
    if(!$iid){$pdo->prepare('INSERT INTO invoices(patient_id,visit_id,total,created_by) VALUES(?,?,0,?)')->execute([$v['patient_id'],$vid,user()['id']]);$iid=$pdo->lastInsertId();}
    $h=$pdo->prepare('SELECT CONCAT(source,ref_id) FROM invoice_items WHERE invoice_id=? AND source IS NOT NULL');$h->execute([$iid]);$have=$h->fetchAll(PDO::FETCH_COLUMN);
    $add=function($src,$ref,$d,$a)use($pdo,$iid,&$have){if($a<=0||in_array($src.$ref,$have))return;$pdo->prepare('INSERT INTO invoice_items(invoice_id,description,amount,source,ref_id) VALUES(?,?,?,?,?)')->execute([$iid,$d,$a,$src,$ref]);$have[]=$src.$ref;};
    $add('fee',$vid,'Consultation fee',$fee);
    foreach($pdo->query("SELECT o.id,t.name,t.price FROM lab_orders o JOIN lab_tests t ON t.id=o.test_id WHERE o.visit_id=$vid AND o.status='done'") as $o)$add('lab',$o['id'],'Lab: '.$o['name'],$o['price']);
    foreach($pdo->query("SELECT o.id,e.name,e.price FROM imaging_orders o JOIN imaging_exams e ON e.id=o.exam_id WHERE o.visit_id=$vid AND o.status='reported'") as $o)$add('img',$o['id'],'Imaging: '.$o['name'],$o['price']);
    foreach($pdo->query("SELECT o.id,c.name,c.price FROM procedure_orders o JOIN procedures_catalog c ON c.id=o.proc_id WHERE o.visit_id=$vid AND o.status='done'") as $o)$add('proc',$o['id'],'Procedure: '.$o['name'],$o['price']);
    foreach($pdo->query("SELECT x.id,x.qty,d.name,d.price FROM prescriptions x JOIN drugs d ON d.id=x.drug_id WHERE x.visit_id=$vid AND x.status='dispensed'") as $x)$add('rx',$x['id'],"Drug: {$x['name']} x{$x['qty']}",$x['qty']*$x['price']);
    foreach($pdo->query("SELECT a.id,a.admitted_at,a.discharged_at,w.daily_rate FROM admissions a JOIN beds b ON b.id=a.bed_id JOIN wards w ON w.id=b.ward_id WHERE a.visit_id=$vid AND a.discharged_at IS NOT NULL") as $a){$d=max(1,(int)ceil((strtotime($a['discharged_at'])-strtotime($a['admitted_at']))/86400));$add('bed',$a['id'],"Bed: $d day(s)",$d*$a['daily_rate']);}
    $t=(float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM invoice_items WHERE invoice_id=$iid")->fetchColumn();
    $pdo->prepare("UPDATE invoices SET total=?,status=IF(paid>0 AND paid>=?,'paid',IF(paid>0,'partial','unpaid')) WHERE id=?")->execute([$t,$t,$iid]);
    $pdo->commit();audit("billed visit $vid");$msg="Invoice #$iid updated. Total KES ".number_format($t,2).'. Record payment on the Billing page.';}
}
head('Visit billing');if($msg)echo "<p class=ok>".e($msg)."</p>";?>
<div class=card><p>Builds the invoice from the consultation fee, finished lab tests, dispensed drugs and bed days. Press again after new results to add only the new items.</p>
<table><tr><th>Visit<th>Patient<th>Stage<th>Invoice<th></tr>
<?php foreach($pdo->query("SELECT v.id,v.status,p.patient_no,p.first_name,p.last_name,i.total,i.paid FROM visits v JOIN patients p ON p.id=v.patient_id LEFT JOIN invoices i ON i.visit_id=v.id WHERE v.status<>'registered' AND v.created_at>=NOW()-INTERVAL 7 DAY ORDER BY v.id DESC") as $v):?>
<tr><td>#<?=$v['id']?><td><?=e($v['patient_no'].' '.$v['first_name'].' '.$v['last_name'])?><td><?=e($v['status'])?><td><?=$v['total']!==null?number_format($v['total'],2).' (paid '.number_format($v['paid'],2).')':'-'?>
<td><form method=post style="display:flex;gap:6px"><?=field()?><input type=hidden name=visit value=<?=$v['id']?>><input name=fee type=number step=0.01 min=0 value=500 style="width:90px" title="Consultation fee"><button style="margin:0">Build invoice</button></form></tr><?php endforeach;?></table></div><?php foot();
