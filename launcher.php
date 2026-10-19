<?php
function depts(){global $pdo;static $d=null;if($d===null){try{$d=$pdo->query('SELECT * FROM departments ORDER BY sort')->fetchAll();}catch(Exception $x){$d=[];}}return $d;}
function dept_link($s){$m=['xray'=>['imaging.php',['radiographer','doctor','nurse']],'ct-scan'=>['imaging.php',['radiographer','doctor','nurse']],'nbu'=>['nbu.php',['nurse','doctor']],'admission'=>['inpatient.php',['nurse','doctor','receptionist']],'triage'=>['queue.php',null],'pharmacy'=>['pharmacy.php',['pharmacist']],'laboratory'=>['lab.php',['lab']],'billing'=>['billing.php',['cashier','receptionist']]];
  $r=user()['role']??'';if(isset($m[$s])&&($r=='admin'||$m[$s][1]===null||in_array($r,$m[$s][1])))return $m[$s][0];return 'dept.php?d='.$s;}
function dots_btn(){$o='';foreach([0,1,2,3,4,5] as $i){$a=$i*M_PI/3;$o.='<circle cx='.round(9*cos($a),1).' cy='.round(9*sin($a),1).' r='.(2+($i%3)*.5).' fill=#fff />';}
  return '<button id=dots type=button title="All departments" aria-label=Departments style="margin:0;background:none;padding:4px 6px"><svg width=28 height=28 viewBox="-13 -13 26 26">'.$o.'</svg></button>';}
function switch_btn(){$u=user();if(!$u||!in_array($u['role'],['doctor','nurse','admin']))return '';$c='Switch ward';$l='';
  foreach(depts() as $d){if($d['slug']==($_SESSION['ctx']??''))$c=$d['icon'].' '.$d['name'];$l.='<li><a href="switch.php?d='.e($d['slug']).'">'.$d['icon'].' '.e($d['name']).'</a></li>';}
  return '<div class=sw><button type=button style="margin:0;background:#0b8f8f;white-space:nowrap">&#8652; '.e($c).'</button><ul>'.$l.'</ul></div>';}
function launcher(){$t='';foreach(depts() as $i=>$d)$t.='<a class=tile style="--i:'.$i.';--h:'.($i*17%360).'" href="'.e(dept_link($d['slug'])).'"><span>'.$d['icon'].'</span>'.e($d['name']).'</a>';
return '<style>#lp{position:fixed;inset:0;background:rgba(7,40,40,.85);backdrop-filter:blur(8px);display:none;z-index:50;overflow:auto;padding:70px 20px}#lp.on{display:block;animation:fi .25s}
#lp input{max-width:420px;display:block;margin:0 auto 24px;font-size:17px;padding:12px 18px;border-radius:30px}
.tg{max-width:900px;margin:auto;display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:14px}
.tile{display:flex;flex-direction:column;align-items:center;gap:8px;padding:18px 8px;border-radius:16px;background:linear-gradient(145deg,hsl(var(--h),70%,52%),hsl(calc(var(--h) + 40),70%,38%));color:#fff;text-decoration:none;font-weight:600;text-align:center;opacity:0;transform:translateY(18px) scale(.9);transition:transform .2s,box-shadow .2s}
#lp.on .tile{animation:tu .45s forwards;animation-delay:calc(var(--i) * 30ms)}.tile span{font-size:30px}.tile:hover{transform:translateY(-4px) scale(1.06)!important;box-shadow:0 10px 24px rgba(0,0,0,.4)}
#dots svg{transition:transform .5s}#dots:hover svg,#dots.on svg{transform:rotate(180deg)}
.sw{position:relative}.sw ul{display:none;position:absolute;top:36px;left:0;background:#fff;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.3);list-style:none;margin:0;padding:6px;max-height:70vh;overflow:auto;z-index:60;min-width:200px}.sw.on ul{display:block}.sw ul a{display:block;color:var(--ink);padding:6px 10px;border-radius:5px}.sw ul a:hover{background:#e6f2f0}
@keyframes fi{from{opacity:0}}@keyframes tu{to{opacity:1;transform:none}}</style>
<div id=lp><input id=ls placeholder="Search departments..." autocomplete=off><div class=tg>'.$t.'</div></div>
<script>(function(){var lp=document.getElementById("lp"),b=document.getElementById("dots"),s=document.getElementById("ls");
function f(){var q=s.value.toLowerCase();lp.querySelectorAll(".tile").forEach(function(a){a.style.display=a.textContent.toLowerCase().indexOf(q)<0?"none":""})}
function tog(o){lp.classList.toggle("on",o);b.classList.toggle("on",o);if(o){s.value="";f();s.focus()}}
b.onclick=function(e){e.stopPropagation();tog(!lp.classList.contains("on"))};lp.onclick=function(e){if(e.target==lp)tog(false)};
document.addEventListener("keydown",function(e){if(e.key=="Escape")tog(false)});s.oninput=f;
var sw=document.querySelector(".sw");if(sw){sw.querySelector("button").onclick=function(e){e.stopPropagation();sw.classList.toggle("on")};document.addEventListener("click",function(){sw.classList.remove("on")})}})();</script>';}
