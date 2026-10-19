<?php require 'config.php'; need(['receptionist','nurse','doctor','records','cashier']); check();
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['visit'])){$pdo->prepare('INSERT INTO visits(patient_id) VALUES(?)')->execute([(int)$_POST['visit']]);start_fee($pdo->lastInsertId(),(int)$_POST['visit']);audit('visit for patient '.(int)$_POST['visit']);$msg='Visit started. A consultation fee invoice was created: the patient must pay at billing before seeing the doctor.';}
  else{$pdo->prepare('INSERT INTO patients(first_name,last_name,gender,dob,id_number,phone,address,next_of_kin,nok_phone) VALUES(?,?,?,?,?,?,?,?,?)')
    ->execute([trim($_POST['first_name']),trim($_POST['last_name']),$_POST['gender'],$_POST['dob'],$_POST['id_number'],$_POST['phone'],$_POST['address'],$_POST['next_of_kin'],$_POST['nok_phone']]);
    $id=$pdo->lastInsertId();$no='P'.date('y').sprintf('%05d',$id);$pdo->prepare('UPDATE patients SET patient_no=? WHERE id=?')->execute([$no,$id]);
    audit("registered $no");header('Location: visit_new.php?patient='.$id);exit;}
}
$q=trim($_GET['q']??'');$s=$pdo->prepare("SELECT * FROM patients WHERE ?='' OR first_name LIKE ? OR last_name LIKE ? OR patient_no LIKE ? OR id_number LIKE ? OR phone LIKE ? ORDER BY id DESC LIMIT 50");
$l="%$q%";$s->execute([$q,$l,$l,$l,$l,$l]);
head('Patients');if($msg)echo "<p class=ok>".e($msg)."</p>";?>
<div class=card><h3>Register patient</h3><form method=post class=g><?=field()?>
<div><label>First name</label><input name=first_name required></div><div><label>Last name</label><input name=last_name required></div>
<div><label>Gender</label><select name=gender><option>Male<option>Female<option>Other</select></div><div><label>Date of birth</label><input name=dob type=date max="<?=date('Y-m-d')?>" required></div>
<div><label>ID / Birth cert no.</label><input name=id_number></div><div><label>Phone</label><input name=phone></div><div><label>Address</label><input name=address></div>
<div><label>Next of kin</label><input name=next_of_kin></div><div><label>Next of kin phone</label><input name=nok_phone></div>
<div><button>Register patient</button></div></form></div>
<div class=card><form><input name=q value="<?=e($q)?>" placeholder="Search by name, patient no, ID or phone"></form>
<table><tr><th>No.<th>Name<th>Gender<th>DOB<th>Phone<th></tr>
<?php foreach($s as $p):?><tr><td><?=e($p['patient_no'])?><td><a href="patient.php?id=<?=$p['id']?>"><?=e($p['first_name'].' '.$p['last_name'])?></a><td><?=e($p['gender'])?><td><?=e($p['dob'])?><td><?=e($p['phone'])?>
<td><a href="visit_new.php?patient=<?=$p['id']?>" style="background:var(--brand);color:#fff;padding:5px 10px;border-radius:4px;text-decoration:none">Start visit</a></tr><?php endforeach;?></table></div><?php foot();
