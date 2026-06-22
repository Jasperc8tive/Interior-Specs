import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import PageHero from "@/components/PageHero";
import CTASection from "@/components/CTASection";
import SectionReveal from "@/components/SectionReveal";
import { Container } from "@/components/ui";
import { posts } from "@/lib/posts";

export const metadata: Metadata = {
  title: "Journal — Design Insights & Luxury Living",
  description:
    "Expert advice for creating exceptional spaces. Insights on luxury interior design, materials, trends and budgeting from Interior Specifics, Lagos.",
};

function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

export default function BlogPage() {
  const [featured, ...rest] = posts;

  return (
    <>
      <PageHero
        eyebrow="The Journal"
        title="Design Insights & Luxury Living"
        intro="Expert advice for creating exceptional spaces."
        image="https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=2000&q=80"
        imageAlt="Elegant interior reading nook"
      />

      <section className="bg-white py-20 md:py-28">
        <Container>
          {/* Featured post */}
          <SectionReveal>
            <Link
              href={`/blog/${featured.slug}`}
              className="group grid overflow-hidden bg-ivory lg:grid-cols-2"
            >
              <div className="relative aspect-[16/10] overflow-hidden lg:aspect-auto">
                <Image
                  src={featured.image}
                  alt={featured.title}
                  fill
                  sizes="(max-width: 1024px) 100vw, 50vw"
                  className="object-cover transition-transform duration-700 group-hover:scale-105"
                />
              </div>
              <div className="flex flex-col justify-center p-8 md:p-12">
                <span className="text-xs font-semibold uppercase tracking-[0.2em] text-gold">
                  {featured.category} · {featured.readTime}
                </span>
                <h2 className="mt-3 text-2xl text-charcoal md:text-3xl">
                  {featured.title}
                </h2>
                <p className="mt-4 text-graysoft">{featured.excerpt}</p>
                <span className="mt-6 text-xs font-semibold uppercase tracking-[0.15em] text-charcoal group-hover:text-gold">
                  Read Article →
                </span>
              </div>
            </Link>
          </SectionReveal>

          {/* Rest */}
          <div className="mt-14 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            {rest.map((p, i) => (
              <SectionReveal key={p.slug} delay={(i % 3) * 0.08}>
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
                    {p.category} · {formatDate(p.date)}
                  </span>
                  <h3 className="mt-2 text-lg text-charcoal group-hover:text-gold">
                    {p.title}
                  </h3>
                  <p className="mt-2 text-sm text-graysoft">{p.excerpt}</p>
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
