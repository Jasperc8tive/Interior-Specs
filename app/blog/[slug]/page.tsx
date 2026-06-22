import type { Metadata } from "next";
import { notFound } from "next/navigation";
import Image from "next/image";
import Link from "next/link";
import CTASection from "@/components/CTASection";
import { Container } from "@/components/ui";
import { getPost, posts } from "@/lib/posts";
import { site } from "@/lib/site";

type Params = { params: Promise<{ slug: string }> };

export function generateStaticParams() {
  return posts.map((p) => ({ slug: p.slug }));
}

export async function generateMetadata({ params }: Params): Promise<Metadata> {
  const { slug } = await params;
  const post = getPost(slug);
  if (!post) return { title: "Article Not Found" };
  return {
    title: post.title,
    description: post.excerpt,
    openGraph: {
      title: post.title,
      description: post.excerpt,
      type: "article",
      images: [post.image],
    },
  };
}

function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

export default async function BlogPostPage({ params }: Params) {
  const { slug } = await params;
  const post = getPost(slug);
  if (!post) notFound();

  const schema = {
    "@context": "https://schema.org",
    "@type": "Article",
    headline: post.title,
    description: post.excerpt,
    image: post.image,
    datePublished: post.date,
    author: { "@type": "Organization", name: site.name },
    publisher: { "@type": "Organization", name: site.name },
  };

  return (
    <>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(schema) }}
      />

      {/* Hero */}
      <section className="relative -mt-[88px] flex min-h-[60vh] items-end">
        <Image
          src={post.image}
          alt={post.title}
          fill
          priority
          sizes="100vw"
          className="object-cover"
        />
        <div className="hero-overlay absolute inset-0" />
        <Container className="relative z-10 pb-14 pt-[88px]">
          <div className="max-w-3xl">
            <span className="text-xs font-semibold uppercase tracking-[0.2em] text-gold-soft">
              {post.category} · {formatDate(post.date)} · {post.readTime}
            </span>
            <h1 className="mt-4 text-3xl leading-tight text-ivory sm:text-4xl lg:text-5xl">
              {post.title}
            </h1>
          </div>
        </Container>
      </section>

      {/* Body */}
      <article className="bg-white py-16 md:py-24">
        <Container>
          <div className="mx-auto max-w-3xl">
            <p className="font-accent text-xl italic text-graysoft">{post.excerpt}</p>
            <div className="mt-10 space-y-6">
              {post.body.map((block, i) =>
                block.type === "h2" ? (
                  <h2 key={i} className="pt-4 text-2xl text-charcoal">
                    {block.text}
                  </h2>
                ) : (
                  <p key={i} className="text-lg leading-relaxed text-charcoal/80">
                    {block.text}
                  </p>
                ),
              )}
            </div>

            <div className="mt-14 border-t border-charcoal/10 pt-8">
              <Link
                href="/blog"
                className="text-sm font-semibold uppercase tracking-[0.15em] text-gold hover:underline"
              >
                ← Back to the Journal
              </Link>
            </div>
          </div>
        </Container>
      </article>

      <CTASection />
    </>
  );
}
