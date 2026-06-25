// The Interior Specifics Method™ — five acts.
// Single source of truth, consumed by the homepage MethodJourney (scrollytelling)
// and the ProcessTimeline component on the About page.

export type MethodAct = {
  n: string;
  title: string;
  detail: string; // short line (timeline / cards)
  narrative: string; // longer line (scroll story)
  image: string;
};

const u = (id: string) =>
  `https://images.unsplash.com/${id}?auto=format&fit=crop&w=2000&q=80`;

export const methodActs: MethodAct[] = [
  {
    n: "01",
    title: "Discover",
    detail: "Deep-dive consultation, lifestyle audit and vision mapping.",
    narrative:
      "Every project begins with you. We listen, audit how you live and work, and map a vision worthy of your space.",
    image: u("photo-1503387762-592deb58ef4e"),
  },
  {
    n: "02",
    title: "Design",
    detail: "Concept development, 3D renderings and material selection.",
    narrative:
      "Your vision becomes tangible — concepts, photoreal renders and curated materials you can see and feel before a single decision is final.",
    image: u("photo-1618221195710-dd6b41faaea6"),
  },
  {
    n: "03",
    title: "Source",
    detail: "Curated furniture, artisan partnerships and global procurement.",
    narrative:
      "We reach the furniture houses, artisans and ateliers most never access — sourcing, negotiating and managing every piece to your scheme.",
    image: u("photo-1567016432779-094069958ea5"),
  },
  {
    n: "04",
    title: "Execute",
    detail: "Project management, quality control and timeline adherence.",
    narrative:
      "We orchestrate trades, deliveries, budgets and quality — a single point of accountability so the build runs flawlessly while you simply wait.",
    image: u("photo-1581094794329-c8112a89af12"),
  },
  {
    n: "05",
    title: "Reveal",
    detail: "Final styling, walkthrough and lifetime support.",
    narrative:
      "The doors open on a space that honours your journey — styled to the last detail, with our care continuing long after the reveal.",
    image: u("photo-1616594039964-ae9021a400a0"),
  },
];
