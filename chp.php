<?php require 'config.php'; need(['chp','nurse','doctor','records']); check();$err='';$chp=user()['role']=='chp';
if($_SERVER['REQUEST_METHOD']==='POST'&&$chp){
  $m=(int)$_POST['members'];$p=(int)$_POST['pregnant'];$u=(int)$_POST['under5'];$i=(int)$_POST['immunized'];
  if($m<1||$p<0||$u<0||$i<0||$p>$m||$u>$m||$i>$u)$err='Check the numbers: members at least 1, and pregnant or under-5 cannot exceed members, immunized cannot exceed under-5.';
  else{$pdo->prepare('INSERT INTO chp_reports(facility_id,chp_id,household_head,village,visit_date,members,pregnant,under5,immunized,danger_signs,referral_needed) VALUES(?,?,?,?,?,?,?,?,?,?,?)')
    ->execute([fscope(),user()['id'],trim($_POST['head']),trim($_POST['village']),$_POST['date'],$m,$p,$u,$i,trim($_POST['danger']),isset($_POST['ref'])?1:0]);audit('chp report');header('Location: chp.php');exit;}}
head('Community health field reports');if($err)echo "<p class=err>".e($err)."</p>";if($chp):?>
<div class=card><h3>New household visit</h3><form method=post class=g><?=field()?><div><label>Household head</label><input name=head required></div><div><label>Village</label><input name=village></div>
<div><label>Visit date</label><input type=date name=date max="<?=date('Y-m-d')?>" value="<?=date('Y-m-d')?>" required></div><div><label>Household members</label><input type=number name=members min=1 required></div>
<div><label>Pregnant women</label><input type=number name=pregnant min=0 value=0></div><div><label>Children under 5</label><input type=number name=under5 min=0 value=0></div><div><label>Under-5s fully immunized</label><input type=number name=immunized min=0 value=0></div>
<div style="grid-column:1/-1"><label>Danger signs seen</label><textarea name=danger></textarea><label style="font-weight:400"><input type=checkbox name=ref style="width:auto"> Referral to facility needed</label><button>Submit report</button></div></form></div><?php endif;?>
<div class=card><table><tr><th>Date<th>Household<th>Village<th>Members<th>Preg.<th>U5<th>Immun.<th>Danger signs<th>Referral</tr>
<?php $w=$chp?' WHERE r.chp_id='.(int)user()['id']:(fscope()?' WHERE r.facility_id='.fscope():'');
foreach($pdo->query('SELECT r.* FROM chp_reports r'.$w.' ORDER BY r.id DESC LIMIT 100') as $r)echo '<tr><td>'.$r['visit_date'].'<td>'.e($r['household_head']).'<td>'.e($r['village']).'<td>'.$r['members'].'<td>'.$r['pregnant'].'<td>'.$r['under5'].'<td>'.$r['immunized'].'<td>'.e($r['danger_signs']).'<td>'.($r['referral_needed']?'<b class=err>Yes</b>':'No');?></table></div><?php foot();
