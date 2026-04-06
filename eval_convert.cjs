const fs = require('fs');
const path = require('path');

try {
  let jsCode = fs.readFileSync(path.join(__dirname, '..', 'docs', 'heritage_data.js'), 'utf8');
  
  jsCode = jsCode.replace('const TRIBES', 'var TRIBES');
  
  // Remove the module export line to prevent ES Module errors
  jsCode = jsCode.replace("if (typeof module !== 'undefined') module.exports = { TRIBES, ACTIVITY_TYPES };", "");
  
  // Evaluate the code so we have the TRIBES array in memory
  eval(jsCode);
  
  // Write valid JSON
  fs.writeFileSync(path.join(__dirname, 'database', 'data', 'heritage_data.json'), JSON.stringify(TRIBES, null, 2));
  console.log("Successfully generated perfect JSON!");
} catch(e) {
  console.log("Error:", e.message);
}
