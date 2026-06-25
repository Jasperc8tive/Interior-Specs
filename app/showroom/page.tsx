import type { Metadata } from "next";
import PageHero from "@/components/PageHero";
import CTASection from "@/components/CTASection";
import ShowroomFilter from "@/components/ShowroomFilter";
import SectionReveal from "@/components/SectionReveal";
import RevealHeading from "@/components/RevealHeading";
import { Button, Container } from "@/components/ui";
import { site } from "@/lib/site";

export const metadata: Metadata = {
  title: "Showroom — Our Portfolio of Luxury Interiors",
  description:
    "Explore case studies of luxury residential, commercial and hospitality interiors across Lagos, and book a private showroom tour with Interior Specifics.",
};

export default function ShowroomPage() {
  return (
    <>
      <PageHero
        eyebrow="Showroom & Portfolio"
        title="Spaces that speak of achievement"
        accent={{ from: 3, to: 4 }}
        intro="Explore our completed work and visit our Lagos showroom to discover curated furniture, fabrics and finishes."
        image="https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=2000&q=80"
        imageAlt="Curated luxury showroom interior"
      />

      {/* Filterable project archive */}
      <section className="bg-ivory py-24 md:py-32">
        <Container>
          <SectionReveal>
            <RevealHeading
              eyebrow="Case Studies"
              title="Our recent projects"
              intro="Filter by discipline, then open any project to see the full story behind it."
            />
          </SectionReveal>
          <div className="mt-16">
            <ShowroomFilter />
          </div>
        </Container>
      </section>

      {/* Visit */}
      <section className="grain relative bg-charcoal py-24 md:py-32">
        <Container>
          <div className="grid items-center gap-14 lg:grid-cols-2 lg:gap-20">
            <SectionReveal>
              <RevealHeading
                eyebrow="Visit Us"
                title="Book a private showroom tour"
                align="left"
                light
              />
              <p className="mt-7 text-lg text-ivory/75">
                Step inside our Lagos showroom to explore curated collections of
                furniture, lighting, textiles and art — and discuss your project with
                our team in person.
              </p>
              <ul className="mt-7 space-y-3 border-t border-line-light pt-7 text-sm text-ivory/70">
                <li>{site.address.street}, {site.address.city}</li>
                <li>{site.hours}</li>
              </ul>
              <div className="mt-8 flex flex-col gap-4 sm:flex-row">
                <Button href={site.bookingUrl} variant="primary">
                  Book a Showroom Tour
                </Button>
                <Button href="/contact" variant="outline">
                  Schedule a Virtual Consultation
                </Button>
              </div>
            </SectionReveal>
            {/* TODO(integrations): replace with a real Google Maps embed. */}
            <SectionReveal
              delay={0.1}
              className="flex aspect-[4/3] items-center justify-center border border-line-light bg-charcoal-light text-sm uppercase tracking-[0.2em] text-ivory/40"
            >
              Map · Lagos Showroom
            </SectionReveal>
          </div>
        </Container>
      </section>

      <CTASection />
    </>
  );
}
