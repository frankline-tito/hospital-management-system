<?php require 'config.php'; need(['records','doctor']);
$a=$_GET['from']??date('Y-m-01');$b=$_GET['to']??date('Y-m-d');$b2=$b.' 23:59:59';
$q=function($sql,$p=[])use($pdo){$s=$pdo->prepare($sql);$s->execute($p);return $s->fetchAll();};
$dx=$q("SELECT c.diagnosis d,SUM(TIMESTAMPDIFF(YEAR,p.dob,c.created_at)<5) u5,SUM(TIMESTAMPDIFF(YEAR,p.dob,c.created_at)>=5) o5,COUNT(*) n FROM consultations c JOIN visits v ON v.id=c.visit_id JOIN patients p ON p.id=v.patient_id WHERE c.created_at BETWEEN ? AND ? GROUP BY c.diagnosis ORDER BY n DESC LIMIT 15",[$a,$b2]);
if(isset($_GET['csv'])){header('Content-Type: text/csv');header('Content-Disposition: attachment; filename=diagnoses.csv');$o=fopen('php://output','w');fputcsv($o,['Diagnosis','Under 5','5 and above','Total']);foreach($dx as $r)fputcsv($o,[$r['d'],$r['u5'],$r['o5'],$r['n']]);exit;}
$opd=$q("SELECT SUM(g<5) u5,SUM(g>=5) o5,COUNT(*) n FROM (SELECT TIMESTAMPDIFF(YEAR,p.dob,v.created_at) g FROM visits v JOIN patients p ON p.id=v.patient_id WHERE v.created_at BETWEEN ? AND ?) x",[$a,$b2])[0];
$pay=$q("SELECT method,SUM(amount) s,COUNT(*) n FROM payments WHERE created_at BETWEEN ? AND ? GROUP BY method",[$a,$b2]);
$lab=$q("SELECT t.name,COUNT(*) n,SUM(o.abnormal) ab FROM lab_orders o JOIN lab_tests t ON t.id=o.test_id WHERE o.status='done' AND o.done_at BETWEEN ? AND ? GROUP BY t.name ORDER BY n DESC",[$a,$b2]);
$adm=$q("SELECT (SELECT COUNT(*) FROM admissions WHERE admitted_at BETWEEN ? AND ?) a,(SELECT COUNT(*) FROM admissions WHERE discharged_at BETWEEN ? AND ?) d,(SELECT COUNT(*) FROM referrals WHERE created_at BETWEEN ? AND ? AND direction='out') ro,(SELECT COUNT(*) FROM telemedicine_requests WHERE created_at BETWEEN ? AND ?) tm",[$a,$b2,$a,$b2,$a,$b2,$a,$b2])[0];
$ch=$q("SELECT COUNT(*) n,COALESCE(SUM(members),0) m,COALESCE(SUM(pregnant),0) p,COALESCE(SUM(under5),0) u,COALESCE(SUM(immunized),0) i,COALESCE(SUM(referral_needed),0) r FROM chp_reports WHERE visit_date BETWEEN ? AND ?",[$a,$b])[0];
head('Reports (MOH 705-style summary)');?>
<div class=card><form style="display:flex;gap:8px;align-items:end"><div><label>From</label><input type=date name=from value="<?=e($a)?>"></div><div><label>To</label><input type=date name=to value="<?=e($b)?>"></div><button style="margin:0">Show</button><a href="?from=<?=e($a)?>&to=<?=e($b)?>&csv=1">Download diagnoses CSV</a></form></div>
<div class=g><div class=card><b><?=(int)$opd['n']?></b> OPD visits<br>Under 5: <?=(int)$opd['u5']?> | 5 and above: <?=(int)$opd['o5']?></div>
<div class=card>Admissions <b><?=$adm['a']?></b> | Discharges <b><?=$adm['d']?></b><br>Referrals out <b><?=$adm['ro']?></b> | Telemedicine requests <b><?=$adm['tm']?></b></div>
<div class=card>Field visits <b><?=$ch['n']?></b> | Members <?=$ch['m']?><br>Pregnant <?=$ch['p']?> | Under 5 <?=$ch['u']?> | Immunized <?=$ch['i']?><br>Need referral <?=$ch['r']?></div></div>
<div class=card><h3>Top diagnoses</h3><table><tr><th>Diagnosis<th>Under 5<th>5 and above<th>Total</tr><?php foreach($dx as $r)echo '<tr><td>'.e($r['d']).'<td>'.$r['u5'].'<td>'.$r['o5'].'<td>'.$r['n'];?></table></div>
<div class=g><div class=card><h3>Lab tests done</h3><table><tr><th>Test<th>Done<th>Abnormal</tr><?php foreach($lab as $r)echo '<tr><td>'.e($r['name']).'<td>'.$r['n'].'<td>'.$r['ab'];?></table></div>
<div class=card><h3>Revenue by method</h3><table><tr><th>Method<th>Payments<th>KES</tr><?php foreach($pay as $r)echo '<tr><td>'.e($r['method']).'<td>'.$r['n'].'<td>'.number_format($r['s'],2);?></table></div></div><?php foot();
