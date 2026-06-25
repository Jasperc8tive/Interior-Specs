"use client";

import { useRef } from "react";
import Image from "next/image";
import {
  motion,
  useReducedMotion,
  useScroll,
  useTransform,
} from "framer-motion";
import { Container } from "@/components/ui";
import AnimatedHeading from "@/components/home/AnimatedHeading";
import { EASE } from "@/lib/anim";

// Compact inner-page hero. Slides beneath the transparent sticky header; the
// image drifts and scales on scroll while the title wipes up word by word.
export default function PageHero({
  eyebrow,
  title,
  intro,
  image,
  imageAlt,
  accent,
}: {
  eyebrow?: string;
  title: string;
  intro?: string;
  image: string;
  imageAlt: string;
  accent?: { from: number; to: number };
}) {
  const ref = useRef<HTMLElement>(null);
  const reduced = useReducedMotion();
  const { scrollYProgress } = useScroll({
    target: ref,
    offset: ["start start", "end start"],
  });
  const scale = useTransform(scrollYProgress, [0, 1], [1, 1.12]);
  const y = useTransform(scrollYProgress, [0, 1], [0, 80]);
  const fade = useTransform(scrollYProgress, [0, 0.6], [1, 0]);

  const settle = (delay: number) => ({
    initial: reduced ? false : { opacity: 0, y: 20 },
    animate: { opacity: 1, y: 0 },
    transition: { duration: 0.9, ease: EASE, delay },
  });

  return (
    <section
      ref={ref}
      className="grain relative -mt-[88px] flex min-h-[68vh] items-center overflow-hidden"
    >
      <motion.div
        style={reduced ? undefined : { scale, y }}
        className="absolute inset-0"
      >
        <Image
          src={image}
          alt={imageAlt}
          fill
          priority
          sizes="100vw"
          className="object-cover"
        />
        <div className="hero-overlay absolute inset-0" />
        <div className="absolute inset-0 bg-[radial-gradient(120%_100%_at_50%_0%,transparent_55%,rgba(18,17,16,0.5))]" />
      </motion.div>

      <Container className="relative z-10 pt-[88px] text-center">
        <motion.div
          style={reduced ? undefined : { opacity: fade }}
          className="mx-auto max-w-3xl"
        >
          {eyebrow && (
            <motion.span
              {...settle(0.1)}
              className="eyebrow eyebrow--center justify-center text-gold-soft"
            >
              {eyebrow}
            </motion.span>
          )}
          <AnimatedHeading
            as="h1"
            text={title}
            accent={accent}
            delay={0.2}
            className="display mt-6 text-4xl text-ivory sm:text-5xl lg:text-[3.5rem]"
          />
          <motion.div {...settle(0.5)} className="gold-rule mx-auto mt-7" />
          {intro && (
            <motion.p
              {...settle(0.6)}
              className="mx-auto mt-7 max-w-2xl text-lg text-ivory/85"
            >
              {intro}
            </motion.p>
          )}
        </motion.div>
      </Container>
    </section>
  );
}
