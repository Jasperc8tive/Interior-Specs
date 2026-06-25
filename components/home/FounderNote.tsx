"use client";

import Image from "next/image";
import { motion } from "framer-motion";
import { Button, Container } from "@/components/ui";
import Parallax from "@/components/Parallax";
import AnimatedHeading from "@/components/home/AnimatedHeading";
import { transition, viewportOnce } from "@/lib/anim";

// The voice behind the house. A parallaxed portrait paired with a signed,
// lyrical statement of intent.
export default function FounderNote() {
  return (
    <section className="bg-white py-24 md:py-32">
      <Container>
        <div className="grid items-center gap-14 lg:grid-cols-[0.85fr_1fr] lg:gap-20">
          <motion.div
            initial={{ opacity: 0 }}
            whileInView={{ opacity: 1 }}
            viewport={viewportOnce}
            transition={transition}
            className="relative aspect-[4/5] overflow-hidden"
          >
            <Parallax className="absolute inset-0" distance={40}>
              <Image
                src="https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&w=1100&q=80"
                alt="The founder of Interior Specifics"
                fill
                sizes="(max-width: 1024px) 100vw, 40vw"
                className="scale-110 object-cover"
              />
            </Parallax>
            <span className="absolute bottom-5 left-5 z-10 text-xs uppercase tracking-[0.25em] text-white/80">
              Founder &amp; Principal Designer
            </span>
          </motion.div>

          <div>
            <span className="eyebrow text-gold">Leadership</span>
            <AnimatedHeading
              text="Meet the visionary behind Interior Specifics"
              accent={{ from: 2, to: 3 }}
              className="mt-6 text-3xl text-charcoal sm:text-4xl lg:text-[2.6rem]"
            />

            <motion.p
              initial={{ opacity: 0, y: 18 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={viewportOnce}
              transition={{ ...transition, delay: 0.12 }}
              className="mt-7 max-w-xl text-lg text-muted"
            >
              With over fifteen years shaping Nigeria&apos;s finest interiors, our
              founder built Interior Specifics on a single conviction: every
              accomplished person deserves a space that honours their journey.
            </motion.p>

            <motion.blockquote
              initial={{ opacity: 0, y: 18 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={viewportOnce}
              transition={{ ...transition, delay: 0.2 }}
              className="accent mt-8 border-l-2 border-gold pl-7 text-2xl leading-snug text-charcoal md:text-[1.7rem]"
            >
              &ldquo;I believe every successful person deserves a space that
              honours their journey.&rdquo;
            </motion.blockquote>

            <motion.div
              initial={{ opacity: 0, y: 18 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={viewportOnce}
              transition={{ ...transition, delay: 0.28 }}
              className="mt-9"
            >
              <Button href="/about#directors" variant="dark">
                Read Our Story
              </Button>
            </motion.div>
          </div>
        </div>
      </Container>
    </section>
  );
}
