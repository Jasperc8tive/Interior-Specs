import type { Metadata } from "next";
import PageHero from "@/components/PageHero";
import BookingForm from "@/components/BookingForm";
import SectionReveal from "@/components/SectionReveal";
import { Container, SectionHeading } from "@/components/ui";
import { site, whatsappLink } from "@/lib/site";

export const metadata: Metadata = {
  title: "Book a Consultation — Private & Complimentary",
  description:
    "Book your complimentary 30-minute design consultation with Interior Specifics, Lagos' premier luxury interior design firm. In-person, virtual or by phone.",
};

const expect = [
  { title: "A focused conversation", body: "We listen to your vision, lifestyle and goals for the space." },
  { title: "Expert perspective", body: "Honest, experienced guidance on what's possible and what it takes." },
  { title: "Clear next steps", body: "A tailored proposal and timeline — with no obligation to proceed." },
];

export default function BookPage() {
  const hasScheduler = Boolean(site.calendlyUrl);

  return (
    <>
      <PageHero
        eyebrow="Private Consultation"
        title="Book Your Private Consultation"
        intro="A complimentary 30-minute conversation about your space — in person at our Lagos showroom, by video, or over the phone."
        image="https://images.unsplash.com/photo-1567016526105-22da7c13161a?auto=format&fit=crop&w=2000&q=80"
        imageAlt="Elegant consultation lounge interior"
      />

      {/* What to expect */}
      <section className="bg-ivory py-20 md:py-28">
        <Container>
          <SectionReveal>
            <SectionHeading eyebrow="What to Expect" title="Your First 30 Minutes With Us" />
          </SectionReveal>
          <div className="mt-14 grid gap-px overflow-hidden border border-charcoal/10 bg-charcoal/10 md:grid-cols-3">
            {expect.map((e, i) => (
              <SectionReveal key={e.title} delay={i * 0.1} className="h-full bg-ivory p-8">
                <span className="font-serif text-2xl text-gold">0{i + 1}</span>
                <h3 className="mt-3 text-lg text-charcoal">{e.title}</h3>
                <p className="mt-2 text-sm text-graysoft">{e.body}</p>
              </SectionReveal>
            ))}
          </div>
        </Container>
      </section>

      {/* Scheduler + request form */}
      <section className="bg-white py-20 md:py-28">
        <Container>
          <div className="grid gap-12 lg:grid-cols-5">
            <SectionReveal className="lg:col-span-3">
              <SectionHeading
                eyebrow="Request a Time"
                title="Tell Us When Suits You"
                align="left"
              />
              <p className="mt-4 text-graysoft">
                Share your preferred date and time and we&apos;ll confirm within 24
                hours. Prefer to pick a slot instantly? Use the scheduler.
              </p>
              <div className="mt-8">
                <BookingForm />
              </div>
            </SectionReveal>

            {/* Calendly slot */}
            <SectionReveal delay={0.1} className="lg:col-span-2">
              {hasScheduler ? (
                // TODO(integrations): confirm this is your Calendly event URL.
                <iframe
                  src={site.calendlyUrl}
                  title="Schedule a consultation"
                  className="h-[640px] w-full border border-charcoal/10"
                />
              ) : (
                <div className="flex h-full flex-col justify-center border-t-2 border-gold bg-ivory p-8">
                  <h3 className="text-xl text-charcoal">Instant Scheduling</h3>
                  <p className="mt-3 text-sm text-graysoft">
                    {/* TODO(integrations): set site.calendlyUrl to embed the live
                        scheduler here. Until then, the request form is fully active. */}
                    Live calendar booking will appear here once connected. In the
                    meantime, the request form gets you the same result — we&apos;ll
                    confirm your slot personally.
                  </p>
                  <div className="mt-6 space-y-2 text-sm">
                    <a href={`tel:${site.phoneHref}`} className="block text-charcoal hover:text-gold">
                      Call us: {site.phoneDisplay}
                    </a>
                    <a
                      href={whatsappLink()}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="block text-charcoal hover:text-gold"
                    >
                      Or message us on WhatsApp
                    </a>
                  </div>
                  <p className="mt-6 text-xs text-graysoft">{site.hours}</p>
                </div>
              )}
            </SectionReveal>
          </div>
        </Container>
      </section>
    </>
  );
}
