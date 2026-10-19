<?php require 'config.php'; need(['doctor']); check();
$id=(int)($_GET['visit']??0);$s=$pdo->prepare('SELECT v.*,p.first_name,p.last_name,p.patient_no FROM visits v JOIN patients p ON p.id=v.patient_id WHERE v.id=?');$s->execute([$id]);$v=$s->fetch();if(!$v)die('Visit not found.');fee_block($id);
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['tests'])){foreach($_POST['tests'] as $t)$pdo->prepare('INSERT INTO lab_orders(visit_id,test_id,ordered_by) VALUES(?,?,?)')->execute([$id,(int)$t,user()['id']]);}
  if(!empty($_POST['drug'])){$q=(int)$_POST['qty'];if($q<1)$err='Enter a quantity of 1 or more.';else $pdo->prepare('INSERT INTO prescriptions(visit_id,drug_id,dose,duration,qty,prescribed_by) VALUES(?,?,?,?,?,?)')->execute([$id,(int)$_POST['drug'],$_POST['dose'],$_POST['duration'],$q,user()['id']]);}
  order_extra($id);if(!$err){advance($id,'consultation');audit("orders visit $id");header("Location: orders.php?visit=$id");exit;}
}
head('Orders: '.$v['first_name'].' '.$v['last_name'].' ('.$v['patient_no'].')');if($err)echo "<p class=err>".e($err)."</p>";
$tests=$pdo->query('SELECT * FROM lab_tests ORDER BY name')->fetchAll();$drugs=$pdo->query('SELECT * FROM drugs ORDER BY name')->fetchAll();?>
<div class=g><div class=card><h3>Order lab tests</h3><?php if(!$tests):echo '<p>No tests in the catalogue yet. Ask the lab to add them.</p>';else:?>
<form method=post><?=field()?><?php foreach($tests as $t)echo '<label style="font-weight:400"><input type=checkbox name="tests[]" value='.$t['id'].' style="width:auto"> '.e($t['name']).'</label>';?><button>Order tests</button></form><?php endif;?></div>
<div class=card><h3>Prescribe</h3><?php if(!$drugs):echo '<p>No drugs in stock list yet. Ask the pharmacy to add them.</p>';else:?>
<form method=post><?=field()?><label>Drug</label><select name=drug><?php foreach($drugs as $d)echo '<option value='.$d['id'].'>'.e($d['name']).' (stock '.$d['stock'].')';?></select>
<label>Dose (e.g. 1 tab 3 times a day)</label><input name=dose required><label>Duration</label><input name=duration placeholder="5 days"><label>Quantity to dispense</label><input name=qty type=number min=1 required><button>Add prescription</button></form><?php endif;?></div></div>
<div class=card><h3>Lab orders and results</h3><table><tr><th>Test<th>Status<th>Result</tr>
<?php foreach($pdo->query("SELECT o.*,t.name FROM lab_orders o JOIN lab_tests t ON t.id=o.test_id WHERE o.visit_id=$id") as $o)echo '<tr><td>'.e($o['name']).'<td>'.$o['status'].'<td>'.e($o['result']).($o['abnormal']?' <b class=err>(abnormal)</b>':'');?></table></div>
<div class=card><h3>Prescriptions</h3><table><tr><th>Drug<th>Dose<th>Duration<th>Qty<th>Status</tr>
<?php foreach($pdo->query("SELECT p.*,d.name FROM prescriptions p JOIN drugs d ON d.id=p.drug_id WHERE p.visit_id=$id") as $p)echo '<tr><td>'.e($p['name']).'<td>'.e($p['dose']).'<td>'.e($p['duration']).'<td>'.$p['qty'].'<td>'.$p['status'];?></table></div><div class=card><h3>Order imaging or a procedure</h3><form method=post><?=field()?><?=extra_form()?><button>Send orders</button></form></div>
<div class=card><h3>Imaging reports</h3><?php foreach($pdo->query("SELECT o.*,e.name FROM imaging_orders o JOIN imaging_exams e ON e.id=o.exam_id WHERE o.visit_id=$id") as $o)echo '<div><b>'.e($o['name']).'</b> ('.e($o['status']).')'.($o['status']=='reported'?'<br>Findings: '.nl2br(e($o['findings'])).'<br><b>Impression:</b> '.nl2br(e($o['impression'])):'').'</div>';?></div>
<div class=card><h3>Procedures</h3><?php foreach($pdo->query("SELECT o.*,c.name FROM procedure_orders o JOIN procedures_catalog c ON c.id=o.proc_id WHERE o.visit_id=$id") as $o)echo '<div><b>'.e($o['name']).'</b> ('.e($o['status']).')'.($o['status']=='done'?'<br>Outcome: '.nl2br(e($o['outcome'])).' '.e($o['complications']):'').'</div>';?></div>
<?php foot();
