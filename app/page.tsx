import Image from "next/image";
import Link from "next/link";
import Hero from "@/components/Hero";
import ProcessTimeline from "@/components/ProcessTimeline";
import PortfolioGrid from "@/components/PortfolioGrid";
import TestimonialCarousel from "@/components/TestimonialCarousel";
import LeadMagnet from "@/components/LeadMagnet";
import SectionReveal from "@/components/SectionReveal";
import { Button, Container, SectionHeading } from "@/components/ui";
import { featuredProjects } from "@/lib/projects";
import { featuredServices } from "@/lib/services";
import { posts } from "@/lib/posts";

const valueProps = [
  {
    title: "Bespoke Design",
    body: "No templates. No compromises. Only your vision, perfected.",
  },
  {
    title: "Turnkey Execution",
    body: "From concept to completion. We handle absolutely everything.",
  },
  {
    title: "Luxury Without Stress",
    body: "A seamless process designed for busy, successful people.",
  },
];

const whyChooseUs = [
  { title: "End-to-End Service", body: "Concept to completion, under one roof." },
  { title: "Global Network", body: "Access to international furniture houses and artisans." },
  { title: "Transparent Process", body: "Clear timelines, documented budgets, no surprises." },
  { title: "After-Care Support", body: "We're here long after the reveal." },
];

