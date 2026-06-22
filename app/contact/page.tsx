import type { Metadata } from "next";
import PageHero from "@/components/PageHero";
import ContactForm from "@/components/ContactForm";
import SectionReveal from "@/components/SectionReveal";
import { Container } from "@/components/ui";
import { site, whatsappLink } from "@/lib/site";

export const metadata: Metadata = {
  title: "Contact — Let's Create Something Exceptional",
  description:
    "Get in touch with Interior Specifics, Lagos' premier luxury interior design firm. Book a consultation or send us your project details.",
};

const faqs = [
  { q: "What happens after I submit?", a: "A member of our team reviews your inquiry and responds within 24 hours to arrange your consultation." },
  { q: "Do you offer free consultations?", a: "Yes — your initial 30-minute design consultation is complimentary and without obligation." },
  { q: "What's your typical response time?", a: "We respond to all inquiries within one business day, often much sooner." },
];

export default function ContactPage() {
  return (
    <>
      <PageHero
        eyebrow="Contact"
        title="Let's Create Something Exceptional"
        intro="Ready to transform your space? We'd love to hear from you."
        image="https://images.unsplash.com/photo-1556912172-45b7abe8b7e1?auto=format&fit=crop&w=2000&q=80"
        imageAlt="Luxury interior detail"
      />

      <section className="bg-white py-20 md:py-28">
        <Container>
          <div className="grid gap-12 lg:grid-cols-5">
            <SectionReveal className="lg:col-span-3">
              <ContactForm />
            </SectionReveal>

            <SectionReveal delay={0.1} className="lg:col-span-2">
              <div className="border-t-2 border-gold bg-ivory p-8">
                <h2 className="text-2xl text-charcoal">Get in Touch</h2>
                <dl className="mt-6 space-y-5 text-sm">
                  <div>
                    <dt className="font-semibold uppercase tracking-[0.15em] text-gold">Office</dt>
                    <dd className="mt-1 text-graysoft">
                      {site.address.street}, {site.address.city}, {site.address.country}
                    </dd>
                  </div>
                  <div>
                    <dt className="font-semibold uppercase tracking-[0.15em] text-gold">Phone</dt>
                    <dd className="mt-1">
                      <a href={`tel:${site.phoneHref}`} className="text-charcoal hover:text-gold">
                        {site.phoneDisplay}
                      </a>
                    </dd>
                  </div>
                  <div>
                    <dt className="font-semibold uppercase tracking-[0.15em] text-gold">Email</dt>
                    <dd className="mt-1">
                      <a href={`mailto:${site.email}`} className="text-charcoal hover:text-gold">
                        {site.email}
                      </a>
                    </dd>
                  </div>
                  <div>
                    <dt className="font-semibold uppercase tracking-[0.15em] text-gold">WhatsApp</dt>
                    <dd className="mt-1">
                      <a href={whatsappLink()} target="_blank" rel="noopener noreferrer" className="text-charcoal hover:text-gold">
                        Start a chat
                      </a>
                    </dd>
                  </div>
                  <div>
                    <dt className="font-semibold uppercase tracking-[0.15em] text-gold">Hours</dt>
                    <dd className="mt-1 text-graysoft">{site.hours}</dd>
                  </div>
                </dl>
              </div>

              {/* TODO(integrations): replace with a real Google Maps embed. */}
              <div className="mt-6 flex aspect-[4/3] items-center justify-center border border-charcoal/10 bg-ivory text-sm uppercase tracking-[0.2em] text-graysoft">
                Map · Lagos Office
              </div>
            </SectionReveal>
          </div>

          {/* FAQ */}
          <div className="mx-auto mt-20 max-w-3xl">
            <h2 className="text-center text-2xl text-charcoal">Before You Reach Out</h2>
            <div className="mt-8 divide-y divide-charcoal/10 border-y border-charcoal/10">
              {faqs.map((f) => (
                <details key={f.q} className="group py-5">
                  <summary className="flex cursor-pointer list-none items-center justify-between text-lg text-charcoal">
                    {f.q}
                    <span className="text-gold transition-transform group-open:rotate-45">+</span>
                  </summary>
                  <p className="mt-3 text-graysoft">{f.a}</p>
                </details>
              ))}
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
