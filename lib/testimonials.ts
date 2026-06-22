// Client testimonials for the home carousel and service pages.

export type Testimonial = {
  quote: string;
  name: string;
  location: string;
  project: string;
};

export const testimonials: Testimonial[] = [
  {
    quote:
      "They didn't just design our home. They captured who we are. Every guest now asks who was behind it.",
    name: "Mrs. Adebayo",
    location: "Lekki, Lagos",
    project: "Full Home Design",
  },
  {
    quote:
      "From the first consultation to the final walkthrough, the process was seamless. Exactly what a busy executive needs.",
    name: "Mr. Okonkwo",
    location: "Ikoyi, Lagos",
    project: "Penthouse Renovation",
  },
  {
    quote:
      "Our headquarters finally reflects the company we've built. Clients notice the difference the moment they arrive.",
    name: "Chairman, V.I. Group",
    location: "Victoria Island, Lagos",
    project: "Corporate Office",
  },
  {
    quote:
      "Impeccable taste, flawless execution, and a team that genuinely cared. Worth every naira.",
    name: "Dr. Bello",
    location: "Banana Island, Lagos",
    project: "Waterfront Residence",
  },
  {
    quote:
      "They sourced pieces I could never have found on my own. The result feels truly one of a kind.",
    name: "Mrs. Eze",
    location: "Ikeja GRA, Lagos",
    project: "Furniture & Décor Sourcing",
  },
];
