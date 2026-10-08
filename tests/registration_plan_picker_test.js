const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
function scenario(radioMode) {
    const handlers = {};
    const tier = {value: '', closest: () => form};
    function select() {
        return {value: '', disabled: false, options: ['', 'individual', 'unsupported'].map(value => ({value, selected:false})), closest:()=>({querySelector:()=>dob})};
    }
    const dob = {required:false}, principal = select(), corporate = select();
    const price = {textContent:''}, card = {hidden:false,querySelector:()=>price};
    const radio = {value:'individual',checked:false,closest:()=>card};
    const summary = {textContent:''};
    const form = {
        querySelector:()=>radioMode?null:principal,
        querySelectorAll:s=>s.startsWith('input')?[radio]:[corporate],
        addEventListener:(type,fn)=>handlers[type]=fn,
        reportValidity:()=>{}
    };
    const window = {shenaRegistrationPlans:{individual:{name:'Individual',basic:100,platinum:300},unsupported:{name:'Basic only',basic:150,platinum:null}}};
    const document = {querySelector:()=>tier,getElementById:id=>id==='corporateTotalPreview'?summary:null};
    vm.runInNewContext(fs.readFileSync('public/js/registration-plan.js','utf8'),{window,document});
    assert.match(summary.textContent,/Choose Basic or Platinum first/);
    assert.equal(radioMode?radio.disabled:principal.disabled,true);
    tier.value='1';handlers.change();
    principal.value='individual';radio.checked=true;corporate.value='individual';handlers.change();
    assert.match(summary.textContent,/Platinum total: KES 600/);
    assert.equal(corporate.options[2].hidden,true);
    assert.equal(dob.required,true);
    if(radioMode) assert.match(price.textContent,/Platinum - KES 300/);
    tier.value='0';handlers.change();
    assert.match(summary.textContent,/Basic total: KES 200/);
    assert.equal(corporate.options[2].hidden,false);
    assert.equal(dob.required,false);
    corporate.value='';
    let prevented=false,stopped=false;
    handlers.submit({preventDefault:()=>prevented=true,stopImmediatePropagation:()=>stopped=true});
    assert.ok(prevented&&stopped,'Incomplete group blocks AJAX/native submission');
}
scenario(false);scenario(true);
console.log('Registration picker: select and radio flows, tier changes, totals, unavailable plans and incomplete groups passed.');
