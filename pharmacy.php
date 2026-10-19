<?php require 'config.php'; need(['pharmacist']); check();$msg=$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['drug_name'])){try{$pdo->prepare('INSERT INTO drugs(name,unit,stock,reorder_level,price) VALUES(?,?,?,?,?)')->execute([trim($_POST['drug_name']),$_POST['unit'],max(0,(int)$_POST['stock']),max(0,(int)$_POST['reorder']),max(0,(float)$_POST['price'])]);$msg='Drug added.';}catch(PDOException $x){$err='That drug already exists. Use restock.';}}
  elseif(isset($_POST['restock'])){$pdo->prepare('UPDATE drugs SET stock=stock+? WHERE id=?')->execute([max(0,(int)$_POST['add']),(int)$_POST['restock']]);audit('restock drug '.(int)$_POST['restock']);$msg='Stock updated.';}
  elseif(isset($_POST['dispense'])){$vid=(int)$_POST['dispense'];$ps=$pdo->prepare("SELECT x.*,d.name FROM prescriptions x JOIN drugs d ON d.id=x.drug_id WHERE x.visit_id=? AND x.status='pending'");$ps->execute([$vid]);$n=0;$short=[];
    foreach($ps->fetchAll() as $x){$u=$pdo->prepare('UPDATE drugs SET stock=stock-? WHERE id=? AND stock>=?');$u->execute([$x['qty'],$x['drug_id'],$x['qty']]);
      if($u->rowCount()){$pdo->prepare("UPDATE prescriptions SET status='dispensed',dispensed_by=? WHERE id=?")->execute([user()['id'],$x['id']]);$n++;}else $short[]=$x['name'];}
    if($n){advance($vid,'billing');audit("dispensed $n items visit $vid");}
    if($short)$err='Not enough stock for: '.implode(', ',$short).'. Restock, then dispense again.';
    elseif($n){header('Location: slip.php?kind=pharmacy&visit='.$vid.'&back=pharmacy.php');exit;}}
}
head('Pharmacy');if($msg)echo '<p class=ok>'.e($msg).'</p>';if($err)echo '<p class=err>'.e($err).'</p>';
$rows=$pdo->query("SELECT x.*,d.name dn,d.stock,p.patient_no,p.first_name,p.last_name,v.reason,u.name doc FROM prescriptions x JOIN drugs d ON d.id=x.drug_id JOIN visits v ON v.id=x.visit_id JOIN patients p ON p.id=v.patient_id LEFT JOIN users u ON u.id=x.prescribed_by WHERE x.status='pending' ORDER BY x.visit_id,x.id")->fetchAll();$g=[];foreach($rows as $x)$g[$x['visit_id']][]=$x;
echo '<h3>Patients waiting for medicine</h3>';if(!$g)echo '<div class=card><p>No patient is waiting. Prescriptions appear here when the doctor saves a consultation with drugs.</p></div>';
foreach($g as $vid=>$L){$f=$L[0];echo '<div class=card><b>'.e($f['patient_no'].' '.$f['first_name'].' '.$f['last_name']).'</b> | Visit #'.$vid.' | Prescribed by '.e($f['doc']).'<br><small>Reason: '.e($f['reason']).'</small><table><tr><th>Drug<th>Dose<th>Duration<th>Qty<th>In stock</tr>';
  foreach($L as $x)echo '<tr><td>'.e($x['dn']).'<td>'.e($x['dose']).'<td>'.e($x['duration']).'<td>'.$x['qty'].'<td>'.($x['stock']<$x['qty']?'<b class=err>'.$x['stock'].' (short)</b>':$x['stock']);
  echo '</table><form method=post>'.field().'<button name=dispense value='.$vid.'>Dispense all and print receipt</button></form></div>';}?>
<div class=card><h3>Drug stock</h3><table><tr><th>Drug<th>Stock<th>Price<th>Restock</tr>
<?php foreach($pdo->query('SELECT * FROM drugs ORDER BY name') as $d)echo '<tr><td>'.e($d['name']).'<td>'.$d['stock'].($d['stock']<=$d['reorder_level']?' <b class=err>low</b>':'').'<td>'.number_format($d['price'],2).'<td><form method=post style="display:flex;gap:6px">'.field().'<input type=hidden name=restock value='.$d['id'].'><input name=add type=number min=1 style="width:90px" required><button style="margin:0">Add</button></form>';?></table></div>
<div class=card><h3>Add new drug</h3><form method=post class=g><?=field()?><div><label>Name</label><input name=drug_name required></div><div><label>Unit</label><input name=unit placeholder="tablet"></div><div><label>Opening stock</label><input name=stock type=number min=0 value=0></div><div><label>Low-stock level</label><input name=reorder type=number min=0 value=10></div><div><label>Price per unit (KES)</label><input name=price type=number step=0.01 min=0 value=0></div><div><button>Add drug</button></div></form></div><?php foot();
