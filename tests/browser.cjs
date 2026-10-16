/* Run with P10_TEST_URL pointing at a disposable running WordPress, never production.
   QA_DEPS can point at an isolated npm install containing playwright and @sparticuz/chromium. */
const path = require('path');
const deps = process.env.QA_DEPS || path.resolve('.local/qa/node_modules');
const { chromium } = require(path.join(deps, '@playwright/test'));
const binary = require(path.join(deps, '@sparticuz/chromium/build/index.js')).default;
const assert = require('node:assert/strict');
(async () => {
  const browser = await chromium.launch({ executablePath: await binary.executablePath(), args: binary.args.filter(arg => !['--disable-web-security','--allow-running-insecure-content'].includes(arg)), headless: true });
  const page = await browser.newPage();
  const errors = []; page.on('pageerror', error => errors.push(error.message));
  const base = process.env.P10_TEST_URL || 'http://127.0.0.1:8080';
  await page.context().addCookies([{ name: 'p10_age_ok', value: '1', url: base }]);
  for (const [label, width, height] of [['desktop',1440,1000],['tablet',820,1180],['mobile',390,844]]) {
    await page.setViewportSize({width,height}); await page.goto(base,{waitUntil:'networkidle'});
    await page.getByRole('button',{name:'Open website assistant'}).click();
    const dialog=page.getByRole('dialog',{name:'Website Assistant'});
    await dialog.waitFor({state:'visible'});
    assert.equal(await dialog.locator('input[type=email], input[type=tel]').count(),0);
    assert.equal(await dialog.locator('[data-chat-follow]').isVisible(),false);
    const rect=await dialog.boundingBox(); assert(rect.x>=0 && rect.y>=0 && rect.x+rect.width<=width+1 && rect.y+rect.height<=height+1);
    console.log(`PASS: ${label} chatbot opens without contact fields and fits viewport`);
    await dialog.getByLabel('Your message').fill('Brandy'); await dialog.getByRole('button',{name:'Send',exact:true}).click();
    await dialog.locator('.p10-chat-message--assistant').filter({hasText:'I found these published pages'}).waitFor({timeout:30000});
    assert(await dialog.getByRole('link',{name:'Brandy',exact:true}).count());
    console.log(`PASS: ${label} live REST-backed public profile retrieval`);
    await page.screenshot({path:`.local/chat-${label}.png`});
    if(label==='desktop' && await dialog.locator('[data-chat-follow]').isVisible()) {
      await dialog.getByRole('button',{name:'Request follow-up',exact:true}).click();
      await dialog.getByRole('button',{name:'Yes, continue',exact:true}).waitFor();
      assert.equal(await dialog.locator('input[type=email], input[type=tel]').count(),0);
      await dialog.getByRole('button',{name:'No, cancel',exact:true}).click();
      assert.equal(await dialog.locator('.p10-chat-message').count(),1);
      console.log('PASS: follow-up consent prompt is optional and cancellable before any contacts are collected');
    }
    await dialog.getByRole('button',{name:'Reset',exact:true}).click();
    assert.equal(await dialog.locator('.p10-chat-message').count(),1);
    await page.keyboard.press('Escape'); await dialog.waitFor({state:'hidden'});
    assert.equal(await page.getByRole('button',{name:'Open website assistant'}).evaluate(el=>el===document.activeElement),true);
    console.log(`PASS: ${label} reset, Escape and focus return`);
    await page.screenshot({path:`.local/${label}.png`,fullPage:true});
  }
  assert.deepEqual(errors,[]); console.log('PASS: no browser JavaScript page errors (Elementor disabled)');
  await browser.close();
})().catch(e=>{console.error(e);process.exit(1);});
