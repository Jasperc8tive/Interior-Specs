"use client";

import { motion } from "framer-motion";
import { Button, Container } from "@/components/ui";
import AnimatedHeading from "@/components/home/AnimatedHeading";
import { site, whatsappLink } from "@/lib/site";
import { transition, viewportOnce } from "@/lib/anim";

// Reusable closing call-to-action band used at the foot of most pages.
// A dramatic onyx canvas with a soft gold glow, matching the homepage close.
export default function CTASection({
  eyebrow = "Begin",
  title = "Ready to transform your space?",
  intro = "Every project is unique. Let's discuss yours over a private consultation.",
  primaryLabel = "Book Your Private Consultation",
  accent,
}: {
  eyebrow?: string;
  title?: string;
  intro?: string;
  primaryLabel?: string;
  accent?: { from: number; to: number };
}) {
  return (
    <section className="grain relative overflow-hidden bg-onyx py-24 md:py-32">
      <div className="absolute inset-0 bg-[radial-gradient(80%_60%_at_50%_0%,rgba(201,169,97,0.12),transparent_70%)]" />
      <Container className="relative">
        <div className="mx-auto flex max-w-2xl flex-col items-center gap-9 text-center">
          <div className="flex flex-col items-center gap-5">
            <motion.span
              initial={{ opacity: 0 }}
              whileInView={{ opacity: 1 }}
              viewport={viewportOnce}
              transition={transition}
              className="eyebrow eyebrow--center justify-center text-gold-soft"
            >
              {eyebrow}
            </motion.span>
            <AnimatedHeading
              text={title}
              accent={accent}
              className="text-3xl text-ivory sm:text-4xl lg:text-[2.9rem]"
            />
            <motion.p
              initial={{ opacity: 0, y: 16 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={viewportOnce}
              transition={{ ...transition, delay: 0.12 }}
              className="max-w-xl text-lg text-ivory/80"
            >
              {intro}
            </motion.p>
          </div>
          <motion.div
            initial={{ opacity: 0, y: 16 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={viewportOnce}
            transition={{ ...transition, delay: 0.2 }}
            className="flex flex-col gap-4 sm:flex-row"
          >
            <Button href={site.bookingUrl} variant="primary">
              {primaryLabel}
            </Button>
            <Button href={whatsappLink()} variant="outline">
              Chat on WhatsApp
            </Button>
          </motion.div>
        </div>
      </Container>
    </section>
  );
}
