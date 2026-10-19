<?php
function art_css(){return '<style>
.split{display:grid;grid-template-columns:minmax(320px,440px) 1fr;min-height:100vh}.split.r{grid-template-columns:1fr minmax(320px,440px)}.lf{background:#fff;padding:48px 40px;display:flex;flex-direction:column;justify-content:center;text-align:left}
.lf h2{margin:0 0 4px;font-size:28px}.lf p.sub{margin:0 0 20px;color:#5b706e}.opt{display:flex;gap:10px;align-items:center;border:1px solid var(--line);border-radius:6px;padding:10px;margin:8px 0;font-weight:400}.opt input{width:auto}.opt small{color:#5b706e;margin-left:auto}
.lf button{width:100%}.rt{position:relative;overflow:hidden;background:linear-gradient(135deg,#073b3b,#0f5c5c,#12a39a,#073b3b);background-size:300% 300%;animation:bg 14s ease infinite;display:flex;align-items:center;justify-content:center}
.hero{position:relative;overflow:hidden;border-radius:12px;margin-bottom:16px;min-height:340px;background:linear-gradient(135deg,#073b3b,#0f5c5c,#12a39a,#073b3b);background-size:300% 300%;animation:bg 14s ease infinite;color:#fff;display:flex;align-items:center;justify-content:space-between;padding:10px 28px}
.hero h2{margin:0;font-size:26px;z-index:2}.hero p{margin:4px 0 0;opacity:.85;z-index:2}.hero svg.syr{width:min(420px,48%);height:auto;z-index:1}
.ecg{position:absolute;left:0;right:0;bottom:6px;width:100%;height:60px;opacity:.55}.ecg path{fill:none;stroke:#7ff3e0;stroke-width:2.5;stroke-dasharray:900;stroke-dashoffset:900;animation:ecg 3.5s linear infinite;filter:drop-shadow(0 0 4px #7ff3e0)}
.bub{position:absolute;bottom:-20px;border-radius:50%;background:rgba(255,255,255,.18);animation:rise linear infinite}
.stat{background:#fff;border:1px solid var(--line);border-radius:10px;padding:16px;transition:.25s;position:relative;overflow:hidden}.stat:hover{transform:translateY(-4px);box-shadow:0 8px 22px rgba(15,92,92,.25)}
.stat b{font-size:30px;color:var(--brand);display:block}.stat:after{content:"";position:absolute;top:0;left:-60%;width:40%;height:100%;background:linear-gradient(100deg,transparent,rgba(255,255,255,.7),transparent);animation:shine 5s infinite}
.plg{animation:push 5s ease-in-out infinite alternate}.liq{transform-box:fill-box;transform-origin:right center;animation:liq 5s ease-in-out infinite alternate}
.dr{animation:drop 1.6s ease-in infinite;opacity:0}.d2{animation-delay:.5s}.d3{animation-delay:1s}
@keyframes bg{0%,100%{background-position:0 50%}50%{background-position:100% 50%}}@keyframes ecg{to{stroke-dashoffset:0}}
@keyframes rise{to{transform:translateY(-320px);opacity:0}}@keyframes shine{0%,60%{left:-60%}100%{left:130%}}
@keyframes push{from{transform:translateX(0)}to{transform:translateX(110px)}}@keyframes liq{from{transform:scaleX(1)}to{transform:scaleX(.31)}}
@keyframes drop{0%{transform:translateY(0);opacity:1}100%{transform:translateY(46px);opacity:0}}
@media(max-width:800px){.split,.split.r{grid-template-columns:1fr}.rt{display:none}.hero{flex-direction:column;text-align:center}.hero .orbit{width:80%}}
.orbit{overflow:visible;flex:none}.hero .orbit{width:min(340px,44%);z-index:1}.rt .orbit{width:min(88%,80vh)}
.ring,.trk,.core{transform-origin:300px 300px}.ring{animation:spin linear infinite}.r1{animation-duration:40s}.r2{animation-duration:70s;animation-direction:reverse}.r3{animation-duration:100s}
.up{transform-origin:0 0;animation:spin linear infinite}.u1{animation-duration:40s;animation-direction:reverse}.u2{animation-duration:70s}.u3{animation-duration:100s;animation-direction:reverse}
.trk{fill:none;stroke:rgba(255,215,130,.6);stroke-width:1.6;animation:spin linear infinite}.t2{animation-duration:90s;animation-direction:reverse}.t1{animation-duration:60s}.t3{animation-duration:130s}
.core{animation:pulse 3s ease-in-out infinite}.tw{animation:tw 3s ease-in-out infinite}
@keyframes spin{to{transform:rotate(360deg)}}@keyframes pulse{50%{transform:scale(1.14)}}@keyframes tw{50%{opacity:.1}}
@media(prefers-reduced-motion:reduce){*{animation:none!important}}</style>';}
function art_syringe(){return '<svg class=syr viewBox="0 0 400 200"><g transform="rotate(-16 200 100)"><rect x="90" y="68" width="190" height="64" rx="9" fill="rgba(255,255,255,.14)" stroke="#fff" stroke-width="3"/>
<rect class=liq x="108" y="76" width="166" height="48" rx="5" fill="#7ff3e0" opacity=".85"/><g stroke="#fff" stroke-width="2" opacity=".8"><path d="M130 68v14M155 68v10M180 68v14M205 68v10M230 68v14M255 68v10"/></g>
<g class=plg><rect x="6" y="80" width="10" height="40" rx="3" fill="#fff"/><rect x="14" y="96" width="86" height="8" fill="#fff"/><rect x="98" y="72" width="14" height="56" rx="3" fill="#0b2e2e" stroke="#fff" stroke-width="2"/></g>
<rect x="280" y="90" width="16" height="20" rx="3" fill="#fff"/><path d="M296 100H386" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
<circle class="dr" cx="388" cy="104" r="4" fill="#7ff3e0"/><circle class="dr d2" cx="388" cy="104" r="3.5" fill="#7ff3e0"/><circle class="dr d3" cx="388" cy="104" r="3" fill="#7ff3e0"/></g></svg>';}
function art_bubbles(){$o='';for($i=0;$i<12;$i++){$s=rand(8,26);$o.='<span class=bub style="left:'.rand(2,96).'%;width:'.$s.'px;height:'.$s.'px;animation-duration:'.rand(7,15).'s;animation-delay:-'.rand(0,12).'s"></span>';}return $o;}
function art_ecg(){return '<svg class=ecg viewBox="0 0 900 60" preserveAspectRatio="none"><path d="M0 35H180l15-25 18 50 18-40 14 15H420l15-25 18 50 18-40 14 15H660l15-25 18 50 18-40 14 15H900"/></svg>';}
function art_orbit(){
 $d='<defs><radialGradient id=gl><stop offset=0 stop-color=#7ff3e0 stop-opacity=.9 /><stop offset=1 stop-color=#7ff3e0 stop-opacity=0 /></radialGradient>
<g id=sy-cap><path d="M0-11H-14a11 11 0 0 0 0 22H0z" style="fill:var(--a)"/><path d="M0-11H14a11 11 0 0 1 0 22H0z" style="fill:var(--b)"/><path d="M-12-6q8-3 14-2" stroke=#fff stroke-opacity=.6 stroke-width=2 fill=none stroke-linecap=round /></g>
<g id=sy-pill><circle r=13 style="fill:var(--a)"/><path d="M-13 0H13" stroke=#fff stroke-opacity=.7 stroke-width=2 /><circle cx=-4 cy=-6 r=2.5 fill=#fff opacity=.5 /></g>
<g id=sy-mom><circle cy=-30 r=7 style="fill:var(--s)"/><path d="M-8-30c0-9 16-9 16 0c-4-3-12-3-16 0z" style="fill:var(--b)"/><path d="M-6-22C-10-8-9 4-6 12L-9 32H8L5 12C24 12 22-8 7-12L6-22Z" style="fill:var(--a)"/><path d="M13 1c-2-3-6 0-3 3l3 3 3-3c3-3-1-6-3-3z" fill=#fff opacity=.85 /></g>
<g id=sy-baby><ellipse cy=9 rx=13 ry=17 style="fill:var(--a)"/><path d="M-13 6Q0 14 13 6" stroke=#fff stroke-width=2.5 fill=none opacity=.8 /><circle cy=-13 r=10 style="fill:var(--s)"/><path d="M-5-13q2 2 4 0M1-13q2 2 4 0" stroke=#5a3a2a stroke-width=1.3 fill=none /><circle cx=-6 cy=-9 r=2 fill=#ff9aa8 opacity=.7 /><circle cx=6 cy=-9 r=2 fill=#ff9aa8 opacity=.7 /><path d="M-4-7q4 3 8 0" stroke=#c0505f fill=none stroke-width=1.2 /><path d="M-6-22q6-5 12 0" stroke=#5a3a2a fill=none /></g>
<path id=sy-heart d="M0 10C-22-4-12-22 0-9 12-22 22-4 0 10z" style="fill:var(--a)"/>
<g id=sy-cross><rect x=-5 y=-15 width=10 height=30 rx=3 style="fill:var(--a)"/><rect x=-15 y=-5 width=30 height=10 rx=3 style="fill:var(--a)"/></g>
<g id=sy-drop><path d="M0-16C10-2 12 4 12 8a12 12 0 0 1-24 0c0-4 2-10 12-24z" style="fill:var(--a)"/><path d="M-5 4a6 6 0 0 0 4 5" stroke=#fff stroke-width=2 fill=none opacity=.7 /></g>
<g id=sy-therm><rect x=-4 y=-20 width=8 height=30 rx=4 fill=#fff style="stroke:var(--a)" stroke-width=2 /><circle cy=14 r=8 style="fill:var(--a)"/><rect x=-1.5 y=-8 width=3 height=20 style="fill:var(--a)"/></g>
<g id=sy-syr><rect x=-18 y=-6 width=30 height=12 rx=3 fill=#fff fill-opacity=.85 style="stroke:var(--a)" stroke-width=2 /><rect x=-16 y=-4 width=18 height=8 style="fill:var(--b)"/><rect x=-30 y=-2 width=14 height=4 style="fill:var(--a)"/><rect x=-33 y=-7 width=3 height=14 style="fill:var(--a)"/><path d="M12 0H30" style="stroke:var(--a)" stroke-width=2 /></g></defs>';
 $rings=[[125,1,[['cap','#ff4d6d','#ffd166','',1.1],['baby','#8ecae6','','#ffd8b5',1.2],['pill','#06d6a0','','',1.1],['heart','#f15bb5','','',1]]],
  [200,2,[['mom','#9b5de5','#3d2b1f','#f2c9a0',1.25],['cap','#00bbf9','#fee440','',1.2],['drop','#ff4d6d','','',1.1],['baby','#ffafcc','','#e8b98f',1.2],['syr','#06d6a0','#ffd166','',1.1]]],
  [270,3,[['mom','#ff9f1c','#2b2118','#c68d60',1.25],['cross','#ef476f','','',1],['cap','#06d6a0','#9b5de5','',1.2],['therm','#118ab2','','',1.1],['pill','#ffd166','','',1.1],['baby','#a0e7e5','','#f2c9a0',1.2]]]];
 $o='<svg class=orbit viewBox="-10 -10 620 620">'.$d.'<circle class=core cx=300 cy=300 r=120 fill="url(#gl)"/>';
 foreach([[125,'2 8','t1'],[200,'14 7','t2'],[270,'1 6','t3']] as $t)$o.='<circle class="trk '.$t[2].'" cx=300 cy=300 r='.$t[0].' stroke-dasharray="'.$t[1].'"/>';
 for($i=0;$i<16;$i++)$o.='<circle class=tw cx='.rand(0,600).' cy='.rand(0,600).' r='.(rand(10,28)/10).' fill=#fff style="animation-delay:-'.rand(0,30)/10 .'s"/>';
 foreach($rings as $ri=>$R){$o.='<g class="ring r'.$R[1].'">';$n=count($R[2]);
  foreach($R[2] as $i=>$it){$a=2*M_PI*$i/$n+$ri;$x=round(300+$R[0]*cos($a),1);$y=round(300+$R[0]*sin($a),1);
   $o.='<g transform="translate('.$x.' '.$y.')"><g class="up u'.$R[1].'"><use href="#sy-'.$it[0].'" transform="scale('.$it[4].')" style="--a:'.$it[1].';--b:'.($it[2]?:'#fff').';--s:'.($it[3]?:'#ffd8b5').'"/></g></g>';}
  $o.='</g>';}
 $s=str_replace('<svg class=syr viewBox="0 0 400 200">','<svg x=205 y=257 width=190 height=95 viewBox="0 0 400 200">',art_syringe());
 return $o.$s.'</svg>';}
