<?php require 'config.php'; need(['doctor','nurse']); check();
$id=(int)($_GET['visit']??0);$s=$pdo->prepare('SELECT v.*,p.first_name,p.last_name,p.patient_no FROM visits v JOIN patients p ON p.id=v.patient_id WHERE v.id=?');$s->execute([$id]);$v=$s->fetch();if(!$v)die('Visit not found.');
if(user()['role']=='doctor')fee_block($id);
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){$b=clean_html($_POST['body']??'');
  if(trim(strip_tags($b))==='')$err='The note is empty.';
  else{$pdo->prepare('INSERT INTO patient_notes(patient_id,visit_id,dept_id,author_id,title,body) VALUES(?,?,?,?,?,?)')->execute([$v['patient_id'],$id,$v['dept_id'],user()['id'],trim($_POST['title']),$b]);audit("note on visit $id");header("Location: notes.php?visit=$id&saved=1");exit;}}
audit("opened notes visit $id");head('Notes: '.$v['first_name'].' '.$v['last_name'].' ('.$v['patient_no'].')');
if($err)echo '<p class=err>'.e($err).'</p>';if(isset($_GET['saved']))echo '<p class=ok>Note saved.</p>';?>
<style>.tb{display:flex;gap:4px;flex-wrap:wrap;background:#fff;border:1px solid var(--line);border-radius:6px 6px 0 0;padding:6px;position:sticky;top:0;z-index:5}.tb button{margin:0;background:#eef4f3;color:var(--ink);border:1px solid var(--line);padding:5px 10px;min-width:34px}.tb button:hover{background:#cfe6e2}.tb i.sp{width:1px;background:var(--line);margin:0 4px}
.desk{background:#d9dfe0;padding:22px 8px;border-radius:0 0 6px 6px;border:1px solid var(--line)}.pg{background:#fff;max-width:794px;min-height:520px;margin:auto;padding:48px 56px;box-shadow:0 2px 12px rgba(0,0,0,.25);font:16px/1.6 Calibri,Arial,sans-serif;outline:none}.pg:focus{box-shadow:0 2px 16px rgba(15,92,92,.55)}
.sb{max-width:794px;margin:6px auto 0;font-size:13px;color:#4b5d5b;display:flex;justify-content:space-between}.old{border-left:4px solid var(--brand);padding-left:12px;margin:12px 0}</style>
<div class=card><form method=post id=nf><?=field()?><label>Note title</label><input name=title placeholder="e.g. Ward round, Admission note, Progress note" required>
<div class=tb onmousedown="event.preventDefault()"><button type=button data-c=bold><b>B</b></button><button type=button data-c=italic><i>I</i></button><button type=button data-c=underline><u>U</u></button><button type=button data-c=strikeThrough><s>S</s></button><i class=sp></i>
<button type=button data-c=insertUnorderedList title=Bullets>&#8226; List</button><button type=button data-c=insertOrderedList title=Numbers>1. List</button><button type=button data-b=h3>Heading</button><button type=button data-b=p>Text</button><i class=sp></i>
<button type=button data-k=u>AA</button><button type=button data-k=l>aa</button><button type=button data-k=t>Aa</button><i class=sp></i><button type=button data-c=undo>&#8630;</button><button type=button data-c=redo>&#8631;</button><button type=button data-c=removeFormat>Clear</button><button type=button id=ts>Date/time</button></div>
<div class=desk><div class=pg id=ed contenteditable=true spellcheck=true><p><br></p></div><div class=sb><span id=wc>0 words</span><span>Ctrl+B bold | Ctrl+I italic | Ctrl+U underline | Ctrl+Z undo</span></div></div>
<input type=hidden name=body id=body><button>Save note</button></form></div>
<script>(function(){var ed=document.getElementById("ed");
document.querySelectorAll(".tb button[data-c]").forEach(function(b){b.onclick=function(){document.execCommand(b.dataset.c,false,null);ed.focus()}});
document.querySelectorAll(".tb button[data-b]").forEach(function(b){b.onclick=function(){document.execCommand("formatBlock",false,b.dataset.b);ed.focus()}});
document.querySelectorAll(".tb button[data-k]").forEach(function(b){b.onclick=function(){var s=window.getSelection();if(!s.rangeCount||s.isCollapsed)return;var t=s.toString(),k=b.dataset.k;
t=k=="u"?t.toUpperCase():k=="l"?t.toLowerCase():t.toLowerCase().replace(/(^|\s)(\S)/g,function(m,a,c){return a+c.toUpperCase()});document.execCommand("insertText",false,t);ed.focus()}});
document.getElementById("ts").onclick=function(){document.execCommand("insertText",false,new Date().toLocaleString());ed.focus()};
function wc(){var t=ed.innerText.trim();document.getElementById("wc").textContent=(t?t.split(/\s+/).length:0)+" words"}ed.oninput=wc;
document.getElementById("nf").onsubmit=function(){document.getElementById("body").value=ed.innerHTML};})();</script>
<div class=card><h3>Previous notes</h3><?php $n=$pdo->prepare('SELECT n.*,u.name,d.name dn FROM patient_notes n JOIN users u ON u.id=n.author_id LEFT JOIN departments d ON d.id=n.dept_id WHERE n.patient_id=? ORDER BY n.id DESC LIMIT 30');$n->execute([$v['patient_id']]);$n=$n->fetchAll();
if(!$n)echo '<p>No notes yet.</p>';foreach($n as $x)echo '<div class=old><b>'.e($x['title']).'</b><br><small>'.e($x['name']).', '.substr($x['created_at'],0,16).($x['dn']?' | '.e($x['dn']):'').'</small>'.$x['body'].'</div>';?></div><?php foot();