export default function Home() {
  return (
    <>
      <Hero />

      {/* Problem / Solution + value props */}
      <section className="bg-ivory py-20 md:py-28">
        <Container>
          <SectionReveal>
            <SectionHeading
              eyebrow="Why It Matters"
              title="Your Space Should Reflect How Far You've Come"
              intro="You've worked hard to achieve success. Your home or office should tell that story — with clarity, warmth and undeniable presence."
            />
          </SectionReveal>
          <div className="mt-14 grid gap-8 md:grid-cols-3">
            {valueProps.map((v, i) => (
              <SectionReveal key={v.title} delay={i * 0.1}>
                <div className="h-full border-t-2 border-gold bg-white p-8">
                  <h3 className="text-xl text-charcoal">{v.title}</h3>
                  <p className="mt-3 text-graysoft">{v.body}</p>
                </div>
              </SectionReveal>
            ))}
          </div>
        </Container>
      </section>

      {/* The Interior Specifics Method */}
      <section className="bg-white py-20 md:py-28">
        <Container>
          <SectionReveal>
            <SectionHeading
              eyebrow="The Interior Specifics Method™"
              title="Our Proven Process for Creating Exceptional Spaces"
              intro="Five deliberate steps that turn ambition into a space you'll love for years."
            />
          </SectionReveal>
          <div className="mt-14">
            <ProcessTimeline />
          </div>
          <div className="mt-10 text-center">
            <Button href="/about#profile" variant="dark">
              Experience the Method
            </Button>
          </div>
        </Container>
      </section>

      {/* Featured projects */}
      <section id="portfolio" className="bg-charcoal py-20 md:py-28">
        <Container>
          <SectionReveal>
            <SectionHeading
              eyebrow="Portfolio"
              title="Spaces That Speak of Achievement"
              intro="A selection of recent residential, commercial and hospitality work across Lagos."
              light
            />
          </SectionReveal>
          <div className="mt-14">
            <PortfolioGrid items={featuredProjects} />
          </div>
          <div className="mt-12 text-center">
            <Button href="/showroom" variant="outline">
              View the Full Showroom
            </Button>
          </div>
        </Container>
      </section>

      {/* Why choose us */}
      <section className="bg-ivory py-20 md:py-28">
        <Container>
          <SectionReveal>
            <SectionHeading
              eyebrow="Trust"
              title="Why Nigeria's Elite Choose Interior Specifics"
            />
          </SectionReveal>
          <div className="mt-14 grid gap-px overflow-hidden border border-charcoal/10 bg-charcoal/10 sm:grid-cols-2 lg:grid-cols-4">
            {whyChooseUs.map((w, i) => (
              <SectionReveal key={w.title} delay={i * 0.08} className="h-full bg-ivory p-8">
                <span className="font-serif text-2xl text-gold">0{i + 1}</span>
                <h3 className="mt-3 text-lg text-charcoal">{w.title}</h3>
                <p className="mt-2 text-sm text-graysoft">{w.body}</p>
              </SectionReveal>
            ))}
          </div>
        </Container>
      </section>

      {/* Testimonials */}
      <section className="bg-charcoal py-20 md:py-28">
        <Container>
          <SectionReveal>
            <SectionHeading eyebrow="Clients" title="What Our Clients Say" light />
          </SectionReveal>
          <div className="mt-14">
            <TestimonialCarousel />
          </div>
        </Container>
      </section>

      {/* Founder */}
      <section className="bg-white py-20 md:py-28">
        <Container>
          <div className="grid items-center gap-12 lg:grid-cols-2">
            <SectionReveal className="relative aspect-[4/5] overflow-hidden">
              <Image
                src="https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&w=1000&q=80"
                alt="Founder of Interior Specifics"
                fill
                sizes="(max-width: 1024px) 100vw, 50vw"
                className="object-cover"
              />
            </SectionReveal>
            <SectionReveal delay={0.1}>
              <SectionHeading
                eyebrow="Leadership"
                title="Meet the Visionary Behind Interior Specifics"
                align="left"
              />
              <p className="mt-6 text-graysoft">
                With over fifteen years shaping Nigeria&apos;s finest interiors, our
                founder built Interior Specifics on a simple conviction: every
                accomplished person deserves a space that honours their journey.
              </p>
              <blockquote className="mt-6 border-l-2 border-gold pl-6 font-accent text-xl italic text-charcoal">
                &ldquo;I believe every successful person deserves a space that honours
                their journey.&rdquo;
              </blockquote>
              <div className="mt-8">
                <Button href="/about#directors" variant="dark">
                  Read Our Story
                </Button>
              </div>
            </SectionReveal>
          </div>
        </Container>
      </section>

      {/* Services overview */}
      <section className="bg-ivory py-20 md:py-28">
        <Container>
          <SectionReveal>
            <SectionHeading
              eyebrow="What We Do"
              title="Comprehensive Design Services"
              intro="From full residences to corporate headquarters, we deliver every discipline in-house."
            />
          </SectionReveal>
          <div className="mt-14 grid gap-6 md:grid-cols-3">
            {featuredServices.map((s, i) => (
              <SectionReveal key={s.slug} delay={i * 0.1}>
                <Link
                  href={`/services/${s.slug}`}
                  className="group block h-full overflow-hidden bg-white"
                >
                  <div className="relative aspect-[16/10] overflow-hidden">
                    <Image
                      src={s.image}
                      alt={s.title}
                      fill
                      sizes="(max-width: 768px) 100vw, 33vw"
                      className="object-cover transition-transform duration-700 group-hover:scale-105"
                    />
                  </div>
                  <div className="p-7">
                    <h3 className="text-xl text-charcoal">{s.title}</h3>
                    <p className="mt-3 text-sm text-graysoft">{s.short}</p>
                    <span className="mt-4 inline-block text-xs font-semibold uppercase tracking-[0.15em] text-gold">
                      Learn More →
                    </span>
                  </div>
                </Link>
              </SectionReveal>
            ))}
          </div>
          <div className="mt-12 text-center">
            <Button href="/services" variant="dark">
              View All Services
            </Button>
          </div>
        </Container>
      </section>

      {/* Blog teaser */}
      <section className="bg-white py-20 md:py-28">
        <Container>
          <SectionReveal>
            <SectionHeading
              eyebrow="Journal"
              title="Design Insights for the Discerning"
            />
          </SectionReveal>
          <div className="mt-14 grid gap-8 md:grid-cols-3">
            {posts.slice(0, 3).map((p, i) => (
              <SectionReveal key={p.slug} delay={i * 0.1}>
                <Link href={`/blog/${p.slug}`} className="group block">
                  <div className="relative aspect-[16/10] overflow-hidden">
                    <Image
                      src={p.image}
                      alt={p.title}
                      fill
                      sizes="(max-width: 768px) 100vw, 33vw"
                      className="object-cover transition-transform duration-700 group-hover:scale-105"
                    />
                  </div>
                  <span className="mt-4 inline-block text-xs font-semibold uppercase tracking-[0.15em] text-gold">
                    {p.category} · {p.readTime}
                  </span>
                  <h3 className="mt-2 text-lg text-charcoal transition-colors group-hover:text-gold">
                    {p.title}
                  </h3>
                  <p className="mt-2 text-sm text-graysoft">{p.excerpt}</p>
                </Link>
              </SectionReveal>
            ))}
          </div>
          <div className="mt-12 text-center">
            <Button href="/blog" variant="dark">
              Read More on Our Journal
            </Button>
          </div>
        </Container>
      </section>

      <LeadMagnet />
    </>
  );
}
