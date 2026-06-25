"use client";

import { motion } from "framer-motion";
import { Container } from "@/components/ui";
import AnimatedHeading from "@/components/home/AnimatedHeading";
import { rise, stagger, transition, viewportOnce } from "@/lib/anim";

const valueProps = [
  {
    title: "Bespoke by Design",
    body: "No templates, no compromises — only your vision, drawn out and perfected.",
  },
  {
    title: "Turnkey Execution",
    body: "Concept to completion under one roof. We handle absolutely everything.",
  },
  {
    title: "Luxury Without the Stress",
    body: "A serene, documented process built around busy, accomplished lives.",
  },
];

// Editorial two-column ethos. A confident statement on the left, the value
// pillars rendered as a hairline-ruled ledger on the right.
export default function Ethos() {
  return (
    <section className="bg-ivory py-24 md:py-32">
      <Container>
        <div className="grid gap-14 lg:grid-cols-[1.05fr_0.95fr] lg:gap-20">
          {/* Statement */}
          <div className="lg:sticky lg:top-32 lg:self-start">
            <motion.span
              initial={{ opacity: 0 }}
              whileInView={{ opacity: 1 }}
              viewport={viewportOnce}
              transition={transition}
              className="eyebrow text-gold"
            >
              Why It Matters
            </motion.span>

            <AnimatedHeading
              text="Your space should reflect how far you've come"
              accent={{ from: 4, to: 7 }}
              className="mt-7 text-3xl text-charcoal sm:text-4xl lg:text-[2.9rem] lg:leading-[1.08]"
            />

            <motion.p
              initial={{ opacity: 0, y: 18 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={viewportOnce}
              transition={{ ...transition, delay: 0.15 }}
              className="mt-7 max-w-md text-lg text-muted"
            >
              You&apos;ve worked hard for your success. Your home or office should
              tell that story — with clarity, warmth and undeniable presence.
            </motion.p>

            <motion.p
              initial={{ opacity: 0, y: 18 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={viewportOnce}
              transition={{ ...transition, delay: 0.25 }}
              className="accent mt-8 max-w-md text-2xl leading-snug text-charcoal/80"
            >
              We design the few square metres where your real life actually
              happens.
            </motion.p>
          </div>

          {/* Pillars */}
          <motion.ol
            variants={stagger(0.12)}
            initial="hidden"
            whileInView="show"
            viewport={viewportOnce}
            className="flex flex-col"
          >
            {valueProps.map((v, i) => (
              <motion.li
                key={v.title}
                variants={rise}
                className="group grid grid-cols-[auto_1fr] gap-6 border-t border-line py-8 last:border-b"
              >
                <span className="font-serif text-2xl text-gold tabular-nums">
                  0{i + 1}
                </span>
                <div>
                  <h3 className="text-xl text-charcoal transition-colors duration-300 group-hover:text-gold">
                    {v.title}
                  </h3>
                  <p className="mt-3 text-muted">{v.body}</p>
                </div>
              </motion.li>
            ))}
          </motion.ol>
        </div>
      </Container>
    </section>
  );
}
