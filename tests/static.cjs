/* Use QA_DEPS for an isolated npm install of postcss. */
const fs=require('fs'), path=require('path'), cp=require('child_process');
const postcss=require(path.resolve(process.env.QA_DEPS||'.local/qa/node_modules','postcss'));
const walk=dir=>fs.readdirSync(dir,{withFileTypes:true}).flatMap(e=>e.isDirectory()?walk(path.join(dir,e.name)):[path.join(dir,e.name)]);
let css=0,js=0;
for(const file of walk('perfect10')) {
 if(file.endsWith('.css')) { postcss.parse(fs.readFileSync(file,'utf8'),{from:file});css++; }
 if(file.endsWith('.js')) { cp.execFileSync(process.execPath,['--check',file]);js++; }
}
console.log(`PASS: PostCSS parsed ${css} stylesheets; Node syntax-checked ${js} scripts`);
