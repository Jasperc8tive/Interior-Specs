"use client";

import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";
import { Container } from "@/components/ui";
import AnimatedHeading from "@/components/home/AnimatedHeading";
import { rise, stagger, transition, viewportOnce } from "@/lib/anim";
import { featuredProjects, type Project } from "@/lib/projects";

// Magazine-style portfolio: an asymmetric lead pair, a supporting trio, and a
// cinematic full-width closer. Cards reveal investment range on hover to
// pre-qualify enquiries.
export default function FeaturedWork() {
  const [a, b, c, d, e, f] = featuredProjects;

  return (
    <section id="portfolio" className="grain relative bg-onyx py-24 md:py-32">
      <Container className="relative">
        <div className="flex flex-col gap-8 md:flex-row md:items-end md:justify-between">
          <div className="max-w-2xl">
            <span className="eyebrow text-gold-soft">Selected Work</span>
            <AnimatedHeading
              text="Spaces that speak of achievement"
              accent={{ from: 3, to: 4 }}
              className="mt-6 text-3xl text-ivory sm:text-4xl lg:text-[2.75rem]"
            />
          </div>
          <motion.div
            initial={{ opacity: 0, y: 16 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={viewportOnce}
            transition={transition}
          >
            <Link
              href="/showroom"
              className="group inline-flex items-center gap-3 text-sm font-medium uppercase tracking-[0.18em] text-ivory/80 transition-colors hover:text-gold"
            >
              View the Full Showroom
              <span className="h-px w-10 bg-gold transition-all duration-300 group-hover:w-16" />
            </Link>
          </motion.div>
        </div>

        <motion.div
          variants={stagger(0.1)}
          initial="hidden"
          whileInView="show"
          viewport={{ once: true, amount: 0.15 }}
          className="mt-14 flex flex-col gap-5 md:mt-16"
        >
          {/* Lead pair */}
          <div className="grid gap-5 md:grid-cols-12">
            <ProjectCard project={a} className="md:col-span-7" aspect="aspect-[4/3] md:aspect-[16/12]" />
            <ProjectCard project={b} className="md:col-span-5" aspect="aspect-[4/3] md:aspect-[16/12]" />
          </div>
          {/* Supporting trio */}
          <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <ProjectCard project={c} aspect="aspect-[4/5]" />
            <ProjectCard project={d} aspect="aspect-[4/5]" />
            <ProjectCard project={e} aspect="aspect-[4/5]" className="sm:col-span-2 lg:col-span-1" />
          </div>
          {/* Cinematic closer */}
          {f && <ProjectCard project={f} aspect="aspect-[16/10] md:aspect-[21/9]" wide />}
        </motion.div>
      </Container>
    </section>
  );
}

function ProjectCard({
  project: p,
  className = "",
  aspect,
  wide = false,
}: {
  project: Project;
  className?: string;
  aspect: string;
  wide?: boolean;
}) {
  return (
    <motion.div variants={rise} className={className}>
      <Link
        href={`/showroom/${p.slug}`}
        className={`group relative block w-full overflow-hidden bg-charcoal ${aspect}`}
      >
        <Image
          src={p.image}
          alt={`${p.title} — ${p.type} interior design in ${p.location}`}
          fill
          sizes={wide ? "100vw" : "(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 40vw"}
          className="object-cover transition-transform duration-[1.1s] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.06]"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-onyx/90 via-onyx/25 to-transparent" />

        <div className={`absolute inset-x-0 bottom-0 p-7 ${wide ? "md:p-10" : ""}`}>
          <span className="block text-xs font-semibold uppercase tracking-[0.2em] text-gold">
            {p.type} · {p.location}
          </span>
          <h3 className="mt-2 text-xl text-ivory md:text-2xl">
            <span className="inline-block">
              {p.title}
              <span className="mt-1 block h-px w-0 bg-gold transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:w-full" />
            </span>
          </h3>

          <div className="grid grid-rows-[0fr] opacity-0 transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:grid-rows-[1fr] group-hover:opacity-100">
            <div className="overflow-hidden">
              <p className="pt-3 text-sm text-ivory/80">{p.blurb}</p>
              <p className="mt-2 text-sm font-semibold text-gold-soft">
                Investment: {p.budget}
              </p>
              <span className="mt-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.15em] text-gold">
                View Case Study
                <svg viewBox="0 0 24 24" className="h-3.5 w-3.5 fill-none stroke-current stroke-2" aria-hidden>
                  <path d="M5 12h14M13 6l6 6-6 6" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              </span>
            </div>
          </div>
        </div>
      </Link>
    </motion.div>
  );
}
