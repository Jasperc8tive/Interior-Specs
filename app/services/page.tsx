import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import PageHero from "@/components/PageHero";
import CTASection from "@/components/CTASection";
import SectionReveal from "@/components/SectionReveal";
import { Container } from "@/components/ui";
import { services } from "@/lib/services";

export const metadata: Metadata = {
  title: "Services — End-to-End Interior Design Solutions",
  description:
    "From residential and commercial design to bespoke furniture and lighting, explore the full range of luxury interior design services from Interior Specifics, Lagos.",
};

export default function ServicesPage() {
  return (
    <>
      <PageHero
        eyebrow="Our Services"
        title="End-to-End Interior Design Solutions"
        intro="From concept to completion, we bring your vision to life — every discipline handled in-house."
        image="https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&w=2000&q=80"
        imageAlt="Beautifully finished luxury interior"
      />

      <section className="bg-ivory py-20 md:py-28">
        <Container>
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {services.map((s, i) => (
              <SectionReveal key={s.slug} delay={(i % 3) * 0.08}>
                <Link
                  href={`/services/${s.slug}`}
                  className="group flex h-full flex-col overflow-hidden bg-white"
                >
                  <div className="relative aspect-[16/10] overflow-hidden">
                    <Image
                      src={s.image}
                      alt={s.title}
                      fill
                      sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                      className="object-cover transition-transform duration-700 group-hover:scale-105"
                    />
                  </div>
                  <div className="flex flex-1 flex-col p-7">
                    <h2 className="text-xl text-charcoal">{s.title}</h2>
                    <p className="mt-3 flex-1 text-sm text-graysoft">{s.short}</p>
                    <span className="mt-5 inline-block text-xs font-semibold uppercase tracking-[0.15em] text-gold">
                      From {s.investmentFrom} · Learn More →
                    </span>
                  </div>
                </Link>
              </SectionReveal>
            ))}
          </div>
        </Container>
      </section>

      <CTASection />
    </>
  );
}
