"use client";

import { motion, useReducedMotion } from "framer-motion";

// A quiet, continuously scrolling band of credentials. Adds the unmistakable
// "established house" texture without shouting. Pauses to a static, wrapping
// row under reduced motion.
export default function CredentialMarquee({ items }: { items: string[] }) {
  const reduced = useReducedMotion();

  if (reduced) {
    return (
      <div className="flex flex-wrap items-center justify-center gap-x-8 gap-y-3 px-6">
        {items.map((item) => (
          <span
            key={item}
            className="text-xs font-medium uppercase tracking-[0.22em] text-ivory/55"
          >
            {item}
          </span>
        ))}
      </div>
    );
  }

  // Duplicated track so the loop is seamless (translate by exactly one set).
  const track = [...items, ...items];

  return (
    <div
      className="group relative flex overflow-hidden"
      style={{
        maskImage:
          "linear-gradient(to right, transparent, black 12%, black 88%, transparent)",
        WebkitMaskImage:
          "linear-gradient(to right, transparent, black 12%, black 88%, transparent)",
      }}
    >
      <motion.div
        className="flex shrink-0 items-center"
        animate={{ x: ["0%", "-50%"] }}
        transition={{ duration: 36, ease: "linear", repeat: Infinity }}
      >
        {track.map((item, i) => (
          <span key={i} className="flex items-center">
            <span className="whitespace-nowrap px-7 text-xs font-medium uppercase tracking-[0.24em] text-ivory/55">
              {item}
            </span>
            <span aria-hidden className="h-1 w-1 rounded-full bg-gold/60" />
          </span>
        ))}
      </motion.div>
    </div>
  );
}
