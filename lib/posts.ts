// Blog posts. Two sample posts ship as local data; swap for a CMS later.

export type Post = {
  slug: string;
  title: string;
  category: string;
  excerpt: string;
  date: string; // ISO
  readTime: string;
  image: string;
  // Body as an array of paragraphs / headings for simple rendering.
  body: { type: "h2" | "p"; text: string }[];
};

const u = (id: string) =>
  `https://images.unsplash.com/${id}?auto=format&fit=crop&w=1400&q=80`;

export const posts: Post[] = [
  {
    slug: "7-secrets-of-luxury-interior-design-nigeria",
    title: "The 7 Secrets of Luxury Interior Design in Nigeria",
    category: "Design Tips",
    excerpt:
      "What separates a merely expensive interior from a truly luxurious one? Seven principles our most discerning clients live by.",
    date: "2026-05-28",
    readTime: "8 min read",
    image: u("photo-1618221195710-dd6b41faaea6"),
    body: [
      { type: "p", text: "Luxury is rarely about how much you spend — it's about how deliberately you spend it. After fifteen years designing for Nigeria's most accomplished homeowners, we've distilled the difference into seven principles." },
      { type: "h2", text: "1. Restraint is the ultimate flex" },
      { type: "p", text: "The most luxurious rooms are not the busiest. Negative space — room to breathe around each piece — signals confidence. Editing is harder than adding, and far more impressive." },
      { type: "h2", text: "2. Invest where you touch" },
      { type: "p", text: "Door handles, taps, light switches, upholstery. The surfaces your hands meet every day are where quality is felt, not just seen. Spend there first." },
      { type: "h2", text: "3. Light in layers" },
      { type: "p", text: "A single ceiling light flattens a room. Ambient, task and accent lighting — on separate circuits — let a space shift from morning brief to evening soirée." },
      { type: "h2", text: "4. Buy fewer, better things" },
      { type: "p", text: "One exceptional sofa outlasts and outclasses three forgettable ones. Curation beats accumulation every time." },
      { type: "h2", text: "5. Let materials age gracefully" },
      { type: "p", text: "Natural stone, solid timber, brass, leather — materials that develop character rather than wear out. Luxury endures." },
      { type: "h2", text: "6. Design for how you actually live" },
      { type: "p", text: "A show home you can't relax in isn't luxury — it's a museum. The best spaces are tailored to your real routines, not a magazine's." },
      { type: "h2", text: "7. Hire a team that handles everything" },
      { type: "p", text: "The final secret: true luxury is the absence of stress. A turnkey partner who manages concept to completion is the difference between a project you endure and one you enjoy." },
    ],
  },
  {
    slug: "how-much-luxury-interior-design-costs-lagos",
    title: "How Much Does Luxury Interior Design Cost in Lagos?",
    category: "Materials",
    excerpt:
      "An honest, practical guide to budgeting for a high-end interior project in Lagos — and where the money actually goes.",
    date: "2026-05-12",
    readTime: "7 min read",
    image: u("photo-1556909212-d5b604d0c90d"),
    body: [
      { type: "p", text: "It's the question every prospective client wants answered but few designers address directly. Here's a transparent look at what a luxury interior project costs in Lagos, and why." },
      { type: "h2", text: "What drives the budget" },
      { type: "p", text: "Three factors dominate: the scale of works (cosmetic refresh vs. full renovation), the quality tier of finishes and furniture, and how much is bespoke versus sourced. A penthouse refresh and a villa rebuild sit at very different points." },
      { type: "h2", text: "Indicative ranges" },
      { type: "p", text: "A considered full-home design typically begins around ₦25M and scales with size and ambition. Commercial fit-outs and hospitality projects, with their commercial-grade detailing, start higher. Standalone services — colour, lighting, custom furniture — let you invest selectively." },
      { type: "h2", text: "Why transparency matters" },
      { type: "p", text: "Surprises erode trust. We document budgets clearly, flag trade-offs early, and give you the information to make confident decisions. You should always know what you're paying for." },
      { type: "h2", text: "The real return" },
      { type: "p", text: "Beyond the obvious — comfort, beauty, status — a well-designed space saves you time, reduces stress and, for property, lifts value. The best interiors pay you back daily." },
    ],
  },
  {
    slug: "2026-interior-design-trends-nigerian-homes",
    title: "2026 Interior Design Trends for Nigerian Homes",
    category: "Trends",
    excerpt:
      "From warm minimalism to locally-sourced craft, the directions defining the most beautiful Nigerian homes this year.",
    date: "2026-04-30",
    readTime: "6 min read",
    image: u("photo-1493809842364-78817add7ffb"),
    body: [
      { type: "p", text: "Design moves in cycles, but the strongest trends reflect how we want to live. Here are the directions shaping Nigeria's finest homes in 2026." },
      { type: "h2", text: "Warm minimalism" },
      { type: "p", text: "Pared-back but never cold. Think earthy palettes, tactile textures and a few exceptional pieces — minimalism with a Nigerian warmth." },
      { type: "h2", text: "Local craft, global standard" },
      { type: "p", text: "Homeowners increasingly want pieces with provenance — Nigerian artisans and materials, executed to international standards." },
      { type: "h2", text: "Quiet luxury lighting" },
      { type: "p", text: "Sculptural, dimmable, scene-based. Lighting is becoming the statement, not the afterthought." },
      { type: "h2", text: "Rooms that flex" },
      { type: "p", text: "As work and life blend, spaces that adapt — a study that becomes a lounge — are in high demand." },
    ],
  },
];

export function getPost(slug: string) {
  return posts.find((p) => p.slug === slug);
}
