"use client";

import { motion } from "framer-motion";
import { Container } from "@/components/ui";
import Counter from "@/components/home/Counter";
import { rise, stagger, viewportOnce } from "@/lib/anim";

const stats = [
  { value: "15+", label: "Years of practice" },
  { value: "200+", label: "Projects delivered" },
  { value: "12", label: "In-house disciplines" },
  { value: "100%", label: "Turnkey delivery" },
];

// A slim band of headline numbers that count up on view — a single beat of
// proof between the work and the reasons to trust it.
export default function StatsBand() {
  return (
    <section className="grain relative border-y border-line-light bg-charcoal py-16 md:py-20">
      <Container>
        <motion.dl
          variants={stagger(0.12)}
          initial="hidden"
          whileInView="show"
          viewport={viewportOnce}
          className="grid grid-cols-2 gap-y-10 sm:grid-cols-4"
        >
          {stats.map((s, i) => (
            <motion.div
              key={s.label}
              variants={rise}
              className={`px-2 text-center ${
                i > 0 ? "sm:border-l sm:border-line-light" : ""
              }`}
            >
              <dt className="sr-only">{s.label}</dt>
              <dd>
                <Counter
                  value={s.value}
                  className="block font-serif text-4xl text-gold md:text-5xl lg:text-[3.25rem]"
                />
                <span className="mt-3 block text-xs uppercase tracking-[0.18em] text-ivory/60">
                  {s.label}
                </span>
              </dd>
            </motion.div>
          ))}
        </motion.dl>
      </Container>
    </section>
  );
}
