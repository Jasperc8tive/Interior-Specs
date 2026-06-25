import type { Metadata } from "next";
import { notFound } from "next/navigation";
import Image from "next/image";
import Link from "next/link";
import CTASection from "@/components/CTASection";
import Gallery from "@/components/Gallery";
import SectionReveal from "@/components/SectionReveal";
import RevealHeading from "@/components/RevealHeading";
import AnimatedHeading from "@/components/home/AnimatedHeading";
import { Container } from "@/components/ui";
import { adjacentProjects, getProject, projects } from "@/lib/projects";
import { site } from "@/lib/site";

type Params = { params: Promise<{ slug: string }> };

export function generateStaticParams() {
  return projects.map((p) => ({ slug: p.slug }));
}

export async function generateMetadata({ params }: Params): Promise<Metadata> {
  const { slug } = await params;
  const project = getProject(slug);
  if (!project) return { title: "Project Not Found" };
  return {
    title: `${project.title} — ${project.type} Case Study`,
    description: project.blurb,
    openGraph: {
      title: `${project.title} — Interior Specifics`,
      description: project.blurb,
      type: "article",
      images: [project.image],
    },
  };
}

export default async function ProjectPage({ params }: Params) {
  const { slug } = await params;
  const project = getProject(slug);
  if (!project) notFound();
  const { prev, next } = adjacentProjects(slug);

  const schema = {
    "@context": "https://schema.org",
    "@type": "CreativeWork",
    name: project.title,
    description: project.blurb,
    image: [project.image, ...project.gallery],
    locationCreated: { "@type": "Place", name: project.location },
    creator: { "@type": "Organization", name: site.name },
  };

  const facts = [
    { label: "Location", value: project.location },
    { label: "Type", value: project.type },
    { label: "Investment", value: project.budget },
    { label: "Timeline", value: project.timeline },
    { label: "Completed", value: project.year },
  ];

  return (
    <>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(schema) }}
      />

      {/* Hero */}
      <section className="grain relative -mt-[88px] flex min-h-[72vh] items-end overflow-hidden">
        <Image
          src={project.image}
          alt={`${project.title} — ${project.type} interior design in ${project.location}`}
          fill
          priority
          sizes="100vw"
          className="object-cover"
        />
        <div className="hero-overlay absolute inset-0" />
        <Container className="relative z-10 pb-16 pt-[88px]">
          <Link
            href="/showroom"
            className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-gold-soft transition-colors hover:text-gold"
          >
            <span aria-hidden>←</span> Showroom
          </Link>
          <AnimatedHeading
            as="h1"
            text={project.title}
            delay={0.15}
            className="display mt-5 max-w-3xl text-4xl text-ivory sm:text-5xl lg:text-6xl"
          />
          <p className="mt-4 text-sm uppercase tracking-[0.2em] text-ivory/70">
            {project.type} · {project.location} · {project.year}
          </p>
        </Container>
      </section>

      {/* Facts + scope */}
      <section className="bg-white py-16 md:py-20">
        <Container>
          <div className="grid gap-px overflow-hidden border border-line bg-line sm:grid-cols-2 lg:grid-cols-5">
            {facts.map((f) => (
              <div key={f.label} className="bg-white p-6">
                <span className="block text-xs font-semibold uppercase tracking-[0.15em] text-gold">
                  {f.label}
                </span>
                <span className="mt-2 block text-charcoal">{f.value}</span>
              </div>
            ))}
          </div>
          <div className="mt-8 flex flex-wrap gap-2">
            {project.scope.map((s) => (
              <span
                key={s}
                className="border border-line px-4 py-1.5 text-xs uppercase tracking-[0.12em] text-muted"
              >
                {s}
              </span>
            ))}
          </div>
        </Container>
      </section>

      {/* Narrative */}
      <section className="bg-ivory py-24 md:py-32">
        <Container>
          <div className="mx-auto grid max-w-5xl gap-12 md:grid-cols-2 md:gap-16">
            <SectionReveal>
              <span className="eyebrow text-gold">The Challenge</span>
              <p className="mt-6 text-lg text-muted">{project.challenge}</p>
            </SectionReveal>
            <SectionReveal delay={0.1}>
              <span className="eyebrow text-gold">Our Solution</span>
              <p className="mt-6 text-lg text-muted">{project.solution}</p>
            </SectionReveal>
          </div>
        </Container>
      </section>

      {/* Gallery */}
      <section className="bg-white py-16 md:py-24">
        <Container>
          <SectionReveal className="mb-12">
            <RevealHeading eyebrow="Gallery" title="Inside the project" align="left" />
          </SectionReveal>
          <Gallery images={[project.image, ...project.gallery]} title={project.title} />
        </Container>
      </section>

      {/* Results */}
      <section className="grain relative bg-charcoal py-24 md:py-32">
        <Container>
          <div className="mx-auto max-w-3xl text-center">
            <SectionReveal>
              <RevealHeading eyebrow="The Outcome" title="Results" light />
              <p className="mt-7 text-lg text-ivory/80">{project.results}</p>
            </SectionReveal>
            {project.testimonial && (
              <SectionReveal delay={0.1}>
                <blockquote className="accent mt-12 text-2xl leading-relaxed text-ivory md:text-3xl">
                  &ldquo;{project.testimonial.quote}&rdquo;
                </blockquote>
                <p className="mt-5 text-sm uppercase tracking-[0.15em] text-gold">
                  {project.testimonial.name}
                </p>
              </SectionReveal>
            )}
          </div>
        </Container>
      </section>

      {/* Prev / next */}
      <section className="bg-ivory py-14">
        <Container>
          <div className="grid gap-4 sm:grid-cols-2">
            {prev && (
              <Link
                href={`/showroom/${prev.slug}`}
                className="group flex flex-col border border-line bg-white p-7 transition-colors duration-300 hover:border-gold"
              >
                <span className="text-xs font-semibold uppercase tracking-[0.15em] text-gold">
                  ← Previous Project
                </span>
                <span className="mt-2 text-lg text-charcoal transition-colors group-hover:text-gold">
                  {prev.title}
                </span>
              </Link>
            )}
            {next && (
              <Link
                href={`/showroom/${next.slug}`}
                className="group flex flex-col items-end border border-line bg-white p-7 text-right transition-colors duration-300 hover:border-gold sm:col-start-2"
              >
                <span className="text-xs font-semibold uppercase tracking-[0.15em] text-gold">
                  Next Project →
                </span>
                <span className="mt-2 text-lg text-charcoal transition-colors group-hover:text-gold">
                  {next.title}
                </span>
              </Link>
            )}
          </div>
        </Container>
      </section>

      <CTASection
        eyebrow="Begin"
        title="Imagine what we could do with your space"
        primaryLabel="Book Your Private Consultation"
      />
    </>
  );
}
