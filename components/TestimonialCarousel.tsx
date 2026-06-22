"use client";

import { useState } from "react";
import { AnimatePresence, motion } from "framer-motion";
import { testimonials } from "@/lib/testimonials";

export default function TestimonialCarousel() {
  const [index, setIndex] = useState(0);
  const t = testimonials[index];

  const go = (dir: number) =>
    setIndex((i) => (i + dir + testimonials.length) % testimonials.length);

  return (
    <div className="mx-auto max-w-3xl text-center">
      <div className="relative min-h-[220px]">
        <AnimatePresence mode="wait">
          <motion.figure
            key={index}
            initial={{ opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -16 }}
            transition={{ duration: 0.5 }}
          >
            <div className="mb-6 font-serif text-5xl text-gold/40">&ldquo;</div>
            <blockquote className="font-accent text-2xl italic leading-relaxed text-ivory md:text-3xl">
              {t.quote}
            </blockquote>
            <figcaption className="mt-8">
              <div className="text-sm font-semibold uppercase tracking-[0.15em] text-gold">
                {t.name}
              </div>
              <div className="mt-1 text-sm text-ivory/60">
                {t.location} · {t.project}
              </div>
            </figcaption>
          </motion.figure>
        </AnimatePresence>
      </div>

      <div className="mt-10 flex items-center justify-center gap-6">
        <button
          type="button"
          onClick={() => go(-1)}
          aria-label="Previous testimonial"
          className="flex h-11 w-11 items-center justify-center rounded-full border border-ivory/30 text-ivory transition-colors hover:border-gold hover:text-gold"
        >
          ‹
        </button>
        <div className="flex gap-2">
          {testimonials.map((_, i) => (
            <button
              key={i}
              type="button"
              aria-label={`Go to testimonial ${i + 1}`}
              onClick={() => setIndex(i)}
              className={`h-2 w-2 rounded-full transition-colors ${
                i === index ? "bg-gold" : "bg-ivory/30"
              }`}
            />
          ))}
        </div>
        <button
          type="button"
          onClick={() => go(1)}
          aria-label="Next testimonial"
          className="flex h-11 w-11 items-center justify-center rounded-full border border-ivory/30 text-ivory transition-colors hover:border-gold hover:text-gold"
        >
          ›
        </button>
      </div>
    </div>
  );
}
