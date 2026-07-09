import { writeFileSync, existsSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { createRequire } from 'node:module';

const __dirname = dirname(fileURLToPath(import.meta.url));
const base = join(__dirname, '..');
const require = createRequire(import.meta.url);
const seed = require('./load-activities-seed.cjs');

const tribes = seed.tribesMeta.map((tribe) => {
  const id = tribe.id;
  const extensions = ['jpg', 'jpeg', 'png', 'webp'];

  for (const ext of extensions) {
    const relative = `tribes/${id}.${ext}`;
    const full = join(base, 'seed/assets', relative);

    if (existsSync(full)) {
      return { ...tribe, assets: { iconImage: relative } };
    }
  }

  return tribe;
});

const payload = {
  tribes,
  activities: seed.activitiesSeed,
};

writeFileSync(
  join(base, 'seed/activities.seed.json'),
  `${JSON.stringify(payload, null, 2)}\n`,
  'utf8'
);

console.log(
  `Wrote ${payload.activities.length} activities and ${payload.tribes.length} tribes to seed/activities.seed.json`
);
