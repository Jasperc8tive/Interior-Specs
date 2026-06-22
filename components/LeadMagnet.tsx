"use client";

import { useState } from "react";
import { Container, SectionHeading } from "@/components/ui";
import SectionReveal from "@/components/SectionReveal";

// Lead magnet email capture. Front-end only — wire to your ESP later.
// TODO(integrations): POST to Mailchimp/ConvertKit and trigger the PDF download.
export default function LeadMagnet() {
  const [submitted, setSubmitted] = useState(false);

  return (
    <section className="bg-ivory py-20 md:py-28">
      <Container>
        <SectionReveal className="mx-auto max-w-2xl text-center">
          <SectionHeading
            eyebrow="Free Guide"
            title="The 7 Secrets of Luxury Interior Design in Nigeria"
            intro="Download our complimentary guide, or book a free 30-minute design consultation."
          />
          {submitted ? (
            <p className="mt-10 text-lg text-charcoal">
              Thank you — check your inbox shortly. We&apos;ll be in touch within 24 hours.
            </p>
          ) : (
            <form
              onSubmit={(e) => {
                e.preventDefault();
                setSubmitted(true);
              }}
              className="mx-auto mt-10 flex max-w-xl flex-col gap-4"
            >
              <div className="grid gap-4 sm:grid-cols-2">
                <input
                  required
                  placeholder="Full name"
                  className="min-h-12 border border-charcoal/15 bg-white px-4 py-3 text-sm focus:border-gold focus:outline-none"
                />
                <input
                  required
                  type="email"
                  placeholder="Email address"
                  className="min-h-12 border border-charcoal/15 bg-white px-4 py-3 text-sm focus:border-gold focus:outline-none"
                />
                <input
                  required
                  type="tel"
                  placeholder="Phone number"
                  className="min-h-12 border border-charcoal/15 bg-white px-4 py-3 text-sm focus:border-gold focus:outline-none"
                />
                <select
                  required
                  defaultValue=""
                  className="min-h-12 border border-charcoal/15 bg-white px-4 py-3 text-sm text-charcoal focus:border-gold focus:outline-none"
                >
                  <option value="" disabled>
                    Property type
                  </option>
                  <option>Residential</option>
                  <option>Commercial</option>
                  <option>Hospitality</option>
                  <option>Other</option>
                </select>
              </div>
              <button
                type="submit"
                className="min-h-12 bg-terracotta px-7 py-3 text-sm font-semibold uppercase tracking-[0.15em] text-charcoal transition-colors hover:bg-gold"
              >
                Send Me the Guide
              </button>
            </form>
          )}
        </SectionReveal>
      </Container>
    </section>
  );
}
