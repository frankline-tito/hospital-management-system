<?php require 'config.php'; need(['cashier','receptionist']); check();
$msg=$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['new'])){
    $items=[];foreach($_POST['desc'] as $i=>$d){$a=(float)$_POST['amt'][$i];if(trim($d)!==''&&$a>0)$items[]=[trim($d),$a];}
    if(!$items)$err='Add at least one item with an amount.';
    else{$pdo->beginTransaction();$pdo->prepare('INSERT INTO invoices(patient_id,total,created_by) VALUES(?,?,?)')->execute([(int)$_POST['patient'],array_sum(array_column($items,1)),user()['id']]);
      $id=$pdo->lastInsertId();foreach($items as $it)$pdo->prepare('INSERT INTO invoice_items(invoice_id,description,amount) VALUES(?,?,?)')->execute([$id,$it[0],$it[1]]);
      $pdo->commit();audit("invoice $id");$msg="Invoice #$id created.";}
  }else{
    $m=$_POST['method'];$amt=(float)$_POST['amount'];$inv=$pdo->prepare('SELECT * FROM invoices WHERE id=?');$inv->execute([(int)$_POST['invoice']]);$inv=$inv->fetch();
    $mp=strtoupper(trim($_POST['mpesa_ref']??''));$bn=trim($_POST['bank_name']??'');$br=strtoupper(trim($_POST['bank_ref']??''));
    if(!$inv)$err='Invoice not found.';
    elseif($amt<=0||$amt>$inv['total']-$inv['paid']+0.001)$err='Amount must be above 0 and not more than the balance ('.number_format($inv['total']-$inv['paid'],2).').';
    elseif($m=='mpesa'&&!preg_match('/^[A-Z0-9]{10}$/',$mp))$err='Enter the 10-character M-Pesa reference code (e.g. SHK3L9X2QP).';
    elseif($m=='bank'&&($bn===''||$br===''))$err='Enter the bank name and the bank reference.';
    else try{
      $pdo->beginTransaction();
      $pdo->prepare('INSERT INTO payments(invoice_id,method,amount,mpesa_ref,bank_name,bank_ref,received_by) VALUES(?,?,?,?,?,?,?)')
        ->execute([$inv['id'],$m,$amt,$m=='mpesa'?$mp:null,$m=='bank'?$bn:null,$m=='bank'?$br:null,user()['id']]);
      $paid=$inv['paid']+$amt;$st=$paid>=$inv['total']-0.001?'paid':'partial';
      $pdo->prepare('UPDATE invoices SET paid=?,status=? WHERE id=?')->execute([$paid,$st,$inv['id']]);
      if($st=='paid'&&$inv['visit_id'])$pdo->prepare("UPDATE visits SET status='done' WHERE id=? AND status='billing'")->execute([$inv['visit_id']]);$pdo->commit();audit("payment $m on invoice {$inv['id']}");$msg='Payment recorded.';
    }catch(PDOException $x){$pdo->rollBack();$err=$x->errorInfo[1]==1062?'That reference code was already used on another payment.':'Could not save payment.';}
  }
}
head('Billing');if($msg)echo "<p class=ok>".e($msg)."</p>";if($err)echo "<p class=err>".e($err)."</p>";
$pts=$pdo->query('SELECT id,patient_no,first_name,last_name FROM patients ORDER BY id DESC LIMIT 200')->fetchAll();?>
<div class=card><h3>New invoice</h3><form method=post><?=field()?><input type=hidden name=new value=1>
<label>Patient</label><select name=patient><?php foreach($pts as $p)echo '<option value='.$p['id'].'>'.e($p['patient_no'].' - '.$p['first_name'].' '.$p['last_name']).'</option>';?></select>
<?php for($i=0;$i<4;$i++)echo '<div class=g><div><input name="desc[]" placeholder="Service / item"></div><div><input name="amt[]" type=number step=0.01 min=0 placeholder="Amount (KES)"></div></div>';?>
<button>Create invoice</button></form></div>
<div class=card><h3>Invoices</h3><table><tr><th>#<th>Patient<th>Total<th>Paid<th>Status<th>Record payment</tr>
<?php foreach($pdo->query('SELECT i.*,p.first_name,p.last_name FROM invoices i JOIN patients p ON p.id=i.patient_id ORDER BY i.id DESC LIMIT 50') as $i):?>
<tr><td><?=$i['id']?><td><?=e($i['first_name'].' '.$i['last_name'])?><td><?=number_format($i['total'],2)?><td><?=number_format($i['paid'],2)?><td><?=$i['status']?>
<td><?php if($i['status']!='paid'):?><form method=post><?=field()?><input type=hidden name=invoice value=<?=$i['id']?>>
<input name=amount type=number step=0.01 placeholder="Amount" required>
<select name=method onchange="var v=this.value;this.form.querySelectorAll('.mp').forEach(x=>x.style.display=v=='mpesa'?'':'none');this.form.querySelectorAll('.bk').forEach(x=>x.style.display=v=='bank'?'':'none')"><option value=cash>Cash<option value=mpesa>M-Pesa<option value=bank>Bank</select>
<input class=mp style="display:none" name=mpesa_ref placeholder="M-Pesa code" maxlength=10>
<input class=bk style="display:none" name=bank_name placeholder="Bank name"><input class=bk style="display:none" name=bank_ref placeholder="Bank reference">
<button style="margin:4px 0 0">Save</button></form><?php endif;?></tr><?php endforeach;?></table></div><?php foot();
