"use client";

import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";
import { Container } from "@/components/ui";
import AnimatedHeading from "@/components/home/AnimatedHeading";
import { rise, stagger, transition, viewportOnce } from "@/lib/anim";
import { posts } from "@/lib/posts";

// Journal teaser. Image-led editorial cards with a quiet hover lift.
export default function JournalPreview() {
  return (
    <section className="bg-white py-24 md:py-32">
      <Container>
        <div className="flex flex-col gap-8 md:flex-row md:items-end md:justify-between">
          <div className="max-w-2xl">
            <span className="eyebrow text-gold">The Journal</span>
            <AnimatedHeading
              text="Design insights for the discerning"
              accent={{ from: 3, to: 4 }}
              className="mt-6 text-3xl text-charcoal sm:text-4xl lg:text-[2.75rem]"
            />
          </div>
          <motion.div
            initial={{ opacity: 0, y: 16 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={viewportOnce}
            transition={transition}
          >
            <Link
              href="/blog"
              className="group inline-flex items-center gap-3 text-sm font-medium uppercase tracking-[0.18em] text-charcoal transition-colors hover:text-gold"
            >
              Read the Journal
              <span className="h-px w-10 bg-gold transition-all duration-300 group-hover:w-16" />
            </Link>
          </motion.div>
        </div>

        <motion.div
          variants={stagger(0.1)}
          initial="hidden"
          whileInView="show"
          viewport={{ once: true, amount: 0.15 }}
          className="mt-14 grid gap-8 md:grid-cols-3"
        >
          {posts.slice(0, 3).map((p) => (
            <motion.article key={p.slug} variants={rise}>
              <Link href={`/blog/${p.slug}`} className="group block">
                <div className="relative aspect-[16/11] overflow-hidden">
                  <Image
                    src={p.image}
                    alt={p.title}
                    fill
                    sizes="(max-width: 768px) 100vw, 33vw"
                    className="object-cover transition-transform duration-[1.1s] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105"
                  />
                </div>
                <span className="mt-5 inline-block text-xs font-semibold uppercase tracking-[0.18em] text-gold">
                  {p.category} · {p.readTime}
                </span>
                <h3 className="mt-2 text-lg text-charcoal transition-colors duration-300 group-hover:text-gold">
                  {p.title}
                </h3>
                <p className="mt-2 text-sm text-muted">{p.excerpt}</p>
              </Link>
            </motion.article>
          ))}
        </motion.div>
      </Container>
    </section>
  );
}
