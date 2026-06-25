"use client";

import { motion, useReducedMotion } from "framer-motion";
import { EASE, viewportOnce } from "@/lib/anim";

type Tag = "h1" | "h2" | "h3";

// Heading whose words wipe up from behind a mask, one after another.
// The signature reveal of the page. Real text underneath, so it stays
// fully accessible; under reduced-motion it renders as a plain heading.
export default function AnimatedHeading({
  text,
  as = "h2",
  className = "",
  delay = 0,
  // Marks a run of words (by index) that should render in gold italic accent.
  accent,
}: {
  text: string;
  as?: Tag;
  className?: string;
  delay?: number;
  accent?: { from: number; to: number };
}) {
  const reduced = useReducedMotion();
  const words = text.split(" ");
  const MotionTag = motion[as];

  const isAccent = (i: number) =>
    accent ? i >= accent.from && i <= accent.to : false;

  if (reduced) {
    const Tag = as;
    return (
      <Tag className={className}>
        {words.map((w, i) => (
          <span key={i} className={isAccent(i) ? "accent text-gold" : undefined}>
            {w}
            {i < words.length - 1 ? " " : ""}
          </span>
        ))}
      </Tag>
    );
  }

  return (
    <MotionTag
      className={className}
      initial="hidden"
      whileInView="show"
      viewport={viewportOnce}
    >
      {words.map((w, i) => (
        <span
          key={i}
          className="inline-block overflow-hidden align-bottom"
          style={{ paddingBottom: "0.12em", marginBottom: "-0.12em" }}
        >
          <motion.span
            className={`inline-block ${isAccent(i) ? "accent text-gold" : ""}`}
            variants={{
              hidden: { y: "115%" },
              show: { y: "0%" },
            }}
            transition={{ duration: 0.95, ease: EASE, delay: delay + i * 0.07 }}
          >
            {w}
          </motion.span>
          {i < words.length - 1 ? " " : ""}
        </span>
      ))}
    </MotionTag>
  );
}
