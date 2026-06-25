"use client";

import { useEffect, useRef, useState } from "react";
import {
  animate,
  motion,
  useInView,
  useReducedMotion,
} from "framer-motion";
import { EASE } from "@/lib/anim";

// Counts a numeric value up when scrolled into view. Any non-numeric prefix or
// suffix in `value` (e.g. "₦", "+", "Lagos") is preserved and rendered static.
// Under reduced motion the final value is shown immediately.
export default function Counter({
  value,
  className = "",
}: {
  value: string;
  className?: string;
}) {
  const reduced = useReducedMotion();
  const ref = useRef<HTMLSpanElement>(null);
  const inView = useInView(ref, { once: true, amount: 0.6 });

  const match = value.match(/^(\D*)(\d[\d,]*)(.*)$/);
  const prefix = match?.[1] ?? "";
  const target = match ? Number(match[2].replace(/,/g, "")) : null;
  const suffix = match?.[3] ?? "";

  const [display, setDisplay] = useState(
    reduced || target === null ? (match ? match[2] : value) : "0",
  );

  useEffect(() => {
    if (reduced || target === null || !inView) return;
    const controls = animate(0, target, {
      duration: 1.6,
      ease: EASE,
      onUpdate: (v) => setDisplay(Math.round(v).toLocaleString()),
    });
    return () => controls.stop();
  }, [inView, reduced, target]);

  // Non-numeric values (e.g. "Lagos") simply fade in.
  if (target === null) {
    return (
      <motion.span
        ref={ref}
        className={className}
        initial={reduced ? false : { opacity: 0, y: 12 }}
        animate={inView ? { opacity: 1, y: 0 } : undefined}
        transition={{ duration: 0.7, ease: EASE }}
      >
        {value}
      </motion.span>
    );
  }

  return (
    <span ref={ref} className={className}>
      {prefix}
      {display}
      {suffix}
    </span>
  );
}
