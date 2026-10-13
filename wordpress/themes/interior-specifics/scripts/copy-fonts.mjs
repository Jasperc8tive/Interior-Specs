// Copies the latin woff2 files the theme self-hosts from @fontsource into assets/fonts/.
// Run after changing font packages: npm run fonts
import { copyFileSync, mkdirSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";

const root = join(dirname(fileURLToPath(import.meta.url)), "..");
const out = join(root, "assets", "fonts");
mkdirSync(out, { recursive: true });

const files = [
  ["@fontsource-variable/playfair-display", "playfair-display-latin-wght-normal.woff2"],
  ["@fontsource-variable/inter", "inter-latin-wght-normal.woff2"],
  ...["400", "500", "600"].flatMap((w) =>
    ["normal", "italic"].map((s) => ["@fontsource/cormorant", `cormorant-latin-${w}-${s}.woff2`]),
  ),
];

for (const [pkg, file] of files) {
  copyFileSync(join(root, "node_modules", pkg, "files", file), join(out, file));
  console.log(`assets/fonts/${file}`);
}
