import type { Metadata } from "next";
import Image from "next/image";
import PageHero from "@/components/PageHero";
import ProcessTimeline from "@/components/ProcessTimeline";
import CTASection from "@/components/CTASection";
import SectionReveal from "@/components/SectionReveal";
import { Container, SectionHeading } from "@/components/ui";
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
        title="Crafting Legacies Through Design"
        intro="We are a Lagos-based luxury interior design studio dedicated to spaces that honour the people who live and work in them."
        image="https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&w=2000&q=80"
        imageAlt="Elegant designed interior by Interior Specifics"
      />

      {/* Story / Profile */}
      <section id="profile" className="bg-white py-20 md:py-28 scroll-mt-24">
        <Container>
          <div className="grid items-center gap-12 lg:grid-cols-2">
            <SectionReveal>
              <SectionHeading
                eyebrow="Our Story"
                title="A Studio Built on a Single Conviction"
                align="left"
              />
              <div className="mt-6 space-y-4 text-graysoft">
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
              <Image
                src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=1200&q=80"
                alt="Interior Specifics design studio"
                fill
                sizes="(max-width: 1024px) 100vw, 50vw"
                className="object-cover"
              />
            </SectionReveal>
          </div>

          {/* Mission & Vision */}
          <div className="mt-20 grid gap-8 md:grid-cols-2">
            <SectionReveal className="border-t-2 border-gold bg-ivory p-10">
              <h3 className="text-2xl text-charcoal">Our Mission</h3>
              <p className="mt-4 text-graysoft">
                To design and deliver exceptional spaces that elevate how our clients
                live, work and feel — with absolute craft and complete peace of mind.
              </p>
            </SectionReveal>
            <SectionReveal delay={0.1} className="border-t-2 border-gold bg-ivory p-10">
              <h3 className="text-2xl text-charcoal">Our Vision</h3>
              <p className="mt-4 text-graysoft">
                To be West Africa&apos;s most respected name in luxury interior
                design — the natural choice for those who expect the very best.
              </p>
            </SectionReveal>
          </div>
        </Container>
      </section>

      {/* Values */}
      <section className="bg-ivory py-20 md:py-28">
        <Container>
          <SectionReveal>
            <SectionHeading eyebrow="What We Stand For" title="Our Core Values" />
          </SectionReveal>
          <div className="mt-14 grid gap-px overflow-hidden border border-charcoal/10 bg-charcoal/10 sm:grid-cols-2 lg:grid-cols-4">
            {values.map((v, i) => (
              <SectionReveal key={v.title} delay={i * 0.08} className="h-full bg-ivory p-8">
                <span className="font-serif text-2xl text-gold">0{i + 1}</span>
                <h3 className="mt-3 text-lg text-charcoal">{v.title}</h3>
                <p className="mt-2 text-sm text-graysoft">{v.body}</p>
              </SectionReveal>
            ))}
          </div>
        </Container>
      </section>

      {/* Method */}
      <section className="bg-white py-20 md:py-28">
        <Container>
          <SectionReveal>
            <SectionHeading
              eyebrow="How We Work"
              title="The Interior Specifics Method™"
              intro="A proven, five-step process refined across hundreds of projects."
            />
          </SectionReveal>
          <div className="mt-14">
            <ProcessTimeline />
          </div>
        </Container>
      </section>

      {/* Directors */}
      <section id="directors" className="bg-ivory py-20 md:py-28 scroll-mt-24">
        <Container>
          <SectionReveal>
            <SectionHeading eyebrow="Leadership" title="Meet the Directors" />
          </SectionReveal>
          <div className="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            {directors.map((d, i) => (
              <SectionReveal key={d.name} delay={i * 0.1} className="bg-white">
                <div className="relative aspect-[3/4] overflow-hidden">
                  <Image
                    src={d.img}
                    alt={d.name}
                    fill
                    sizes="(max-width: 768px) 100vw, 33vw"
                    className="object-cover"
                  />
                </div>
                <div className="p-6">
                  <h3 className="text-lg text-charcoal">{d.name}</h3>
                  <p className="mt-1 text-sm text-gold">{d.role}</p>
                </div>
              </SectionReveal>
            ))}
          </div>
        </Container>
      </section>

      {/* Awards & Clients */}
      <section id="awards" className="bg-charcoal py-20 md:py-28 scroll-mt-24">
        <Container>
          <SectionReveal>
            <SectionHeading
              eyebrow="Recognition"
              title="Awards & Recognition"
              light
            />
          </SectionReveal>
          <div className="mx-auto mt-12 grid max-w-3xl gap-px overflow-hidden border border-ivory/10 bg-ivory/10 sm:grid-cols-2">
            {awards.map((a, i) => (
              <SectionReveal key={a} delay={i * 0.06} className="bg-charcoal p-6">
                <p className="text-sm text-ivory/80">{a}</p>
              </SectionReveal>
            ))}
          </div>
          <p id="clients" className="mx-auto mt-10 max-w-2xl text-center text-ivory/60 scroll-mt-24">
            Trusted by homeowners, property developers and corporate clients across
            Lagos and beyond.
          </p>
        </Container>
      </section>

      <CTASection
        eyebrow="Work With Us"
        title="Partner With Our Team"
        intro="Tell us about your space and your ambitions. We'll show you what's possible."
      />
    </>
  );
}
