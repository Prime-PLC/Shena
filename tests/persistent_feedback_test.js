const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
class Element {
    constructor(tag) { this.tag = tag; this.children = []; this.style = {}; this.dataset = {}; this.listeners = {}; this.isConnected = true; }
    setAttribute(k, v) { this[k] = v; }
    append(...children) { children.forEach(c => { c.parent = this; this.children.push(c); }); }
    addEventListener(k, fn) { (this.listeners[k] ||= []).push(fn); }
    querySelector(selector) { return this.children.find(c => selector === '[data-feedback-messages]' && c.dataset.feedbackMessages !== undefined); }
    focus() { document.activeElement = this; }
    showModal() { this.open = true; }
    close() { this.open = false; (this.listeners.close || []).forEach(fn => fn()); }
    remove() { if (this.parent) this.parent.children = this.parent.children.filter(c => c !== this); this.isConnected = false; }
}
function find(el, id) { if (el.id === id) return el; for (const c of el.children) { const got = find(c, id); if (got) return got; } return null; }
const document = {head: new Element('head'), body: new Element('body'), activeElement: new Element('button'), createElement: tag => new Element(tag), getElementById: id => find(document.body, id) || find(document.head, id), addEventListener() {}};
const context = {document, window: {alert() {}}, console, setTimeout() {throw new Error('Feedback must not auto-dismiss');}};
vm.createContext(context);
vm.runInContext(fs.readFileSync(require('node:path').join(__dirname, '../public/js/app.js'), 'utf8'), context);
const app = context.window.ShenaApp;
const previous = document.activeElement;
const dangerous = '<img src=x onerror=alert(1)>';
app.feedback([{type: 'error', message: dangerous}, {type: 'success', message: 'Saved without SMS'}]);
let dialog = document.getElementById('shena-feedback-dialog');
assert.equal(dialog.open, true);
assert.equal(dialog.children[1].children.length, 2);
assert.equal(dialog.children[1].children[0].textContent, dangerous);
assert.equal(dialog.children[1].children[0].innerHTML, undefined);
assert.equal(document.activeElement.tag, 'button');
app.feedback([{type: 'info', message: 'Another result'}]);
assert.equal(document.body.children.length, 1);
assert.equal(dialog.children[1].children.length, 3);
dialog.close();
assert.equal(document.getElementById('shena-feedback-dialog'), null);
assert.equal(document.activeElement, previous);
console.log('Persistent feedback behavior passed: safe text, combined results, manual dismissal and focus restoration.');

const composer = new Element('section'); composer.id = 'sms-review-17';
const editor = new Element('textarea'); composer.querySelector = () => editor;
composer.scrollIntoView = () => { composer.scrolled = true; };
document.body.append(composer);
context.window.matchMedia = () => ({matches:true});
app.feedback([{type:'info', message:'Review draft', target:'#sms-review-17'}]);
document.getElementById('shena-feedback-dialog').close();
assert.equal(document.activeElement, editor);
assert.equal(composer.scrolled, true);
console.log('Got it focuses and scrolls to the exact SMS editor.');
