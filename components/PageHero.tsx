import Image from "next/image";
import { Container } from "@/components/ui";

// Compact hero for inner pages. Slides beneath the transparent sticky header.
export default function PageHero({
  eyebrow,
  title,
  intro,
  image,
  imageAlt,
}: {
  eyebrow?: string;
  title: string;
  intro?: string;
  image: string;
  imageAlt: string;
}) {
  return (
    <section className="relative -mt-[88px] flex min-h-[60vh] items-center">
      <Image
        src={image}
        alt={imageAlt}
        fill
        priority
        sizes="100vw"
        className="object-cover"
      />
      <div className="hero-overlay absolute inset-0" />
      <Container className="relative z-10 pt-[88px] text-center">
        <div className="mx-auto max-w-3xl">
          {eyebrow && (
            <span className="text-xs font-semibold uppercase tracking-[0.3em] text-gold-soft">
              {eyebrow}
            </span>
          )}
          <h1 className="mt-5 text-4xl leading-tight text-ivory sm:text-5xl">
            {title}
          </h1>
          <div className="gold-rule mx-auto mt-6" />
          {intro && (
            <p className="mx-auto mt-6 max-w-2xl text-lg text-ivory/85">{intro}</p>
          )}
        </div>
      </Container>
    </section>
  );
}
