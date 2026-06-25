"use client";

import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";
import { Button, Container } from "@/components/ui";
import AnimatedHeading from "@/components/home/AnimatedHeading";
import { rise, stagger, viewportOnce } from "@/lib/anim";
import { featuredServices } from "@/lib/services";

// Disciplines preview. Restrained cards with a slow image push and a gold
// captioned reveal on hover.
export default function ServicesPreview() {
  return (
    <section className="bg-ivory py-24 md:py-32">
      <Container>
        <div className="max-w-2xl">
          <span className="eyebrow text-gold">What We Do</span>
          <AnimatedHeading
            text="Every discipline, delivered in-house"
            accent={{ from: 3, to: 4 }}
            className="mt-6 text-3xl text-charcoal sm:text-4xl lg:text-[2.75rem]"
          />
          <motion.p
            initial={{ opacity: 0, y: 16 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={viewportOnce}
            transition={{ duration: 0.8, ease: [0.22, 1, 0.36, 1], delay: 0.15 }}
            className="mt-6 text-lg text-muted"
          >
            From full residences to corporate headquarters — one team, one
            standard, from first sketch to final styling.
          </motion.p>
        </div>

        <motion.div
          variants={stagger(0.1)}
          initial="hidden"
          whileInView="show"
          viewport={{ once: true, amount: 0.15 }}
          className="mt-14 grid gap-6 md:grid-cols-3"
        >
          {featuredServices.map((s) => (
            <motion.div key={s.slug} variants={rise}>
              <Link
                href={`/services/${s.slug}`}
                className="group block h-full overflow-hidden border border-line bg-white transition-colors duration-300 hover:border-gold/50"
              >
                <div className="relative aspect-[16/11] overflow-hidden">
                  <Image
                    src={s.image}
                    alt={s.title}
                    fill
                    sizes="(max-width: 768px) 100vw, 33vw"
                    className="object-cover transition-transform duration-[1.1s] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-charcoal/40 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100" />
                </div>
                <div className="p-7">
                  <h3 className="text-xl text-charcoal transition-colors duration-300 group-hover:text-gold">
                    {s.title}
                  </h3>
                  <p className="mt-3 text-sm text-muted">{s.short}</p>
                  <span className="mt-5 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.15em] text-gold">
                    Learn More
                    <span className="h-px w-6 bg-gold transition-all duration-300 group-hover:w-10" />
                  </span>
                </div>
              </Link>
            </motion.div>
          ))}
        </motion.div>

        <div className="mt-14 text-center">
          <Button href="/services" variant="dark">
            View All Services
          </Button>
        </div>
      </Container>
    </section>
  );
}
