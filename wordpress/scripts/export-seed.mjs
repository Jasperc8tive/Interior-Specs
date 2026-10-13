// Exports the Next.js data files (lib/*.ts) to JSON fixtures consumed by `wp is seed`.
// Node 24+ strips TypeScript types natively, so the source files are imported as-is.
//   node scripts/export-seed.mjs
import { writeFileSync, mkdirSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath, pathToFileURL } from "node:url";

const root = join(dirname(fileURLToPath(import.meta.url)), "..", "..");
const lib = (name) => import(pathToFileURL(join(root, "lib", name)).href);

const [{ services }, { projects }, { posts }, { testimonials }, { methodActs }, { site, nav }] =
  await Promise.all(
    ["services.ts", "projects.ts", "posts.ts", "testimonials.ts", "method.ts", "site.ts"].map(lib),
  );

const out = join(root, "wordpress", "seed");
mkdirSync(out, { recursive: true });
const write = (file, data) => {
  writeFileSync(join(out, file), JSON.stringify(data, null, 2) + "\n");
  console.log(`seed/${file}  (${Array.isArray(data) ? data.length + " records" : "object"})`);
};

write("services.json", services);
write("projects.json", projects);
write("posts.json", posts);
write("testimonials.json", testimonials);
write("method.json", methodActs);
write("site.json", { ...site, nav });
