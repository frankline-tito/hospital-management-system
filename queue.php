<?php require 'config.php'; need();head('Patient queue');
$r=user()['role'];$all=isset($_GET['all']);$ctx=null;foreach(depts() as $d)if($d['slug']==($_SESSION['ctx']??''))$ctx=$d;
echo '<form action=trace.php style="display:flex;gap:8px;margin-bottom:12px"><input name=q placeholder="Find a patient by ID, patient no, name or phone"><button style="margin:0">Find</button></form>';
$w="v.status<>'done' AND DATE(v.created_at)=CURDATE()";$a=[];$filt=$ctx&&!$all&&in_array($r,['doctor','nurse']);if($filt){$w.=' AND (v.dept_id=? OR v.dept_id IS NULL)';$a[]=$ctx['id'];}
$s=$pdo->prepare("SELECT v.*,p.patient_no,p.first_name,p.last_name,d.name dn,(SELECT id FROM invoices WHERE visit_id=v.id ORDER BY id LIMIT 1) inv FROM visits v JOIN patients p ON p.id=v.patient_id LEFT JOIN departments d ON d.id=v.dept_id WHERE $w ORDER BY v.id");$s->execute($a);$rows=$s->fetchAll();
if($filt)echo '<p>Showing patients for <b>'.e($ctx['name']).'</b>. <a href="queue.php?all=1">Show all service points</a></p>';
echo '<div class=card>';
if(!$rows)echo '<p>No active visits today. Start a visit from the Records page.</p>';
else{echo '<table><tr><th>Visit<th>Patient<th>Reason<th>Service point<th>Stage<th>Fee<th>Actions</tr>';
 foreach($rows as $v){$paid=fee_paid($v['id']);$id=$v['id'];$st=$v['status'];
  echo '<tr><td>#'.$id.'<td>'.e($v['patient_no'].' '.$v['first_name'].' '.$v['last_name']).'<td>'.e($v['reason']).'<td>'.e($v['dn']?:'-').'<td>'.e($st).'<td>'.($paid?'Paid':'<b class=err>Unpaid</b>').'<td>';
  if(!$paid&&$v['inv']&&in_array($r,['cashier','receptionist','admin']))echo "<a href='pay.php?invoice={$v['inv']}'>Take payment</a> ";
  if(in_array($r,['nurse','admin'])&&in_array($st,['registered','triage']))echo "<a href='triage.php?visit=$id'>Take vitals</a> ";
  if(in_array($r,['doctor','admin'])&&in_array($st,['consultation','lab','imaging','procedure','pharmacy'])){if($paid)echo "<a href='consult.php?visit=$id'>Consult</a> <a href='orders.php?visit=$id'>Orders and results</a> <a href='notes.php?visit=$id'>Notes</a>";else echo '<b class=err>Fee not paid</b>';}
  if($r=='radiographer'&&$st=='imaging')echo "<a href='imaging.php'>Report</a>";if(in_array($r,['nurse','doctor'])&&$st=='procedure')echo "<a href='procedures.php'>Do procedure</a> ";if($r=='lab'&&$st=='lab')echo "<a href='lab.php'>Enter results</a>";
  if($r=='pharmacist'&&$st=='pharmacy')echo "<a href='pharmacy.php'>Dispense</a>";}
 echo '</table>';}
echo '</div>';foot();
