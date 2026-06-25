"use client";

import { motion } from "framer-motion";
import AnimatedHeading from "@/components/home/AnimatedHeading";
import { transition, viewportOnce } from "@/lib/anim";

// Animated drop-in replacement for SectionHeading: an eyebrow with a gold tick,
// a masked word-by-word heading reveal, a gold rule, and an optional intro.
// Shares the homepage motion language so every page reads as one system.
export default function RevealHeading({
  eyebrow,
  title,
  intro,
  align = "center",
  light = false,
  accent,
}: {
  eyebrow?: string;
  title: string;
  intro?: string;
  align?: "center" | "left";
  light?: boolean;
  accent?: { from: number; to: number };
}) {
  const center = align === "center";
  return (
    <div
      className={`flex flex-col gap-5 ${
        center ? "items-center text-center" : "items-start text-left"
      }`}
    >
      {eyebrow && (
        <motion.span
          initial={{ opacity: 0 }}
          whileInView={{ opacity: 1 }}
          viewport={viewportOnce}
          transition={transition}
          className={`eyebrow ${center ? "eyebrow--center justify-center" : ""} ${
            light ? "text-gold-soft" : "text-gold"
          }`}
        >
          {eyebrow}
        </motion.span>
      )}

      <AnimatedHeading
        text={title}
        accent={accent}
        className={`max-w-3xl text-3xl md:text-4xl lg:text-[2.75rem] ${
          light ? "text-ivory" : "text-charcoal"
        }`}
      />

      <div className={`gold-rule ${center ? "mx-auto" : ""}`} />

      {intro && (
        <motion.p
          initial={{ opacity: 0, y: 16 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={viewportOnce}
          transition={{ ...transition, delay: 0.15 }}
          className={`max-w-2xl text-base md:text-lg ${
            light ? "text-ivory/80" : "text-muted"
          }`}
        >
          {intro}
        </motion.p>
      )}
    </div>
  );
}
