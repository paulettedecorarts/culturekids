const fs = require('fs');
const path = require('path');

// Try requiring the JS file
try {
  const data = require('../docs/heritage_data.js');
  const tribes = data.TRIBES;
  
  if (!tribes || !Array.isArray(tribes)) {
      console.error('TRIBES array not found');
      process.exit(1);
  }

  const outPath = path.join(__dirname, 'database', 'data', 'heritage_data.json');
  fs.mkdirSync(path.dirname(outPath), { recursive: true });
  fs.writeFileSync(outPath, JSON.stringify(tribes, null, 2));
  console.log(`Successfully wrote ${tribes.length} tribes to ${outPath}`);
} catch (e) {
  console.error(e);
}
