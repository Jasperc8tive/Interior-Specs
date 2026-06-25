import type { Metadata } from "next";
import PageHero from "@/components/PageHero";
import ContactForm from "@/components/ContactForm";
import SectionReveal from "@/components/SectionReveal";
import RevealHeading from "@/components/RevealHeading";
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

const details = [
  { label: "Office", value: `${site.address.street}, ${site.address.city}, ${site.address.country}` },
  { label: "Phone", value: site.phoneDisplay, href: `tel:${site.phoneHref}` },
  { label: "Email", value: site.email, href: `mailto:${site.email}` },
  { label: "WhatsApp", value: "Start a chat", href: whatsappLink(), external: true },
  { label: "Hours", value: site.hours },
];

export default function ContactPage() {
  return (
    <>
      <PageHero
        eyebrow="Contact"
        title="Let's create something exceptional"
        accent={{ from: 2, to: 3 }}
        intro="Ready to transform your space? We'd love to hear from you."
        image="https://images.unsplash.com/photo-1556912172-45b7abe8b7e1?auto=format&fit=crop&w=2000&q=80"
        imageAlt="Luxury interior detail"
      />

      <section className="bg-white py-24 md:py-32">
        <Container>
          <div className="grid gap-12 lg:grid-cols-5 lg:gap-16">
            <SectionReveal className="lg:col-span-3">
              <RevealHeading eyebrow="Send a Message" title="Tell us about your project" align="left" />
              <div className="mt-8">
                <ContactForm />
              </div>
            </SectionReveal>

            <SectionReveal delay={0.1} className="lg:col-span-2">
              <div className="border-t-2 border-gold bg-ivory p-8">
                <h2 className="text-2xl text-charcoal">Get in touch</h2>
                <dl className="mt-7 space-y-6 text-sm">
                  {details.map((d) => (
                    <div key={d.label}>
                      <dt className="text-xs font-semibold uppercase tracking-[0.18em] text-gold">
                        {d.label}
                      </dt>
                      <dd className="mt-1.5">
                        {d.href ? (
                          <a
                            href={d.href}
                            {...(d.external ? { target: "_blank", rel: "noopener noreferrer" } : {})}
                            className="text-charcoal transition-colors hover:text-gold"
                          >
                            {d.value}
                          </a>
                        ) : (
                          <span className="text-muted">{d.value}</span>
                        )}
                      </dd>
                    </div>
                  ))}
                </dl>
              </div>

              {/* TODO(integrations): replace with a real Google Maps embed. */}
              <div className="mt-6 flex aspect-[4/3] items-center justify-center border border-line bg-ivory text-sm uppercase tracking-[0.2em] text-muted">
                Map · Lagos Office
              </div>
            </SectionReveal>
          </div>

          {/* FAQ */}
          <div className="mx-auto mt-24 max-w-3xl">
            <SectionReveal>
              <RevealHeading eyebrow="Good to Know" title="Before you reach out" />
            </SectionReveal>
            <div className="mt-10 divide-y divide-line border-y border-line">
              {faqs.map((f) => (
                <details key={f.q} className="group py-6">
                  <summary className="flex cursor-pointer list-none items-center justify-between gap-4 text-lg text-charcoal transition-colors group-hover:text-gold">
                    {f.q}
                    <span className="text-2xl font-light text-gold transition-transform duration-300 group-open:rotate-45">+</span>
                  </summary>
                  <p className="mt-3 text-muted">{f.a}</p>
                </details>
              ))}
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
