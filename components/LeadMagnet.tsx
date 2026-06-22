"use client";

import { useState } from "react";
import { Container, SectionHeading } from "@/components/ui";
import SectionReveal from "@/components/SectionReveal";
import { honeypotProps, submitLead, type FormStatus } from "@/lib/forms";
import { site, whatsappLink } from "@/lib/site";

// Lead magnet email capture. Submits to /api/lead (Resend).
// TODO(integrations): trigger the actual PDF download/delivery on success.
const inputClass =
  "min-h-12 border border-charcoal/15 bg-white px-4 py-3 text-sm focus:border-gold focus:outline-none";

export default function LeadMagnet() {
  const [status, setStatus] = useState<FormStatus>("idle");

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = e.currentTarget;
    setStatus("loading");
    try {
      await submitLead("lead-magnet", form);
      setStatus("success");
      form.reset();
    } catch {
      setStatus("error");
    }
  }

  return (
    <section className="bg-ivory py-20 md:py-28">
      <Container>
        <SectionReveal className="mx-auto max-w-2xl text-center">
          <SectionHeading
            eyebrow="Free Guide"
            title="The 7 Secrets of Luxury Interior Design in Nigeria"
            intro="Download our complimentary guide, or book a free 30-minute design consultation."
          />
          {status === "success" ? (
            <p className="mt-10 text-lg text-charcoal">
              Thank you — check your inbox shortly. We&apos;ll be in touch within 24 hours.
            </p>
          ) : (
            <form onSubmit={handleSubmit} className="mx-auto mt-10 flex max-w-xl flex-col gap-4">
              <input {...honeypotProps} />
              <div className="grid gap-4 sm:grid-cols-2">
                <input required name="name" aria-label="Full name" placeholder="Full name" className={inputClass} />
                <input required name="email" type="email" aria-label="Email address" placeholder="Email address" className={inputClass} />
                <input required name="phone" type="tel" aria-label="Phone number" placeholder="Phone number" className={inputClass} />
                <select required name="propertyType" aria-label="Property type" defaultValue="" className={`${inputClass} text-charcoal`}>
                  <option value="" disabled>Property type</option>
                  <option>Residential</option>
                  <option>Commercial</option>
                  <option>Hospitality</option>
                  <option>Other</option>
                </select>
              </div>
              {status === "error" && (
                <p className="text-sm text-burgundy">
                  Sorry, something went wrong. Please{" "}
                  <a href={whatsappLink()} target="_blank" rel="noopener noreferrer" className="underline">
                    message us on WhatsApp
                  </a>{" "}
                  or email{" "}
                  <a href={`mailto:${site.email}`} className="underline">{site.email}</a>.
                </p>
              )}
              <button
                type="submit"
                disabled={status === "loading"}
                className="min-h-12 bg-terracotta px-7 py-3 text-sm font-semibold uppercase tracking-[0.15em] text-charcoal transition-colors hover:bg-gold disabled:opacity-60"
              >
                {status === "loading" ? "Sending…" : "Send Me the Guide"}
              </button>
            </form>
          )}
        </SectionReveal>
      </Container>
    </section>
  );
}
