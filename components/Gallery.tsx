"use client";

import { useCallback, useEffect, useState } from "react";
import Image from "next/image";
import { AnimatePresence, motion } from "framer-motion";

// Project image gallery with a full-screen lightbox.
// Click a tile to open; arrow keys / on-screen arrows navigate; Esc or backdrop closes.
export default function Gallery({
  images,
  title,
}: {
  images: string[];
  title: string;
}) {
  const [open, setOpen] = useState<number | null>(null);
  const isOpen = open !== null;

  const close = useCallback(() => setOpen(null), []);
  const step = useCallback(
    (dir: number) =>
      setOpen((i) =>
        i === null ? i : (i + dir + images.length) % images.length,
      ),
    [images.length],
  );

  // Keyboard navigation + body scroll lock while the lightbox is open.
  useEffect(() => {
    if (!isOpen) return;
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "Escape") close();
      if (e.key === "ArrowRight") step(1);
      if (e.key === "ArrowLeft") step(-1);
    };
    window.addEventListener("keydown", onKey);
    const prevOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      window.removeEventListener("keydown", onKey);
      document.body.style.overflow = prevOverflow;
    };
  }, [isOpen, close, step]);

  return (
    <>
      <div className="grid gap-6 md:grid-cols-3">
        {images.map((src, i) => (
          <button
            key={src}
            type="button"
            onClick={() => setOpen(i)}
            aria-label={`Open image ${i + 1} of ${images.length}`}
            className={`group relative cursor-zoom-in overflow-hidden ${
              i === 0 ? "aspect-[16/10] md:col-span-2" : "aspect-[4/3]"
            }`}
          >
            <Image
              src={src}
              alt={`${title} interior detail ${i + 1}`}
              fill
              sizes="(max-width: 768px) 100vw, 50vw"
              className="object-cover transition-transform duration-700 group-hover:scale-105"
            />
            <span className="absolute inset-0 bg-charcoal/0 transition-colors duration-300 group-hover:bg-charcoal/15" />
          </button>
        ))}
      </div>

      <AnimatePresence>
        {isOpen && (
          <motion.div
            className="fixed inset-0 z-[60] flex items-center justify-center bg-charcoal/95 p-4"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            transition={{ duration: 0.25 }}
            onClick={close}
            role="dialog"
            aria-modal="true"
            aria-label={`${title} gallery`}
          >
            {/* Close */}
            <button
              type="button"
              onClick={close}
              aria-label="Close gallery"
              className="absolute right-5 top-5 flex h-11 w-11 items-center justify-center text-2xl text-ivory/80 transition-colors hover:text-gold"
            >
              ✕
            </button>

            {/* Prev */}
            <button
              type="button"
              onClick={(e) => {
                e.stopPropagation();
                step(-1);
              }}
              aria-label="Previous image"
              className="absolute left-3 flex h-12 w-12 items-center justify-center rounded-full border border-ivory/30 text-ivory transition-colors hover:border-gold hover:text-gold md:left-8"
            >
              ‹
            </button>

            {/* Image */}
            <motion.div
              key={open}
              className="relative h-[78vh] w-full max-w-5xl"
              initial={{ opacity: 0, scale: 0.97 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ duration: 0.3 }}
              onClick={(e) => e.stopPropagation()}
            >
              <Image
                src={images[open]}
                alt={`${title} interior detail ${open + 1}`}
                fill
                sizes="100vw"
                className="object-contain"
              />
            </motion.div>

            {/* Next */}
            <button
              type="button"
              onClick={(e) => {
                e.stopPropagation();
                step(1);
              }}
              aria-label="Next image"
              className="absolute right-3 flex h-12 w-12 items-center justify-center rounded-full border border-ivory/30 text-ivory transition-colors hover:border-gold hover:text-gold md:right-8"
            >
              ›
            </button>

            {/* Counter */}
            <span className="absolute bottom-6 text-xs uppercase tracking-[0.2em] text-ivory/60">
              {open + 1} / {images.length}
            </span>
          </motion.div>
        )}
      </AnimatePresence>
    </>
  );
}
