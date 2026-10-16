/* Actual theme CSS/WordPress rendering, not CSS-string assertions. Disposable site only. */
const fs=require('fs'),path=require('path'),assert=require('node:assert/strict');
const deps=process.env.QA_DEPS||path.resolve('.local/qa/node_modules');
const {chromium}=require(path.join(deps,'@playwright/test'));
const binary=require(path.join(deps,'@sparticuz/chromium/build/index.js')).default;
(async()=>{
 const browser=await chromium.launch({executablePath:await binary.executablePath(),args:binary.args.filter(a=>!['--disable-web-security','--allow-running-insecure-content'].includes(a)),headless:true});
 const page=await browser.newPage({reducedMotion:'reduce'}), errors=[];
 page.on('pageerror',e=>errors.push(e.message));
 const base=process.env.P10_TEST_URL||'http://127.0.0.1:8080';
 await page.route('**/*', route=>{ const url=new URL(route.request().url()); return /^https?:$/.test(url.protocol) && url.origin!==new URL(base).origin ? route.abort() : route.continue(); });
 await page.context().addCookies([{name:'p10_age_ok',value:'1',url:base}]);
 const routes=JSON.parse(fs.readFileSync('.local/layout-routes.json','utf8'));
 const measurements=[];
 for(const width of [320,390,768,1024,1100,1180,1280,1366,1440,1536,1920,2560,3840]){
  await page.setViewportSize({width,height:1000});
  const selections=[320,768,1440,2560].includes(width)?Object.entries(routes):[['home',routes.home]];
  for(const [label,route] of selections){
   const response=await page.goto(base+route,{waitUntil:'networkidle'});assert(response.status()<400,`${label} HTTP status`);
   const result=await page.evaluate(()=>{
    const w=innerWidth;
    const shells=[...document.querySelectorAll('.p10-shell,.p10-header__inner,.p10-hero__inner,.p10-editor-content')].filter(el=>el.getBoundingClientRect().width>0).map(el=>({width:el.getBoundingClientRect().width,left:el.getBoundingClientRect().left,selector:el.className}));
    const controls=[...document.querySelectorAll('input:not([type=hidden]),select,textarea,button')].filter(el=>el.getBoundingClientRect().width && !el.closest('dialog:not([open]),.p10-drawer,.p10-agegate,.p10-card__overlay,.p10-concierge__panel,[aria-hidden="true"]') && getComputedStyle(el).visibility!=='hidden').map(el=>({tag:el.tagName,type:el.type,rect:el.getBoundingClientRect().toJSON(),font:parseFloat(getComputedStyle(el).fontSize)}));
    return {w,doc:document.documentElement.scrollWidth,shells,controls,font:parseFloat(getComputedStyle(document.body).fontSize),space:parseFloat(getComputedStyle(document.querySelector('.p10-footer')).paddingTop),header:document.querySelector('.p10-header').getBoundingClientRect().toJSON(),headerLinks:[...document.querySelectorAll('.p10-header .p10-brand,.p10-header .p10-nav__list > li > a,.p10-header__actions > a,.p10-header__actions > button')].filter(el=>el.getBoundingClientRect().width && getComputedStyle(el).visibility!=='hidden').map(el=>({text:el.textContent.trim(),rect:el.getBoundingClientRect().toJSON()}))};
   });
   assert(result.shells.length,`${label}: expected shells`);
   for(const el of result.shells){assert(el.width<=width*.97+1,`${label}@${width}: shell ${el.selector} ${el.width}`);assert(el.left>=-1,`${label}@${width}: shell left edge`);}
   for(const link of result.headerLinks){assert(link.rect.x>=-1 && link.rect.right<=width+1,`${label}@${width}: header link clipped ${link.text}`);assert(link.rect.top>=-1 && link.rect.bottom<=result.header.bottom+1,`${label}@${width}: header too short for ${link.text} (${link.rect.bottom})`);}
   assert(result.doc<=width+1,`${label}@${width}: page overflow ${result.doc}`);
   for(const el of result.controls){assert(el.rect.x>=-1 && el.rect.x+el.rect.width<=width+1,`${label}@${width}: control overflow ${el.tag} ${JSON.stringify(el.rect)}`);if(['INPUT','SELECT','TEXTAREA'].includes(el.tag) && !['checkbox','radio','range'].includes(el.type))assert(el.font>=16,`${label}@${width}: text field too small`);}
   if(label==='home'){
    const shell=result.shells.find(el=>el.selector==='p10-shell');assert(Math.abs(shell.width-width*.97)<1,`home@${width}: actual 97vw, not a desktop pixel cap`);
    measurements.push({width,shell:shell.width,bodyFont:result.font,footerPadding:result.space});
    if([390,1440,2560].includes(width))await page.screenshot({path:`.local/layout-${width}.png`});
   }
   console.log(`PASS: ${label} @ ${width}px — 97vw maximum shells, no page/control overflow, readable fields`);
  }
 }
 assert(measurements.at(-1).bodyFont>measurements.find(m=>m.width===1440).bodyFont,'body font scales with vw beyond desktop baseline');
 assert(measurements.at(-1).footerPadding>measurements.find(m=>m.width===1440).footerPadding,'Customizer footer padding scales with vw');
 console.log('MEASUREMENTS: '+JSON.stringify(measurements));
 assert.deepEqual(errors,[]);console.log('PASS: no browser JavaScript page errors in layout matrix');
 await browser.close();
})().catch(e=>{console.error(e);process.exit(1)});
