// Central site configuration: brand, contact, navigation, socials.
// TODO(integrations): replace placeholder values with real accounts before launch.

export const site = {
  name: "Interior Specifics",
  legalName: "Interior Specifics Ltd.",
  tagline: "Designing Spaces Worthy of Achievement",
  secondaryTagline: "The Intersection of Success and Sanctuary",
  description:
    "Bespoke luxury interior design for Nigeria's most discerning homeowners and businesses. Concept to completion, under one roof.",
  url: "https://interiorspecifics.com",
  foundedYear: 2009,

  // Contact / NAP — keep consistent everywhere for local SEO.
  email: "info@interiorspecifics.com",
  phoneDisplay: "+234 801 234 5678",
  phoneHref: "+2348012345678",
  // TODO(integrations): set the real WhatsApp business number (digits only, country code).
  whatsapp: "2348012345678",
  whatsappMessage: "Hello Interior Specifics, I'd like to discuss a project.",
  address: {
    street: "12 Bourdillon Road, Ikoyi",
    city: "Lagos",
    country: "Nigeria",
  },
  hours: "Mon–Fri: 9am–6pm · Sat: 10am–4pm",

  // TODO(integrations): real links.
  calendlyUrl: "#book", // replace with https://calendly.com/your-handle
  gaMeasurementId: "", // e.g. "G-XXXXXXX"

  socials: {
    instagram: "https://instagram.com/",
    linkedin: "https://linkedin.com/",
    pinterest: "https://pinterest.com/",
    youtube: "https://youtube.com/",
  },

  stats: [
    { value: "15+", label: "Years" },
    { value: "200+", label: "Projects" },
    { value: "Lagos", label: "Premier Design Firm" },
  ],
};

export function whatsappLink() {
  return `https://wa.me/${site.whatsapp}?text=${encodeURIComponent(
    site.whatsappMessage,
  )}`;
}

export type NavChild = { label: string; href: string };
export type NavItem = { label: string; href: string; children?: NavChild[] };

export const nav: NavItem[] = [
  { label: "Home", href: "/" },
  {
    label: "About",
    href: "/about",
    children: [
      { label: "Directors", href: "/about#directors" },
      { label: "Company Profile", href: "/about#profile" },
      { label: "Awards & Recognition", href: "/about#awards" },
      { label: "Clients & Case Studies", href: "/about#clients" },
    ],
  },
  {
    label: "Showroom",
    href: "/showroom",
    children: [
      { label: "All Projects", href: "/showroom" },
      { label: "Residential", href: "/showroom?type=Residential" },
      { label: "Commercial", href: "/showroom?type=Commercial" },
      { label: "Hospitality", href: "/showroom?type=Hospitality" },
    ],
  },
  {
    label: "Services",
    href: "/services",
    // children populated from services data in the Header to avoid duplication
  },
  { label: "Blog", href: "/blog" },
  { label: "Contact", href: "/contact" },
];
