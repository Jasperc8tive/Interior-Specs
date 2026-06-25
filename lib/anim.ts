// Shared motion language for the homepage.
// One easing curve + a small set of variants keeps the whole page feeling
// like a single, deliberate piece of choreography rather than ad-hoc effects.

import type { Variants, Transition } from "framer-motion";

// The signature curve: a slow, confident settle. Mirrors --ease-lux in CSS.
export const EASE: [number, number, number, number] = [0.22, 1, 0.36, 1];

export const transition: Transition = { duration: 0.9, ease: EASE };

// Parent that staggers its children into view.
export const stagger = (gap = 0.09, delay = 0): Variants => ({
  hidden: {},
  show: {
    transition: { staggerChildren: gap, delayChildren: delay },
  },
});

// A single element rising and fading into place.
export const rise: Variants = {
  hidden: { opacity: 0, y: 26 },
  show: { opacity: 1, y: 0, transition },
};

// A line of text wiping up from behind a mask (parent must clip overflow).
export const wipeUp: Variants = {
  hidden: { y: "115%" },
  show: { y: "0%", transition: { duration: 1, ease: EASE } },
};

// Shared viewport config so reveals trigger consistently and only once.
export const viewportOnce = { once: true, amount: 0.3 } as const;
