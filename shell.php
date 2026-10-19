<?php
function modules(){return [
['home','Home','🏠','Live KPIs for the whole system, across all departments.','index.php',null],
['trace','Patient Tracing & Booking','🧭','Trace a patient\'s journey, see where they are now, and manage bookings.','trace.php',['receptionist','records','nurse','doctor','cashier']],
['records','Records','🗂️','Capture client details and onboard them to the right service points.','patients.php',['receptionist','records','nurse','doctor']],
['billing','Billing','💳','Invoices and payments at the cash office, insurance/SHIF desk and social services.','billing.php',['cashier','receptionist']],
['triage','Triage','🩺','Vitals and screening, then queue the patient for the right consultation.','queue.php',['nurse','doctor','receptionist','records','lab','pharmacist','cashier']],
['consult','Consultation','⚕️','Examinations, investigations and plans, in person or by telemedicine.','queue.php',['doctor']],
['immun','Immunization & Nutrition','💉','Scheduled vaccines, vitamins, deworming and nutrition checks.','immunization.php',['nurse','doctor','records']],
['lab','Laboratory','🧪','Capture results for ordered tests and relay them to clinicians.','lab.php',['lab']],
['proc','Procedures','🩹','Outcomes of ordered procedures, relayed to clinicians.','procedures.php',['nurse','doctor']],
['img','Imaging','🩻','Ultrasound and X-ray reports, relayed to clinicians.','imaging.php',['radiographer','doctor','nurse']],
['pharm','Pharmacy','💊','Dispense prescribed medicines and manage drug stock.','pharmacy.php',['pharmacist']],
['inv','Inventory','📦','Supplies and equipment, requisitions and issuances.','inventory.php',['pharmacist','nurse','doctor','lab','radiographer']],
['adm','Admissions','🛏️','Ward beds, discharge and treatment records.','inpatient.php',['nurse','doctor','receptionist']],
['nbu','NBU (New Born Unit)','👶','Newborn admissions, daily charts, feeding, phototherapy and discharge.','nbu.php',['nurse','doctor']],
['access','Access Control','🔐','Create accounts, revoke access and assign privileges.','users.php',[]],
['seed','Test Data','🧫','Add or remove sample data for testing.','seed.php',[]],
['setup','Master Setup','⚙️','Facilities and service points.','facilities.php',[]],
['ref','Referrals','🔁','Refer patients out or receive referrals in.','referrals.php',['doctor','receptionist','records']],
['tele','Telemedicine','📡','Request specialist consultations remotely.','telemed.php',['doctor','nurse']],
['chp','Field Reports','🏘️','Community health promoter household visits.','chp.php',['chp','records','nurse','doctor']],
['rep','Reports','📊','MOH-style summaries and downloads.','reports.php',['records','doctor']],
['msg','Messages','✉️','Secure messages between staff.','messages.php',null]];}
function can_mod($m){$r=user()['role'];return $r=='admin'||$m[5]===null||in_array($r,$m[5]);}
function shell_css(){return '<style>:root{--card:#fff}[data-theme=dark]{--ink:#e6f0ee;--bg:#0b1616;--line:#27403f;--card:#142626}
.app{display:flex;min-height:100vh}#sb{width:236px;background:#073b3b;color:#fff;padding:10px 8px;position:sticky;top:0;height:100vh;overflow:auto;flex:none;transition:width .25s}
.lg{font-size:20px;font-weight:700;padding:10px;display:flex;gap:8px}.lg u{text-decoration:none}.mi{display:flex;align-items:center;gap:12px;padding:9px 10px;border-radius:10px;color:#cfe9e5;text-decoration:none;margin:2px 0;white-space:nowrap}
.mi b{font-size:19px;width:26px;text-align:center}.mi u{text-decoration:none}.mi:hover{background:#0f5c5c;color:#fff}.mi.on{background:linear-gradient(90deg,#12a39a,#0f5c5c);color:#fff;box-shadow:0 0 12px rgba(18,163,154,.6)}.mi.soon{opacity:.5}
.mi small,.bd{margin-left:auto;font-size:11px;background:#456;border-radius:9px;padding:1px 7px;font-style:normal}.bd{background:#a23b2a}
.col #sb{width:64px}.col .mi u,.col .mi small,.col .bd,.col .lg u{display:none}.cn{flex:1;min-width:0}
.top{display:flex;align-items:center;gap:10px;background:linear-gradient(90deg,#0f5c5c,#0b8f8f);padding:8px 14px;position:sticky;top:0;z-index:40;color:#fff}
.top .ib{margin:0;background:rgba(255,255,255,.15);font-size:18px;padding:6px 10px}.loc{background:rgba(255,255,255,.15);padding:5px 12px;border-radius:20px;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px}
.av{display:inline-block;width:28px;height:28px;line-height:28px;border-radius:50%;background:#fff;color:#0f5c5c;font-size:14px}
.pw{position:relative}.pw ul{display:none;position:absolute;right:0;top:42px;background:var(--card);border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.3);list-style:none;margin:0;padding:6px;min-width:200px;z-index:60}.pw.on ul{display:block}.pw a{display:block;padding:6px 10px;color:var(--ink);text-decoration:none;border-radius:5px}.pw a:hover{background:rgba(15,92,92,.15)}
.mc{display:flex;gap:12px;align-items:flex-start;text-decoration:none;color:var(--ink);padding:10px;border-radius:10px;transition:.2s}a.mc:hover{background:rgba(15,92,92,.12);transform:translateY(-2px)}.mc b{font-size:28px}.mc small{color:#5b706e;display:block}
.tl{border-left:3px solid var(--brand);margin-left:14px;padding-left:22px}.tl div{position:relative;margin:0 0 14px}.tl b{position:absolute;left:-41px;background:var(--card);border-radius:50%;width:32px;text-align:center;font-size:18px}
.card,.stat{background:var(--card)}input,select,textarea{background:var(--card);color:var(--ink)}[data-theme=dark] .sw ul{background:var(--card)}
@media(max-width:800px){#sb{position:fixed;left:-250px;z-index:70;transition:left .25s}.open #sb{left:0}.loc{display:none}}</style>';}
function shell_js(){return '<script>(function(){var d=document,b=d.body,h=d.documentElement;try{var t=localStorage.getItem("th");if(t)h.dataset.theme=t;if(localStorage.getItem("sbc")=="1")b.classList.add("col")}catch(x){}
d.getElementById("mb").onclick=function(){if(innerWidth<800)b.classList.toggle("open");else{b.classList.toggle("col");try{localStorage.setItem("sbc",b.classList.contains("col")?"1":"0")}catch(x){}}};
d.getElementById("th").onclick=function(){var n=h.dataset.theme=="dark"?"light":"dark";h.dataset.theme=n;try{localStorage.setItem("th",n)}catch(x){}};
var p=d.querySelector(".pw");p.querySelector("button").onclick=function(e){e.stopPropagation();p.classList.toggle("on")};d.addEventListener("click",function(){p.classList.remove("on")})})();</script>';}
function shell_open(){global $pdo;$u=user();$cur=basename($_SERVER['SCRIPT_NAME']);$f='All facilities';
 if($u['facility']){$q=$pdo->prepare('SELECT name FROM facilities WHERE id=?');$q->execute([$u['facility']]);$f=$q->fetchColumn()?:$f;}
 $it='';foreach(modules() as $m){if(!can_mod($m))continue;$bd=($m[0]=='msg'&&!empty($GLOBALS['unread']))?'<i class=bd>'.$GLOBALS['unread'].'</i>':'';
  $it.=$m[4]===''?'<span class="mi soon" title="Coming soon"><b>'.$m[2].'</b><u>'.e($m[1]).'</u><small>soon</small></span>':'<a class="mi'.($m[4]===$cur?' on':'').'" href="'.$m[4].'" title="'.e($m[1]).'"><b>'.$m[2].'</b><u>'.e($m[1]).'</u>'.$bd.'</a>';}
 return shell_css().'<div class=app><aside id=sb><div class=lg>🏥 <u>Hospital</u></div>'.$it.'</aside><div class=cn><header class=top><button id=mb type=button class=ib>&#9776;</button><span class=loc>&#128205; '.e($f).'</span>'.switch_btn().'<span style="margin-left:auto"></span>'.dots_btn().'<button id=th type=button class=ib title="Light / dark">&#9788;</button>
<div class=pw><button type=button class=ib style="padding:4px 6px"><b class=av>'.e(strtoupper(substr($u['name'],0,1))).'</b></button><ul><li><a>'.e($u['name']).' ('.e($u['role']).')</a></li><li><a href=profile.php>My profile</a></li><li><a href=messages.php>Messages</a></li><li><a href=logout.php>Sign out</a></li></ul></div></header>'.launcher().shell_js();}
