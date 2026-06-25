import type { Metadata } from "next";
import Image from "next/image";
import PageHero from "@/components/PageHero";
import ProcessTimeline from "@/components/ProcessTimeline";
import CTASection from "@/components/CTASection";
import SectionReveal from "@/components/SectionReveal";
import RevealHeading from "@/components/RevealHeading";
import Parallax from "@/components/Parallax";
import { Container } from "@/components/ui";
import { site } from "@/lib/site";

export const metadata: Metadata = {
  title: "About — Crafting Legacies Through Design",
  description:
    "Meet the team behind Interior Specifics, Lagos' premier luxury interior design firm. Our story, values, leadership and the Interior Specifics Method™.",
};

const values = [
  { title: "Excellence", body: "We pursue the exceptional in every detail, never the merely acceptable." },
  { title: "Integrity", body: "Transparent budgets, honest timelines and advice you can trust." },
  { title: "Innovation", body: "Fresh thinking grounded in timeless design principles." },
  { title: "Client-Centricity", body: "Your vision and your comfort lead every decision we make." },
];

const directors = [
  { name: "Founder & Creative Director", role: "Vision & Design", img: "https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&w=800&q=80" },
  { name: "Design Director", role: "Concept & Detailing", img: "https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=800&q=80" },
  { name: "Head of Projects", role: "Execution & Delivery", img: "https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=800&q=80" },
];

const awards = [
  "Architectural Digest Nigeria — Featured Studio",
  "House & Home — Design of the Year, Finalist",
  "IDAN — Member in Good Standing",
  "Lagos Luxury Awards — Interior Firm of the Year",
];

export default function AboutPage() {
  return (
    <>
      <PageHero
        eyebrow={`Since ${site.foundedYear}`}
        title="Crafting legacies through design"
        accent={{ from: 1, to: 1 }}
        intro="We are a Lagos-based luxury interior design studio dedicated to spaces that honour the people who live and work in them."
        image="https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&w=2000&q=80"
        imageAlt="Elegant designed interior by Interior Specifics"
      />

      {/* Story / Profile */}
      <section id="profile" className="scroll-mt-24 bg-white py-24 md:py-32">
        <Container>
          <div className="grid items-center gap-14 lg:grid-cols-2 lg:gap-20">
            <SectionReveal>
              <RevealHeading
                eyebrow="Our Story"
                title="A studio built on a single conviction"
                align="left"
              />
              <div className="mt-7 space-y-5 text-lg text-muted">
                <p>
                  Interior Specifics began with the belief that every accomplished
                  person deserves a space that reflects their journey. What started
                  as a boutique practice has grown into one of Lagos&apos; most
                  trusted luxury design firms, with over 200 completed projects.
                </p>
                <p>
                  Today we offer a complete service — architecture, interiors,
                  sourcing, finishing and project management — all under one roof,
                  so our clients enjoy a seamless, stress-free experience from first
                  sketch to final styling.
                </p>
              </div>
            </SectionReveal>
            <SectionReveal delay={0.1} className="relative aspect-[4/3] overflow-hidden">
              <Parallax className="absolute inset-0" distance={34}>
                <Image
                  src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=1200&q=80"
                  alt="Interior Specifics design studio"
                  fill
                  sizes="(max-width: 1024px) 100vw, 50vw"
                  className="scale-110 object-cover"
                />
              </Parallax>
            </SectionReveal>
          </div>

          {/* Mission & Vision */}
          <div className="mt-20 grid gap-6 md:grid-cols-2">
            {[
              { h: "Our Mission", p: "To design and deliver exceptional spaces that elevate how our clients live, work and feel — with absolute craft and complete peace of mind." },
              { h: "Our Vision", p: "To be West Africa's most respected name in luxury interior design — the natural choice for those who expect the very best." },
            ].map((m, i) => (
              <SectionReveal key={m.h} delay={i * 0.1} className="group border-t-2 border-gold bg-ivory p-10 transition-colors duration-300 hover:bg-ivory-dark">
                <h3 className="text-2xl text-charcoal">{m.h}</h3>
                <p className="mt-4 text-muted">{m.p}</p>
              </SectionReveal>
            ))}
          </div>
        </Container>
      </section>

      {/* Values */}
      <section className="bg-ivory py-24 md:py-32">
        <Container>
          <SectionReveal>
            <RevealHeading eyebrow="What We Stand For" title="Our core values" />
          </SectionReveal>
          <div className="mt-16 grid gap-px overflow-hidden border border-line bg-line sm:grid-cols-2 lg:grid-cols-4">
            {values.map((v, i) => (
              <SectionReveal key={v.title} delay={i * 0.08} className="group h-full bg-ivory p-8 transition-colors duration-300 hover:bg-white">
                <span className="font-serif text-3xl text-gold/80 transition-colors duration-300 group-hover:text-gold">0{i + 1}</span>
                <h3 className="mt-4 text-lg text-charcoal">{v.title}</h3>
                <p className="mt-3 text-sm text-muted">{v.body}</p>
              </SectionReveal>
            ))}
          </div>
        </Container>
      </section>

      {/* Method */}
      <section className="bg-white py-24 md:py-32">
        <Container>
          <SectionReveal>
            <RevealHeading
              eyebrow="How We Work"
              title="The Interior Specifics Method™"
              intro="A proven, five-step process refined across hundreds of projects."
            />
          </SectionReveal>
          <div className="mt-16">
            <ProcessTimeline />
          </div>
        </Container>
      </section>

      {/* Directors */}
      <section id="directors" className="scroll-mt-24 bg-ivory py-24 md:py-32">
        <Container>
          <SectionReveal>
            <RevealHeading eyebrow="Leadership" title="Meet the directors" />
          </SectionReveal>
          <div className="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            {directors.map((d, i) => (
              <SectionReveal key={d.name} delay={i * 0.1} className="group bg-white">
                <div className="relative aspect-[3/4] overflow-hidden">
                  <Image
                    src={d.img}
                    alt={d.name}
                    fill
                    sizes="(max-width: 768px) 100vw, 33vw"
                    className="object-cover transition-transform duration-[1.1s] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-charcoal/45 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100" />
                </div>
                <div className="p-7">
                  <h3 className="text-lg text-charcoal">{d.name}</h3>
                  <p className="mt-1 text-sm uppercase tracking-[0.12em] text-gold">{d.role}</p>
                </div>
              </SectionReveal>
            ))}
          </div>
        </Container>
      </section>

      {/* Awards & Clients */}
      <section id="awards" className="grain relative scroll-mt-24 bg-charcoal py-24 md:py-32">
        <Container>
          <SectionReveal>
            <RevealHeading eyebrow="Recognition" title="Awards & recognition" light />
          </SectionReveal>
          <div className="mx-auto mt-14 grid max-w-3xl gap-px overflow-hidden border border-line-light bg-line-light sm:grid-cols-2">
            {awards.map((a, i) => (
              <SectionReveal key={a} delay={i * 0.06} className="group flex items-center bg-charcoal p-7 transition-colors duration-300 hover:bg-charcoal-light">
                <p className="text-sm text-ivory/80">{a}</p>
              </SectionReveal>
            ))}
          </div>
          <p id="clients" className="mx-auto mt-12 max-w-2xl scroll-mt-24 text-center text-ivory/60">
            Trusted by homeowners, property developers and corporate clients across
            Lagos and beyond.
          </p>
        </Container>
      </section>

      <CTASection
        eyebrow="Work With Us"
        title="Partner with our team"
        intro="Tell us about your space and your ambitions. We'll show you what's possible."
      />
    </>
  );
}
