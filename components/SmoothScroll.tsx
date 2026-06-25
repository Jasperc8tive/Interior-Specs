"use client";

import { useEffect } from "react";
import { useReducedMotion } from "framer-motion";
import Lenis from "lenis";

// App-wide smooth scrolling via Lenis. Uses native window scroll under the hood,
// so Framer Motion's useScroll and `position: sticky` keep working.
// Disabled entirely when the user prefers reduced motion.
export default function SmoothScroll({ children }: { children: React.ReactNode }) {
  const prefersReduced = useReducedMotion();

  useEffect(() => {
    if (prefersReduced) return;

    const lenis = new Lenis({
      duration: 1.1,
      smoothWheel: true,
      // gentle, premium easing
      easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    });

    // Dev-only handle so tooling (e.g. screenshot scripts) can drive scroll
    // deterministically. Stripped from production builds.
    if (process.env.NODE_ENV !== "production") {
      (window as unknown as { __lenis?: Lenis }).__lenis = lenis;
    }

    let frame = 0;
    const raf = (time: number) => {
      lenis.raf(time);
      frame = requestAnimationFrame(raf);
    };
    frame = requestAnimationFrame(raf);

    return () => {
      cancelAnimationFrame(frame);
      lenis.destroy();
    };
  }, [prefersReduced]);

  return <>{children}</>;
}
