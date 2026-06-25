"use client";

import { useCallback, useRef, useState, useSyncExternalStore } from "react";
import Image from "next/image";
import {
  motion,
  useMotionValueEvent,
  useReducedMotion,
  useScroll,
} from "framer-motion";
import { Container, SectionHeading } from "@/components/ui";
import SectionReveal from "@/components/SectionReveal";
import { methodActs, type MethodAct } from "@/lib/method";

const EYEBROW = "The Interior Specifics Method™";
const EASE = [0.22, 1, 0.36, 1] as const;

// SSR-safe media query (no setState-in-effect, no hydration mismatch).
function useMediaQuery(query: string) {
  const subscribe = useCallback(
    (cb: () => void) => {
      const mql = window.matchMedia(query);
      mql.addEventListener("change", cb);
      return () => mql.removeEventListener("change", cb);
    },
    [query],
  );
  return useSyncExternalStore(
    subscribe,
    () => window.matchMedia(query).matches,
    () => false, // server: assume mobile → safe stacked fallback
  );
}

export default function MethodJourney() {
  const reduced = useReducedMotion();
  const isDesktop = useMediaQuery("(min-width: 768px)");
  const enhanced = isDesktop && !reduced;

  return enhanced ? <PinnedJourney /> : <StackedJourney />;
}

/* ------------------------------------------------------------------ */
/* Desktop: pinned stage; scroll position selects the active act.      */
/* Acts cross-fade via the declarative animate API (no scroll-bound    */
/* style values), which is stable across the Framer/Turbopack runtime. */
/* ------------------------------------------------------------------ */

function PinnedJourney() {
  const ref = useRef<HTMLElement>(null);
  const { scrollYProgress } = useScroll({
    target: ref,
    offset: ["start start", "end end"],
  });
  const [active, setActive] = useState(0);

  useMotionValueEvent(scrollYProgress, "change", (v) => {
    const i = Math.min(
      methodActs.length - 1,
      Math.max(0, Math.floor(v * methodActs.length)),
    );
    setActive(i);
  });

  return (
    <section
      id="method"
      ref={ref}
      style={{ height: `${methodActs.length * 100}vh` }}
      className="relative bg-charcoal"
    >
      <div className="sticky top-0 h-screen overflow-hidden">
        {methodActs.map((act, i) => (
          <Scene key={act.n} act={act} index={i} isActive={i === active} />
        ))}

        {/* Persistent label */}
        <div className="pointer-events-none absolute inset-x-0 top-0 z-20 pt-[120px]">
          <Container>
            <span className="text-xs font-semibold uppercase tracking-[0.3em] text-gold-soft">
              {EYEBROW}
            </span>
          </Container>
        </div>

        {/* Progress rail */}
        <ol className="absolute right-8 top-1/2 z-20 hidden -translate-y-1/2 flex-col gap-5 lg:flex">
          {methodActs.map((a, i) => (
            <li key={a.n} className="flex items-center justify-end gap-3 text-right">
              <span
                className={`text-xs uppercase tracking-[0.2em] transition-colors duration-300 ${
                  i === active ? "text-gold" : "text-ivory/40"
                }`}
              >
                {a.title}
              </span>
              <span
                className={`h-2 w-2 rounded-full transition-all duration-300 ${
                  i === active ? "scale-125 bg-gold" : "bg-ivory/30"
                }`}
              />
            </li>
          ))}
        </ol>
      </div>
    </section>
  );
}

function Scene({
  act,
  index,
  isActive,
}: {
  act: MethodAct;
  index: number;
  isActive: boolean;
}) {
  return (
    <div className="absolute inset-0">
      {/* Image layer */}
      <motion.div
        className="absolute inset-0"
        initial={false}
        animate={{ opacity: isActive ? 1 : 0, scale: isActive ? 1 : 1.08 }}
        transition={{ duration: 1, ease: EASE }}
      >
        <Image
          src={act.image}
          alt={`${act.title} — the Interior Specifics Method`}
          fill
          priority={index === 0}
          sizes="100vw"
          className="object-cover"
        />
        <div className="absolute inset-0 bg-charcoal/55" />
        <div className="hero-overlay absolute inset-0" />
      </motion.div>

      {/* Text layer */}
      <motion.div
        className="absolute inset-0 z-10 flex items-center"
        initial={false}
        animate={{ opacity: isActive ? 1 : 0, y: isActive ? 0 : 32 }}
        transition={{ duration: 0.7, ease: EASE, delay: isActive ? 0.12 : 0 }}
      >
        <Container>
          <div className="max-w-2xl">
            <span className="font-serif text-6xl text-gold/80 md:text-7xl">{act.n}</span>
            <h3 className="mt-3 text-4xl text-ivory sm:text-5xl">{act.title}</h3>
            <div className="gold-rule mt-6" />
            <p className="mt-6 max-w-xl text-lg text-ivory/85">{act.narrative}</p>
          </div>
        </Container>
      </motion.div>
    </div>
  );
}

/* ------------------------------------------------------------------ */
/* Mobile / reduced-motion: stacked full-bleed acts                    */
/* ------------------------------------------------------------------ */

function StackedJourney() {
  return (
    <section id="method" className="bg-charcoal">
      <Container className="py-16 md:py-20">
        <SectionReveal>
          <SectionHeading
            eyebrow={EYEBROW}
            title="Our Proven Process for Creating Exceptional Spaces"
            intro="Five deliberate acts that turn ambition into a space you'll love for years."
            light
          />
        </SectionReveal>
      </Container>

      {methodActs.map((act) => (
        <div key={act.n} className="relative flex min-h-[70vh] items-center">
          <Image
            src={act.image}
            alt={`${act.title} — the Interior Specifics Method`}
            fill
            sizes="100vw"
            className="object-cover"
          />
          <div className="absolute inset-0 bg-charcoal/60" />
          <div className="hero-overlay absolute inset-0" />
          <Container className="relative z-10">
            <SectionReveal className="max-w-xl">
              <span className="font-serif text-5xl text-gold/80">{act.n}</span>
              <h3 className="mt-3 text-3xl text-ivory sm:text-4xl">{act.title}</h3>
              <div className="gold-rule mt-5" />
              <p className="mt-5 text-lg text-ivory/85">{act.narrative}</p>
            </SectionReveal>
          </Container>
        </div>
      ))}
    </section>
  );
}
