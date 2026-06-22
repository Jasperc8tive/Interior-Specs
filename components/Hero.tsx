import Image from "next/image";
import { Button, Container } from "@/components/ui";
import { site } from "@/lib/site";

// Full-bleed hero. Static premium image (swap for a video background later).
export default function Hero() {
  return (
    <section className="relative -mt-[88px] flex min-h-[92vh] items-center">
      <Image
        src="https://images.unsplash.com/photo-1618219908412-a29a1bb7b86e?auto=format&fit=crop&w=2000&q=80"
        alt="Luxury living room interior with warm natural light"
        fill
        priority
        sizes="100vw"
        className="object-cover"
      />
      <div className="hero-overlay absolute inset-0" />

      <Container className="relative z-10 pt-[88px]">
        <div className="max-w-3xl">
          <span className="text-xs font-semibold uppercase tracking-[0.3em] text-gold-soft">
            Lagos&apos; Premier Luxury Design Firm
          </span>
          <h1 className="mt-6 text-4xl leading-tight text-ivory sm:text-5xl lg:text-6xl">
            Designing Spaces Worthy of Achievement
          </h1>
          <p className="mt-6 max-w-xl text-lg text-ivory/85">
            Bespoke interior design for Nigeria&apos;s most discerning homeowners
            and businesses. From concept to completion, under one roof.
          </p>
          <div className="mt-9 flex flex-col gap-4 sm:flex-row">
            <Button href={site.calendlyUrl} variant="primary">
              Book Your Private Consultation
            </Button>
            <Button href="#portfolio" variant="outline">
              View Our Portfolio
            </Button>
          </div>

          <div className="mt-12 flex flex-wrap gap-8 border-t border-ivory/20 pt-6">
            {site.stats.map((s) => (
              <div key={s.label}>
                <div className="font-serif text-2xl text-gold md:text-3xl">
                  {s.value}
                </div>
                <div className="text-xs uppercase tracking-[0.15em] text-ivory/70">
                  {s.label}
                </div>
              </div>
            ))}
          </div>
        </div>
      </Container>
    </section>
  );
}
