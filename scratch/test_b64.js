const fs = require('fs');
const lines = fs.readFileSync('Blades/web_sk_pkwt.blade.php', 'utf8').split('\n');

const lineH = lines[198].trim();
const lineF = lines[199].trim();

const b64H = lineH.replace(/^\$b64HeaderPkwt\s*=\s*'/, '').replace(/';$/, '');
const b64F = lineF.replace(/^\$b64FooterPkwt\s*=\s*'/, '').replace(/';$/, '');

console.log('b64H length:', b64H.length);
console.log('b64F length:', b64F.length);

const bufH = Buffer.from(b64H, 'base64');
const bufF = Buffer.from(b64F, 'base64');

fs.writeFileSync('media/test_hdr_out.jpg', bufH);
fs.writeFileSync('media/test_ftr_out.jpg', bufF);

console.log('Header written:', bufH.length, 'bytes');
console.log('Footer written:', bufF.length, 'bytes');

// Check magic bytes
console.log('Header magic:', bufH.slice(0, 4));
console.log('Footer magic:', bufF.slice(0, 4));
