"use client";

import { motion } from "framer-motion";
import { Container } from "@/components/ui";
import AnimatedHeading from "@/components/home/AnimatedHeading";
import { rise, stagger } from "@/lib/anim";

const reasons = [
  {
    title: "End-to-End Service",
    body: "Concept, design, sourcing, build and styling — orchestrated under one roof, by one accountable team.",
  },
  {
    title: "A Global Network",
    body: "Direct access to international furniture houses, ateliers and artisans most clients never reach.",
  },
  {
    title: "A Transparent Process",
    body: "Clear timelines, documented budgets and honest counsel. You always know what you're paying for.",
  },
  {
    title: "Lifetime Aftercare",
    body: "We remain on hand long after the reveal — because the relationship outlasts the project.",
  },
];

// Reasons to trust the house, presented as a full-width ledger of hairline
// rows that warm to gold on hover.
export default function WhyLedger() {
  return (
    <section className="bg-ivory py-24 md:py-32">
      <Container>
        <div className="max-w-2xl">
          <span className="eyebrow text-gold">The Difference</span>
          <AnimatedHeading
            text="Why Nigeria's elite choose Interior Specifics"
            accent={{ from: 4, to: 5 }}
            className="mt-6 text-3xl text-charcoal sm:text-4xl lg:text-[2.75rem]"
          />
        </div>

        <motion.ul
          variants={stagger(0.1)}
          initial="hidden"
          whileInView="show"
          viewport={{ once: true, amount: 0.15 }}
          className="mt-14 border-t border-line"
        >
          {reasons.map((r, i) => (
            <motion.li key={r.title} variants={rise} className="border-b border-line">
              <div className="group grid items-baseline gap-x-8 gap-y-3 py-9 transition-colors duration-300 md:grid-cols-[6rem_1fr_1.4fr] md:py-10">
                <span className="font-serif text-3xl text-gold/70 tabular-nums transition-colors duration-300 group-hover:text-gold md:text-4xl">
                  0{i + 1}
                </span>
                <h3 className="text-xl text-charcoal transition-colors duration-300 group-hover:text-gold md:text-2xl">
                  {r.title}
                </h3>
                <p className="text-muted md:pl-6">{r.body}</p>
              </div>
            </motion.li>
          ))}
        </motion.ul>
      </Container>
    </section>
  );
}
