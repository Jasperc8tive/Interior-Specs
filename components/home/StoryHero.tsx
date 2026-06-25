"use client";

import { useRef } from "react";
import Image from "next/image";
import {
  motion,
  useReducedMotion,
  useScroll,
  useTransform,
} from "framer-motion";
import { Button, Container } from "@/components/ui";
import AnimatedHeading from "@/components/home/AnimatedHeading";
import { site } from "@/lib/site";
import { EASE } from "@/lib/anim";

// Opening scene: a full-bleed hero whose image scales and drifts while the
// headline wipes up word by word and the copy settles into place.
export default function StoryHero() {
  const ref = useRef<HTMLElement>(null);
  const reduced = useReducedMotion();
  const { scrollYProgress } = useScroll({
    target: ref,
    offset: ["start start", "end start"],
  });

  const imageScale = useTransform(scrollYProgress, [0, 1], [1, 1.18]);
  const imageY = useTransform(scrollYProgress, [0, 1], [0, 110]);
  const contentOpacity = useTransform(scrollYProgress, [0, 0.5], [1, 0]);
  const contentY = useTransform(scrollYProgress, [0, 0.55], [0, -80]);
  const cueOpacity = useTransform(scrollYProgress, [0, 0.12], [1, 0]);

  const imgStyle = reduced ? undefined : { scale: imageScale, y: imageY };

  const settle = (delay: number) => ({
    initial: reduced ? false : { opacity: 0, y: 22 },
    animate: { opacity: 1, y: 0 },
    transition: { duration: 0.9, ease: EASE, delay },
  });

  return (
    <section
      ref={ref}
      className="grain relative -mt-[88px] flex min-h-[100svh] items-center overflow-hidden"
    >
      <motion.div style={imgStyle} className="absolute inset-0">
        <Image
          src="https://images.unsplash.com/photo-1618219908412-a29a1bb7b86e?auto=format&fit=crop&w=2400&q=80"
          alt="Luxury living room interior bathed in warm natural light"
          fill
          priority
          sizes="100vw"
          className="object-cover"
        />
        <div className="hero-overlay absolute inset-0" />
        {/* Edge vignette for a gallery-lit feel */}
        <div className="absolute inset-0 bg-[radial-gradient(120%_100%_at_50%_0%,transparent_55%,rgba(18,17,16,0.55))]" />
      </motion.div>

      {/* Vertical establishing label */}
      <div className="pointer-events-none absolute left-6 top-1/2 hidden -translate-y-1/2 lg:block">
        <motion.span
          {...settle(0.9)}
          className="block origin-left -rotate-90 text-[10px] font-medium uppercase tracking-[0.4em] text-ivory/50"
        >
          Est. {site.foundedYear} — Victoria Island, Lagos
        </motion.span>
      </div>

      <Container className="relative z-10 pt-[88px]">
        <motion.div
          style={reduced ? undefined : { opacity: contentOpacity, y: contentY }}
          className="max-w-4xl"
        >
          <motion.span
            {...settle(0.15)}
            className="eyebrow text-gold-soft"
          >
            Lagos&apos; Premier Luxury Design House
          </motion.span>

          <AnimatedHeading
            as="h1"
            text="Designing spaces worthy of achievement"
            accent={{ from: 2, to: 4 }}
            delay={0.25}
            className="display mt-7 text-5xl text-ivory sm:text-6xl lg:text-7xl xl:text-[5.25rem]"
          />

          <motion.p
            {...settle(0.75)}
            className="mt-7 max-w-xl text-lg text-ivory/85"
          >
            Bespoke interiors for Nigeria&apos;s most discerning homeowners and
            businesses — concept to completion, under one roof. Follow the
            journey from first vision to final reveal.
          </motion.p>

          <motion.div
            {...settle(0.9)}
            className="mt-10 flex flex-col gap-4 sm:flex-row"
          >
            <Button href={site.bookingUrl} variant="primary">
              Book Your Private Consultation
            </Button>
            <Button href="#method" variant="outline">
              Begin the Journey
            </Button>
          </motion.div>

          <motion.div
            {...settle(1.05)}
            className="mt-14 flex flex-wrap items-stretch gap-x-10 gap-y-6 border-t border-line-light pt-7"
          >
            {site.stats.map((s, i) => (
              <div
                key={s.label}
                className={i > 0 ? "border-ivory/10 sm:border-l sm:pl-10" : ""}
              >
                <div className="font-serif text-2xl text-gold md:text-[1.7rem]">
                  {s.value}
                </div>
                <div className="mt-1 text-xs uppercase tracking-[0.18em] text-ivory/60">
                  {s.label}
                </div>
              </div>
            ))}
          </motion.div>
        </motion.div>
      </Container>

      {/* Scroll cue */}
      <motion.div
        style={reduced ? undefined : { opacity: cueOpacity }}
        className="absolute bottom-8 left-1/2 z-10 flex -translate-x-1/2 flex-col items-center gap-2 text-ivory/70"
      >
        <span className="text-[10px] uppercase tracking-[0.3em]">Scroll</span>
        <motion.span
          aria-hidden
          animate={reduced ? undefined : { y: [0, 9, 0] }}
          transition={{ duration: 1.8, repeat: Infinity, ease: "easeInOut" }}
          className="block h-9 w-px bg-gradient-to-b from-gold to-transparent"
        />
      </motion.div>
    </section>
  );
}
