const fs = require('fs');
(async () => {
 const tabs = await (await fetch('http://127.0.0.1:9223/json/list')).json();
 const tab = tabs.find(t => t.type === 'page');
 const socket = new WebSocket(tab.webSocketDebuggerUrl);
 await new Promise(resolve => socket.addEventListener('open', resolve, {once:true}));
 let seq = 0; const waiting = new Map();
 socket.addEventListener('message', event => { const m = JSON.parse(event.data); if (m.id && waiting.has(m.id)) { const {resolve,reject}=waiting.get(m.id);waiting.delete(m.id);m.error?reject(m.error):resolve(m.result); } });
 const send = (method, params={}) => new Promise((resolve,reject) => { const id=++seq;waiting.set(id,{resolve,reject});socket.send(JSON.stringify({id,method,params})); });
 const evaluate = async expression => {const r=await send('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(JSON.stringify(r.exceptionDetails));return r.result.value;};
 const wait = async (expression) => {for(let i=0;i<100;i++){if(await evaluate(expression))return;await new Promise(r=>setTimeout(r,100));}throw new Error('Timed out: '+expression);};
 const visit = async path => {await send('Page.navigate',{url:'http://127.0.0.1:8010'+path});await wait(`location.pathname === '${path}' && document.readyState === 'complete'`);};
 const submit = async (values,next) => {await evaluate(`(()=>{const values=${JSON.stringify(values)};for(const [name,value] of Object.entries(values)){const e=document.getElementsByName(name)[0];if(!e)throw new Error(name);e.value=value;}document.querySelector('.builder-form').requestSubmit();})()`);await wait(`location.pathname === '${next}' && document.readyState === 'complete'`);};
 await send('Page.enable');
 const results=[];
 for(const width of [320,375,768,1440]){
  await send('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:false});
  for(const path of ['/','/builder/template','/builder/personal','/builder/experience','/builder/projects']){
   await visit(path);
   const dimensions=await evaluate(`({width:innerWidth,scroll:document.documentElement.scrollWidth})`);
   if(dimensions.scroll>dimensions.width)throw new Error('Overflow '+path+' '+JSON.stringify(dimensions));
   results.push({path,width,overflow:false});
  }
 }
 await send('Emulation.setDeviceMetricsOverride',{width:1440,height:1000,deviceScaleFactor:1,mobile:false});
 await visit('/builder/template');
 await evaluate(`document.querySelector('[value="modern"]').checked=true;document.querySelector('.builder-form').requestSubmit()`);
 await wait(`location.pathname === '/builder/personal' && document.readyState === 'complete'`);
 await submit({'personal[name]':'Ayu Putri','personal[role]':'Product Designer','personal[bio]':'Desain yang bermakna.','personal[email]':'ayu@example.com'},'/builder/experience');
 await evaluate(`document.querySelector('[data-repeat-group="education"] [data-add]').click()`);
 if(await evaluate(`document.querySelectorAll('[data-repeat-group="education"] [data-row]').length`)!==2)throw new Error('Add failed');
 await evaluate(`document.querySelectorAll('[data-repeat-group="education"] [data-remove]')[1].click()`);
 if(await evaluate(`document.querySelectorAll('[data-repeat-group="education"] [data-row]').length`)!==1)throw new Error('Remove failed');
 await submit({'education[0][school]':'Universitas Indonesia','education[0][degree]':'Desain','experience[0][company]':'Studio Kreatif','experience[0][role]':'Designer'},'/builder/projects');
 await submit({'skills':'Figma, CSS','projects[0][title]':'Aplikasi Ruang','projects[0][description]':'Riset dan desain produk.','projects[0][url]':'https://example.com'},'/builder/preview');
 for(const width of [320,375,768,1440]){
  await send('Emulation.setDeviceMetricsOverride',{width,height:1000,deviceScaleFactor:1,mobile:false});
  await visit('/builder/preview');
  const dims=await evaluate(`({width:innerWidth,scroll:document.documentElement.scrollWidth})`);
  if(dims.scroll>dims.width)throw new Error('Preview overflow '+width);
  results.push({path:'/builder/preview',width,overflow:false});
  if(width===375||width===1440){const shot=await send('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});fs.writeFileSync('storage/framework/builder-'+width+'.png',Buffer.from(shot.data,'base64'));}
 }
 console.log(JSON.stringify({checks:results,formFlow:'passed',addRemove:'passed'},null,2));
 await send('Browser.close').catch(()=>{});
 socket.close();
})().catch(e=>{console.error(e);process.exit(1)});
