// Service catalogue. Drives the /services overview grid, the [slug] template,
// the header dropdown, and the home "Services Overview" section.

export type Service = {
  slug: string;
  title: string;
  short: string; // one-line for cards
  overview: string[]; // paragraphs for the detail page
  process: { step: string; detail: string }[];
  investmentFrom: string; // e.g. "₦15M"
  faqs: { q: string; a: string }[];
  keywords: string[]; // SEO long-tail
  image: string; // Unsplash
  featured?: boolean; // shown on home page
};

const u = (id: string) =>
  `https://images.unsplash.com/${id}?auto=format&fit=crop&w=1400&q=80`;

export const services: Service[] = [
  {
    slug: "residential-interior-design",
    title: "Residential Interior Design",
    short: "Luxury homes, penthouses and villas tailored to how you live.",
    overview: [
      "We design private residences for accomplished individuals who expect their homes to reflect the lives they have built. From Ikoyi penthouses to Lekki villas, every project begins with a deep understanding of your lifestyle, taste and routines.",
      "Our residential practice brings together architecture, interiors, furniture and styling into one seamless vision — so the result feels considered in every detail, never assembled from parts.",
    ],
    process: [
      { step: "Lifestyle Audit", detail: "We map how you actually live, host and unwind." },
      { step: "Concept & 3D", detail: "Mood, materials and photoreal renders before a single order." },
      { step: "Sourcing", detail: "Curated furniture and finishes, locally and internationally." },
      { step: "Execution", detail: "Managed build, install and quality control." },
      { step: "Reveal", detail: "Final styling, walkthrough and lifetime aftercare." },
    ],
    investmentFrom: "₦25M",
    faqs: [
      { q: "Do you take on single rooms?", a: "We focus on whole-home and multi-room projects, but we'll happily advise on the right scope during your consultation." },
      { q: "Can you work with my architect?", a: "Yes — we regularly collaborate with architects and contractors already engaged on a project." },
    ],
    keywords: ["luxury home interior designer Lagos", "bespoke residential design Nigeria"],
    image: u("photo-1616594039964-ae9021a400a0"),
    featured: true,
  },
  {
    slug: "commercial-interior-design",
    title: "Commercial Interior Design",
    short: "Corporate offices, executive suites and boardrooms that command respect.",
    overview: [
      "Your workspace is a statement of intent. We design corporate environments that reinforce your brand, support your people and impress every visitor who walks through the door.",
      "From headquarters to executive floors, we balance prestige with productivity — spaces that look exceptional and work even harder.",
    ],
    process: [
      { step: "Brand & Brief", detail: "Align the space with your culture and identity." },
      { step: "Space Planning", detail: "Optimised layouts for flow and focus." },
      { step: "Design & Spec", detail: "Materials, joinery and furniture systems." },
      { step: "Fit-out", detail: "Coordinated delivery with minimal downtime." },
    ],
    investmentFrom: "₦40M",
    faqs: [
      { q: "Can you work around an operating office?", a: "Yes, we phase fit-outs to keep disruption to your team to a minimum." },
    ],
    keywords: ["corporate interior design Lagos", "office design Nigeria"],
    image: u("photo-1604328698692-f76ea9498e76"),
    featured: true,
  },
  {
    slug: "hospitality-retail-design",
    title: "Hospitality & Retail Design",
    short: "Hotels, restaurants and boutiques that turn visitors into devotees.",
    overview: [
      "In hospitality and retail, atmosphere is revenue. We craft immersive environments that elevate the guest experience and keep people coming back.",
      "Every finish, every light, every sightline is designed to tell your story and protect your margins.",
    ],
    process: [
      { step: "Concept", detail: "A signature narrative for the space." },
      { step: "Guest Journey", detail: "Designing every touchpoint and sightline." },
      { step: "Design & Spec", detail: "Durable, beautiful, commercial-grade detailing." },
      { step: "Delivery", detail: "On-time fit-out built for high traffic." },
    ],
    investmentFrom: "₦50M",
    faqs: [
      { q: "Do you design for franchises?", a: "Yes — we can develop roll-out-ready design systems for multi-site brands." },
    ],
    keywords: ["hotel interior design Nigeria", "retail space designer Lagos"],
    image: u("photo-1551632436-cbf8dd35adfa"),
    featured: true,
  },
  {
    slug: "space-planning-architecture",
    title: "Space Planning & Architecture",
    short: "Layout optimisation and architectural modifications that unlock potential.",
    overview: [
      "Great interiors start with great bones. We rethink layouts, flow and proportion to make spaces feel larger, brighter and more purposeful.",
      "From wall reconfigurations to full architectural input, we ensure form and function move as one.",
    ],
    process: [
      { step: "Survey", detail: "Measure, assess and document the existing space." },
      { step: "Options", detail: "Multiple layout strategies to compare." },
      { step: "Resolution", detail: "Detailed plans ready for build." },
    ],
    investmentFrom: "₦8M",
    faqs: [
      { q: "Is this only for new builds?", a: "No — most of our space-planning work is reconfiguring existing properties." },
    ],
    keywords: ["space planning services Lagos"],
    image: u("photo-1503387762-592deb58ef4e"),
  },
  {
    slug: "furniture-decor-sourcing",
    title: "Furniture & Décor Sourcing",
    short: "Curated local and international procurement, including custom pieces.",
    overview: [
      "Our global network gives you access to furniture houses, artisans and ateliers most clients never reach. We source, negotiate and manage logistics end to end.",
      "Whether it's a statement chandelier or a full home of furnishings, we handle the detail so the pieces simply arrive — perfect.",
    ],
    process: [
      { step: "Curate", detail: "Selections aligned to your scheme and budget." },
      { step: "Procure", detail: "Ordering, customs and logistics handled." },
      { step: "Install", detail: "White-glove delivery and placement." },
    ],
    investmentFrom: "₦10M",
    faqs: [
      { q: "Can you source a specific designer brand?", a: "Very likely — share the brand and we'll confirm availability and lead times." },
    ],
    keywords: ["luxury furniture sourcing Nigeria"],
    image: u("photo-1567016432779-094069958ea5"),
  },
  {
    slug: "project-management-execution",
    title: "Project Management & Execution",
    short: "Contractor coordination, timelines and quality control — handled.",
    overview: [
      "The difference between a good design and a flawless home is execution. We manage contractors, timelines, budgets and quality so you never have to chase anyone.",
      "You get clear documentation, regular updates and a single point of accountability from start to finish.",
    ],
    process: [
      { step: "Plan", detail: "Programme, budget and responsibilities agreed." },
      { step: "Coordinate", detail: "Trades and deliveries orchestrated." },
      { step: "Control", detail: "Quality checks at every milestone." },
    ],
    investmentFrom: "Quoted per project",
    faqs: [
      { q: "Can you manage a project we designed elsewhere?", a: "Yes, we can take on delivery of an existing design package." },
    ],
    keywords: ["interior design project management Lagos"],
    image: u("photo-1581094794329-c8112a89af12"),
  },
  {
    slug: "interior-exterior-finishing",
    title: "Interior & Exterior Finishing",
    short: "Flooring, wall treatments, ceilings and facades, beautifully resolved.",
    overview: [
      "Finishes are where luxury is felt. We specify and deliver flooring, joinery, wall treatments, ceilings and facades with an obsession for craftsmanship.",
      "The result is a space that reads as effortless precision in every surface.",
    ],
    process: [
      { step: "Specify", detail: "Materials selected for beauty and longevity." },
      { step: "Sample", detail: "Approve finishes before commitment." },
      { step: "Apply", detail: "Skilled trades, supervised closely." },
    ],
    investmentFrom: "₦12M",
    faqs: [
      { q: "Do you handle exteriors too?", a: "Yes — facades, cladding and external finishes are part of our scope." },
    ],
    keywords: ["luxury finishing services Nigeria"],
    image: u("photo-1600585154340-be6161a56a0c"),
  },
  {
    slug: "real-estate-staging",
    title: "Real Estate Staging",
    short: "Staging that helps developers sell faster, at stronger prices.",
    overview: [
      "Empty spaces are hard to sell. We stage properties so prospective buyers can feel the lifestyle on offer — accelerating sales and lifting perceived value.",
      "Ideal for developers, agents and owners preparing premium properties for market.",
    ],
    process: [
      { step: "Assess", detail: "Identify the target buyer and the story to tell." },
      { step: "Style", detail: "Furnish and dress for maximum impact." },
      { step: "Show", detail: "Photo-ready, viewing-ready spaces." },
    ],
    investmentFrom: "₦6M",
    faqs: [
      { q: "Do you offer rental furniture for staging?", a: "Yes, we offer both rental and purchase staging packages." },
    ],
    keywords: ["luxury home staging Lagos"],
    image: u("photo-1600210492486-724fe5c67fb0"),
  },
  {
    slug: "renovation-remodeling",
    title: "Renovation & Remodeling",
    short: "Full-scale renovations and heritage restorations, end to end.",
    overview: [
      "Transform an existing property into something extraordinary. We handle full renovations and sensitive restorations with the same rigour as a new build.",
      "From structural changes to the final cushion, we manage every phase.",
    ],
    process: [
      { step: "Audit", detail: "Understand the property and its possibilities." },
      { step: "Design", detail: "A renovation scheme with clear costings." },
      { step: "Rebuild", detail: "Managed works to a fixed programme." },
      { step: "Finish", detail: "Styling and handover." },
    ],
    investmentFrom: "₦30M",
    faqs: [
      { q: "Can you renovate while we live in the property?", a: "Where feasible we phase works; often a short decant delivers a better result." },
    ],
    keywords: ["luxury home renovation Nigeria"],
    image: u("photo-1574359411659-15573a27fd0c"),
  },
  {
    slug: "custom-furniture-design",
    title: "Custom Furniture Design",
    short: "Bespoke pieces, built-ins and millwork made for your space.",
    overview: [
      "When nothing off-the-shelf will do, we design and commission bespoke furniture and joinery — tailored to your dimensions, materials and taste.",
      "Every piece is a one-off, crafted by artisans we trust.",
    ],
    process: [
      { step: "Design", detail: "Sketches and technical drawings." },
      { step: "Prototype", detail: "Materials and finishes confirmed." },
      { step: "Craft", detail: "Made and installed to order." },
    ],
    investmentFrom: "₦4M",
    faqs: [
      { q: "How long does a bespoke piece take?", a: "Typically 6–12 weeks depending on complexity and materials." },
    ],
    keywords: ["custom furniture designer Lagos"],
    image: u("photo-1538688525198-9b88f6f53126"),
  },
  {
    slug: "color-consultation",
    title: "Color Consultation",
    short: "Palette development and mood boards that set the right tone.",
    overview: [
      "Colour shapes how a space feels. Our consultations develop bespoke palettes and mood boards that bring harmony, warmth and sophistication to your interiors.",
      "Perfect as a standalone service or the foundation of a larger project.",
    ],
    process: [
      { step: "Discover", detail: "Understand mood, light and intent." },
      { step: "Develop", detail: "Curated palettes and material pairings." },
      { step: "Deliver", detail: "A clear scheme you can act on." },
    ],
    investmentFrom: "₦1.5M",
    faqs: [
      { q: "Will you specify exact paint products?", a: "Yes — you receive named colours and product references." },
    ],
    keywords: ["interior color consultant Nigeria"],
    image: u("photo-1513161455079-7dc1de15ef3e"),
  },
  {
    slug: "lighting-design",
    title: "Lighting Design",
    short: "Ambient, task and accent lighting plans that transform a room.",
    overview: [
      "Lighting is the most underrated luxury. We design layered lighting schemes — ambient, task and accent — that flatter your space and your guests.",
      "From fixtures to controls, we deliver atmosphere on demand.",
    ],
    process: [
      { step: "Plan", detail: "Lighting layers mapped to how you use each room." },
      { step: "Specify", detail: "Fixtures, temperatures and controls selected." },
      { step: "Commission", detail: "Installed, focused and scene-set." },
    ],
    investmentFrom: "₦5M",
    faqs: [
      { q: "Do you handle smart lighting controls?", a: "Yes — we design and specify scene-based and app-controlled systems." },
    ],
    keywords: ["luxury lighting design Lagos"],
    image: u("photo-1524634126442-357e0eac3c14"),
  },
];

export function getService(slug: string) {
  return services.find((s) => s.slug === slug);
}

export const featuredServices = services.filter((s) => s.featured);
