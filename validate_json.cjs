const fs = require('fs');
try {
  JSON.parse(fs.readFileSync('database/data/heritage_data.json', 'utf8'));
  console.log("Valid JSON");
} catch(e) {
  console.log(e.message);
}
