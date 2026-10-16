/* Fluid-header drawer behavior on real WordPress pages; no menu content is replaced. */
const path=require('path'),assert=require('node:assert/strict');
const deps=process.env.QA_DEPS||path.resolve('.local/qa/node_modules');
const {chromium}=require(path.join(deps,'@playwright/test'));
const binary=require(path.join(deps,'@sparticuz/chromium/build/index.js')).default;
(async()=>{
 const browser=await chromium.launch({executablePath:await binary.executablePath(),args:binary.args.filter(a=>!['--disable-web-security','--allow-running-insecure-content'].includes(a)),headless:true});
 const page=await browser.newPage({reducedMotion:'reduce'}),base=process.env.P10_TEST_URL||'http://127.0.0.1:8080';
 await page.route('**/*',r=>new URL(r.request().url()).origin!==new URL(base).origin?r.abort():r.continue());
 await page.context().addCookies([{name:'p10_age_ok',value:'1',url:base}]);
 for(const width of [390,1440]){
  await page.setViewportSize({width,height:1000});await page.goto(base,{waitUntil:'networkidle'});
  const button=page.locator('[data-p10-burger]'),drawer=page.locator('[data-p10-drawer]');
  assert(await button.isVisible(),'Existing burger must be visible when the full menu cannot fit');
  await button.click();assert.equal(await drawer.getAttribute('aria-hidden'),'false');
  assert.equal(await button.getAttribute('aria-expanded'),'true');
  const links=await drawer.locator('.p10-drawer__list a').evaluateAll(els=>els.map(el=>({text:el.textContent.trim(),x:el.getBoundingClientRect().x,right:el.getBoundingClientRect().right})));
  assert(links.length>0);assert(links.every(el=>el.x>=0 && el.right<=width+1));
  await page.keyboard.press('Escape');assert.equal(await drawer.getAttribute('aria-hidden'),'true');
  assert(await button.evaluate(el=>el===document.activeElement));
  console.log(`PASS: ${width}px drawer preserves links, fits viewport, updates ARIA and restores focus on Escape`);
 }
 await page.locator('[data-p10-burger]').click();
 await page.setViewportSize({width:3840,height:1000});
 await page.waitForFunction(()=>!document.body.classList.contains('p10-compact-nav') && document.documentElement.style.overflow!=='hidden');
 assert.equal(await page.locator('[data-p10-burger]').getAttribute('aria-expanded'),'false');
 console.log('PASS: resizing to a fitting desktop menu closes the drawer and unlocks scrolling');
 await browser.close();
})().catch(e=>{console.error(e);process.exit(1)});
