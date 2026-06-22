// Portfolio / showroom projects.
// Used on home (featured grid), the filterable /showroom archive, and the
// per-project case-study pages at /showroom/[slug].

export type ProjectType = "Residential" | "Commercial" | "Hospitality";

export type Project = {
  slug: string;
  title: string;
  location: string;
  type: ProjectType;
  budget: string; // revealed on card hover to qualify leads
  year: string;
  image: string; // hero / card image
  blurb: string; // one-line for cards
  scope: string[]; // services delivered
  timeline: string;
  // Case-study narrative.
  challenge: string;
  solution: string;
  results: string;
  gallery: string[]; // additional images
  testimonial?: { quote: string; name: string };
};

const u = (id: string) =>
  `https://images.unsplash.com/${id}?auto=format&fit=crop&w=1400&q=80`;

export const projects: Project[] = [
  {
    slug: "ikoyi-penthouse",
    title: "Ikoyi Penthouse",
    location: "Ikoyi, Lagos",
    type: "Residential",
    budget: "₦80M – ₦120M",
    year: "2025",
    image: u("photo-1600607687939-ce8a6c25118c"),
    blurb: "A sky-high family residence wrapped in warm stone, brass and bespoke joinery.",
    scope: ["Residential Design", "Custom Furniture", "Lighting Design", "Project Management"],
    timeline: "9 months",
    challenge:
      "A newly handed-over penthouse shell with breathtaking views but no warmth, no storage strategy and an open plan that felt cavernous rather than calm.",
    solution:
      "We zoned the open plan into intimate living moments, introduced warm stone, brass and walnut joinery, and designed bespoke storage that disappears into the architecture. A layered lighting scheme lets the space shift from bright family mornings to soft evening entertaining.",
    results:
      "A residence that finally feels like home — tailored to the family's routines, flooded with light, and quietly spectacular. Delivered on time and fully furnished, ready to live in from day one.",
    gallery: [
      u("photo-1600210492486-724fe5c67fb0"),
      u("photo-1616594039964-ae9021a400a0"),
      u("photo-1616486338812-3dadae4b4ace"),
    ],
    testimonial: {
      quote: "They captured exactly how we wanted to live up here. Every guest asks who designed it.",
      name: "Homeowner, Ikoyi",
    },
  },
  {
    slug: "lekki-villa",
    title: "Lekki Villa",
    location: "Lekki, Lagos",
    type: "Residential",
    budget: "₦60M – ₦90M",
    year: "2024",
    image: u("photo-1600566753086-00f18fb6b3ea"),
    blurb: "Contemporary villa balancing open living with intimate, layered comfort.",
    scope: ["Residential Design", "Space Planning", "Furniture Sourcing", "Finishing"],
    timeline: "11 months",
    challenge:
      "A family of five needed a home that could host large gatherings yet still feel intimate day to day — across a layout that wasted space on circulation.",
    solution:
      "We reworked the plan to recover usable square metres, then built a material palette of natural oak, limewash and rattan for warmth. Flexible living spaces open up for entertaining and close down for quiet evenings.",
    results:
      "A villa that flexes with the family's life — generous when it needs to be, serene the rest of the time. Now a regular fixture in the family's hosting calendar.",
    gallery: [
      u("photo-1600585154340-be6161a56a0c"),
      u("photo-1616137466211-f939a420be84"),
      u("photo-1600121848594-d8644e57abab"),
    ],
    testimonial: {
      quote: "Our home finally works the way our family actually lives. Worth every naira.",
      name: "Homeowner, Lekki",
    },
  },
  {
    slug: "victoria-island-hq",
    title: "Victoria Island HQ",
    location: "Victoria Island, Lagos",
    type: "Commercial",
    budget: "₦120M – ₦180M",
    year: "2025",
    image: u("photo-1497366754035-f200968a6e72"),
    blurb: "An executive headquarters built to impress clients and inspire teams.",
    scope: ["Commercial Design", "Space Planning", "Custom Joinery", "Project Management"],
    timeline: "8 months",
    challenge:
      "A fast-growing firm had outgrown a tired office that no longer reflected its stature — or supported the way its people actually worked.",
    solution:
      "We designed a headquarters that balances prestige with productivity: a refined reception and boardroom for clients, plus bright, flexible work zones for focus and collaboration. Brand cues are woven into materials rather than stuck on walls.",
    results:
      "A workspace that wins the room before a word is spoken, and one the team is proud to come into. Delivered in phases to keep the business running throughout.",
    gallery: [
      u("photo-1604328698692-f76ea9498e76"),
      u("photo-1497215728101-856f4ea42174"),
      u("photo-1524758631624-e2822e304c36"),
    ],
    testimonial: {
      quote: "Clients notice the difference the moment they walk in. It reflects the company we've built.",
      name: "Chairman, V.I. Group",
    },
  },
  {
    slug: "boutique-hotel-suite",
    title: "Boutique Hotel Suites",
    location: "Ikeja, Lagos",
    type: "Hospitality",
    budget: "₦150M – ₦220M",
    year: "2024",
    image: u("photo-1582719478250-c89cae4dc85b"),
    blurb: "Signature suites where every detail rewards a returning guest.",
    scope: ["Hospitality Design", "Custom Furniture", "Lighting Design", "Finishing"],
    timeline: "12 months",
    challenge:
      "A boutique hotel wanted signature suites that could command premium rates and earn loyal, returning guests — without finishes that wouldn't survive heavy use.",
    solution:
      "We designed an immersive guest experience from threshold to nightstand, pairing rich, tactile materials with commercial-grade durability. Lighting, joinery and layout were tuned around how guests actually move through a suite.",
    results:
      "Suites that photograph beautifully, hold up to constant turnover, and keep guests coming back. A measurable lift in rate and repeat bookings.",
    gallery: [
      u("photo-1611892440504-42a792e24d32"),
      u("photo-1631049307264-da0ec9d70304"),
      u("photo-1578683010236-d716f9a3f461"),
    ],
    testimonial: {
      quote: "Guests now request these suites by name. The design pays for itself.",
      name: "General Manager, Ikeja",
    },
  },
  {
    slug: "banana-island-residence",
    title: "Banana Island Residence",
    location: "Banana Island, Lagos",
    type: "Residential",
    budget: "₦100M – ₦160M",
    year: "2025",
    image: u("photo-1600585154526-990dced4db0d"),
    blurb: "A waterfront home of quiet grandeur, light and natural materials.",
    scope: ["Residential Design", "Architecture Input", "Furniture Sourcing", "Lighting Design"],
    timeline: "14 months",
    challenge:
      "A waterfront property with extraordinary potential, but interiors that fought the views and felt heavier than the setting deserved.",
    solution:
      "We pared everything back to let light and water lead — pale stone, natural timber and a restrained palette. Sightlines were opened to the water, and statement pieces were chosen sparingly for maximum impact.",
    results:
      "A home of quiet grandeur that feels effortless and timeless. The interiors now amplify the setting rather than compete with it.",
    gallery: [
      u("photo-1600210491369-e753d80a41f3"),
      u("photo-1600607688969-a5bfcd646154"),
      u("photo-1600566752355-35792bedcfea"),
    ],
    testimonial: {
      quote: "They understood the house better than we did. It feels like it was always meant to look this way.",
      name: "Homeowner, Banana Island",
    },
  },
  {
    slug: "fine-dining-restaurant",
    title: "Fine-Dining Restaurant",
    location: "Victoria Island, Lagos",
    type: "Hospitality",
    budget: "₦90M – ₦130M",
    year: "2024",
    image: u("photo-1517248135467-4c7edcad34c4"),
    blurb: "An atmospheric dining room engineered for ambience and turnover alike.",
    scope: ["Hospitality Design", "Lighting Design", "Custom Joinery", "Project Management"],
    timeline: "7 months",
    challenge:
      "A new fine-dining concept needed a room that felt special enough to justify the price point, while still seating enough covers to be profitable.",
    solution:
      "We choreographed the guest journey and sightlines, used layered lighting to create intimacy at every table, and detailed durable, beautiful surfaces built for nightly service. Acoustics and flow were designed in from the start.",
    results:
      "A dining room with genuine atmosphere and the cover count to match. A destination from opening night.",
    gallery: [
      u("photo-1550966871-3ed3cdb5ed0c"),
      u("photo-1414235077428-338989a2e8c0"),
      u("photo-1559339352-11d035aa65de"),
    ],
    testimonial: {
      quote: "The room does half the selling for us. Guests feel the occasion the moment they sit down.",
      name: "Owner, V.I. Restaurant",
    },
  },
];

export const featuredProjects = projects;

export function getProject(slug: string) {
  return projects.find((p) => p.slug === slug);
}

// Previous / next for in-page navigation between case studies.
export function adjacentProjects(slug: string) {
  const i = projects.findIndex((p) => p.slug === slug);
  if (i === -1) return { prev: undefined, next: undefined };
  const prev = projects[(i - 1 + projects.length) % projects.length];
  const next = projects[(i + 1) % projects.length];
  return { prev, next };
}

export const projectTypes: ProjectType[] = ["Residential", "Commercial", "Hospitality"];
