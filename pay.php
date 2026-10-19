<?php require 'config.php'; need(['cashier','receptionist']); check();
$iid=(int)($_GET['invoice']??$_POST['invoice']??0);$i=$pdo->prepare('SELECT i.*,p.first_name,p.last_name,p.patient_no FROM invoices i JOIN patients p ON p.id=i.patient_id WHERE i.id=?');$i->execute([$iid]);$i=$i->fetch();if(!$i)die('Invoice not found.');
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){$m=$_POST['method'];$amt=(float)$_POST['amount'];$bal=$i['total']-$i['paid'];
  $mp=strtoupper(trim($_POST['mpesa_ref']??''));$bn=trim($_POST['bank_name']??'');$br=strtoupper(trim($_POST['bank_ref']??''));
  if(!in_array($m,['cash','mpesa','bank'],true))$err='Choose a payment method.';
  elseif($amt<=0||$amt>$bal+0.001)$err='Amount must be above 0 and not more than the balance ('.number_format($bal,2).').';
  elseif($m=='mpesa'&&!preg_match('/^[A-Z0-9]{10}$/',$mp))$err='Enter the 10-character M-Pesa code.';
  elseif($m=='bank'&&($bn===''||$br===''))$err='Enter the bank name and bank reference.';
  else try{$pdo->beginTransaction();
    $pdo->prepare('INSERT INTO payments(invoice_id,method,amount,mpesa_ref,bank_name,bank_ref,received_by) VALUES(?,?,?,?,?,?,?)')->execute([$iid,$m,$amt,$m=='mpesa'?$mp:null,$m=='bank'?$bn:null,$m=='bank'?$br:null,user()['id']]);$pay=$pdo->lastInsertId();
    $paid=$i['paid']+$amt;$st=$paid>=$i['total']-0.001?'paid':'partial';$pdo->prepare('UPDATE invoices SET paid=?,status=? WHERE id=?')->execute([$paid,$st,$iid]);
    if($st=='paid'&&$i['visit_id'])$pdo->prepare("UPDATE visits SET status='done' WHERE id=? AND status='billing'")->execute([$i['visit_id']]);
    $pdo->commit();audit("payment $m invoice $iid");header('Location: receipt.php?payment='.$pay.'&auto=1');exit;
  }catch(PDOException $x){$pdo->rollBack();$err=$x->errorInfo[1]==1062?'That reference was already used on another payment.':'Could not save the payment.';}}
head('Take payment: '.$i['first_name'].' '.$i['last_name'].' ('.$i['patient_no'].')');if($err)echo '<p class=err>'.e($err).'</p>';
$bal=$i['total']-$i['paid'];$its=$pdo->prepare('SELECT * FROM invoice_items WHERE invoice_id=?');$its->execute([$iid]);?>
<div class=card><h3>Invoice #<?=$iid?></h3><table><?php foreach($its as $t)echo '<tr><td>'.e($t['description']).'<td class=r>'.number_format($t['amount'],2);?><tr><th>Total<th class=r><?=number_format($i['total'],2)?><tr><td>Paid<td class=r><?=number_format($i['paid'],2)?><tr><th>Balance<th class=r><?=number_format($bal,2)?></table>
<?php foreach($pdo->query("SELECT id,amount,method FROM payments WHERE invoice_id=$iid") as $y)echo '<div>Paid '.number_format($y['amount'],2).' ('.e($y['method']).') <a href="receipt.php?payment='.$y['id'].'" target=_blank>Receipt</a></div>';?></div>
<?php if($bal>0.001):?><div class=card style="max-width:520px"><h3>Record payment</h3><form method=post><?=field()?><input type=hidden name=invoice value=<?=$iid?>>
<label>Amount (KES)</label><input name=amount type=number step=0.01 value="<?=$bal?>" required><label>Method</label><select name=method id=mt><option value=cash>Cash<option value=mpesa>M-Pesa<option value=bank>Bank</select>
<div id=mpb style="display:none"><label>M-Pesa code</label><input name=mpesa_ref maxlength=10></div><div id=bkb style="display:none"><label>Bank name</label><input name=bank_name><label>Bank reference</label><input name=bank_ref></div>
<button>Save payment and print receipt</button></form></div>
<script>var mt=document.getElementById("mt");mt.onchange=function(){document.getElementById("mpb").style.display=mt.value=="mpesa"?"":"none";document.getElementById("bkb").style.display=mt.value=="bank"?"":"none"}</script><?php endif;foot();
