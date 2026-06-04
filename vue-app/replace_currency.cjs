const fs = require('fs');
const path = require('path');

function walk(dir) {
  let results = [];
  const list = fs.readdirSync(dir);
  list.forEach(file => {
    file = path.join(dir, file);
    const stat = fs.statSync(file);
    if (stat && stat.isDirectory()) {
      results = results.concat(walk(file));
    } else if (file.endsWith('.vue')) {
      results.push(file);
    }
  });
  return results;
}

const files = walk('c:/Users/essuo/Downloads/Project Tracker/Project Tracker/vue-app/src');
let count = 0;

files.forEach(file => {
  let content = fs.readFileSync(file, 'utf8');
  // Match a dollar sign followed by a digit
  const regex = /\$(?=\d)/g;
  if (regex.test(content)) {
    content = content.replace(regex, 'GHC ');
    fs.writeFileSync(file, content);
    console.log('Updated ' + file);
    count++;
  }
});

console.log('Total files updated: ' + count);
