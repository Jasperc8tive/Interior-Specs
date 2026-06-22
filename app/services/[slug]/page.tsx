import type { Metadata } from "next";
import { notFound } from "next/navigation";
import Link from "next/link";
import PageHero from "@/components/PageHero";
import CTASection from "@/components/CTASection";
import SectionReveal from "@/components/SectionReveal";
import PortfolioGrid from "@/components/PortfolioGrid";
import { Container, SectionHeading } from "@/components/ui";
import { getService, services } from "@/lib/services";
import { projects } from "@/lib/projects";
import { site } from "@/lib/site";

type Params = { params: Promise<{ slug: string }> };

export function generateStaticParams() {
  return services.map((s) => ({ slug: s.slug }));
}

export async function generateMetadata({ params }: Params): Promise<Metadata> {
  const { slug } = await params;
  const service = getService(slug);
  if (!service) return { title: "Service Not Found" };
  return {
    title: `${service.title} in Lagos`,
    description: service.short,
    keywords: service.keywords,
  };
}

export default async function ServiceDetailPage({ params }: Params) {
  const { slug } = await params;
  const service = getService(slug);
  if (!service) notFound();

  const schema = {
    "@context": "https://schema.org",
    "@type": "Service",
    name: service.title,
    description: service.short,
    provider: { "@type": "InteriorDesigner", name: site.name },
    areaServed: "Lagos, Nigeria",
  };

  // Show three related projects for visual proof.
  const related = projects.slice(0, 3);

  return (
    <>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(schema) }}
      />
      <PageHero
        eyebrow="Service"
        title={service.title}
        intro={service.short}
        image={service.image}
        imageAlt={service.title}
      />

      {/* Overview */}
      <section className="bg-white py-20 md:py-28">
        <Container>
          <div className="grid gap-12 lg:grid-cols-3">
            <SectionReveal className="lg:col-span-2">
              <SectionHeading eyebrow="Overview" title="What This Service Includes" align="left" />
              <div className="mt-6 space-y-4 text-graysoft">
                {service.overview.map((p, i) => (
                  <p key={i}>{p}</p>
                ))}
              </div>
            </SectionReveal>
            <SectionReveal delay={0.1} className="h-fit border-t-2 border-gold bg-ivory p-8">
              <span className="text-xs font-semibold uppercase tracking-[0.2em] text-gold">
                Investment
              </span>
              <p className="mt-3 font-serif text-3xl text-charcoal">
                From {service.investmentFrom}
              </p>
              <p className="mt-3 text-sm text-graysoft">
                Every project is unique. We&apos;ll provide a clear, documented quote
                after your consultation.
              </p>
            </SectionReveal>
          </div>
        </Container>
      </section>

      {/* Process */}
      <section className="bg-ivory py-20 md:py-28">
        <Container>
          <SectionReveal>
            <SectionHeading eyebrow="Process" title="How It Works" />
          </SectionReveal>
          <div className="mt-14 grid gap-px overflow-hidden border border-gold/20 bg-gold/20 sm:grid-cols-2 lg:grid-cols-3">
            {service.process.map((step, i) => (
              <SectionReveal key={step.step} delay={i * 0.08} className="h-full bg-ivory p-7">
                <span className="font-serif text-2xl text-gold">0{i + 1}</span>
                <h3 className="mt-2 text-lg text-charcoal">{step.step}</h3>
                <p className="mt-2 text-sm text-graysoft">{step.detail}</p>
              </SectionReveal>
            ))}
          </div>
        </Container>
      </section>

      {/* Portfolio */}
      <section className="bg-charcoal py-20 md:py-28">
        <Container>
          <SectionReveal>
            <SectionHeading eyebrow="Portfolio" title="Recent Work" light />
          </SectionReveal>
          <div className="mt-14">
            <PortfolioGrid items={related} />
          </div>
        </Container>
      </section>

      {/* FAQ */}
      <section className="bg-white py-20 md:py-28">
        <Container>
          <SectionReveal>
            <SectionHeading eyebrow="Questions" title="Frequently Asked" />
          </SectionReveal>
          <div className="mx-auto mt-12 max-w-3xl divide-y divide-charcoal/10 border-y border-charcoal/10">
            {service.faqs.map((f) => (
              <details key={f.q} className="group py-5">
                <summary className="flex cursor-pointer list-none items-center justify-between text-lg text-charcoal">
                  {f.q}
                  <span className="text-gold transition-transform group-open:rotate-45">+</span>
                </summary>
                <p className="mt-3 text-graysoft">{f.a}</p>
              </details>
            ))}
          </div>
          <div className="mt-12 text-center text-sm text-graysoft">
            Explore more services on our{" "}
            <Link href="/services" className="text-gold underline-offset-4 hover:underline">
              services overview
            </Link>
            .
          </div>
        </Container>
      </section>

      <CTASection
        eyebrow="Begin"
        title={`Start Your ${service.title} Project`}
        primaryLabel="Book Your Consultation"
      />
    </>
  );
}
