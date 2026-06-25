"use client";

import { motion } from "framer-motion";
import { rise, stagger } from "@/lib/anim";
import { methodActs } from "@/lib/method";

// The Interior Specifics Method™ — five-step signature process, revealed as a
// staggered row of hairline-separated cards.
export default function ProcessTimeline() {
  return (
    <motion.ol
      variants={stagger(0.1)}
      initial="hidden"
      whileInView="show"
      viewport={{ once: true, amount: 0.15 }}
      className="grid gap-px overflow-hidden border border-line bg-line md:grid-cols-5"
    >
      {methodActs.map((s) => (
        <motion.li
          key={s.n}
          variants={rise}
          className="group flex h-full flex-col gap-3 bg-ivory p-7 transition-colors duration-300 hover:bg-white"
        >
          <span className="font-serif text-3xl text-gold">{s.n}</span>
          <h3 className="text-sm uppercase tracking-[0.14em] text-charcoal">
            {s.title}
          </h3>
          <p className="text-sm text-muted">{s.detail}</p>
        </motion.li>
      ))}
    </motion.ol>
  );
}
