"use client";

import { motion, useReducedMotion, useScroll, useSpring } from "framer-motion";

// Thin gold reading-progress bar fixed to the top of the viewport.
// Used on the homepage to reinforce the sense of a scroll journey.
export default function ScrollProgress() {
  const reduced = useReducedMotion();
  const { scrollYProgress } = useScroll();
  const smooth = useSpring(scrollYProgress, {
    stiffness: 120,
    damping: 30,
    mass: 0.3,
  });

  return (
    <motion.div
      aria-hidden
      style={{ scaleX: reduced ? scrollYProgress : smooth }}
      className="fixed inset-x-0 top-0 z-50 h-[3px] origin-left bg-gold"
    />
  );
}
