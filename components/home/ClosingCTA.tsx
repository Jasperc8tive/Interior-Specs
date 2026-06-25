"use client";

import { useState } from "react";
import Image from "next/image";
import { motion } from "framer-motion";
import { Container } from "@/components/ui";
import AnimatedHeading from "@/components/home/AnimatedHeading";
import { honeypotProps, submitLead, type FormStatus } from "@/lib/forms";
import { site, whatsappLink } from "@/lib/site";
import { transition, viewportOnce } from "@/lib/anim";

const inputClass =
  "min-h-12 w-full border border-line-light bg-white/[0.03] px-4 py-3 text-sm text-ivory placeholder:text-ivory/40 transition-colors focus:border-gold focus:outline-none";

// The final beat: a dramatic dark canvas with a single, focused lead capture.
// Submitting requests the consultation and the complimentary design guide.
export default function ClosingCTA() {
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
    <section className="grain relative overflow-hidden bg-onyx py-24 md:py-32">
      {/* Faint architectural backdrop */}
      <div className="absolute inset-0 opacity-[0.12]">
        <Image
          src="https://images.unsplash.com/photo-1600210492493-0946911123ea?auto=format&fit=crop&w=2000&q=80"
          alt=""
          aria-hidden
          fill
          sizes="100vw"
          className="object-cover"
        />
      </div>
      <div className="absolute inset-0 bg-[radial-gradient(80%_60%_at_50%_0%,rgba(201,169,97,0.12),transparent_70%)]" />

      <Container className="relative">
        <div className="grid gap-14 lg:grid-cols-2 lg:gap-20">
          {/* Invitation */}
          <div className="lg:pt-6">
            <span className="eyebrow text-gold-soft">Begin Your Project</span>
            <AnimatedHeading
              text="Let's design something worthy of you"
              accent={{ from: 3, to: 5 }}
              className="mt-6 text-4xl text-ivory sm:text-5xl lg:text-[3.25rem]"
            />
            <motion.p
              initial={{ opacity: 0, y: 18 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={viewportOnce}
              transition={{ ...transition, delay: 0.12 }}
              className="mt-7 max-w-md text-lg text-ivory/80"
            >
              Share a few details and we&apos;ll be in touch within 24 hours — with
              a complimentary copy of <span className="accent text-gold-soft">The
              7 Secrets of Luxury Interior Design in Nigeria</span> and an invitation
              to a private consultation.
            </motion.p>

            <motion.div
              initial={{ opacity: 0, y: 18 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={viewportOnce}
              transition={{ ...transition, delay: 0.2 }}
              className="mt-9 flex flex-wrap items-center gap-x-8 gap-y-3 border-t border-line-light pt-7 text-sm text-ivory/70"
            >
              <a
                href={whatsappLink()}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 transition-colors hover:text-gold"
              >
                Message on WhatsApp
              </a>
              <span aria-hidden className="h-3 w-px bg-line-light" />
              <a
                href={`tel:${site.phoneHref}`}
                className="inline-flex items-center gap-2 transition-colors hover:text-gold"
              >
                {site.phoneDisplay}
              </a>
            </motion.div>
          </div>

          {/* Form */}
          <motion.div
            initial={{ opacity: 0, y: 26 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={viewportOnce}
            transition={{ ...transition, delay: 0.15 }}
            className="border border-line-light bg-white/[0.02] p-7 backdrop-blur-sm md:p-9"
          >
            {status === "success" ? (
              <div className="flex min-h-[320px] flex-col items-center justify-center text-center">
                <span className="gold-text font-serif text-5xl">Thank you</span>
                <p className="mt-4 max-w-sm text-ivory/80">
                  Check your inbox shortly for the guide — we&apos;ll personally
                  follow up within 24 hours to arrange your consultation.
                </p>
              </div>
            ) : (
              <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                <input {...honeypotProps} />
                <div>
                  <label htmlFor="cta-name" className="sr-only">Full name</label>
                  <input id="cta-name" required name="name" placeholder="Full name" className={inputClass} />
                </div>
                <div className="grid gap-4 sm:grid-cols-2">
                  <div>
                    <label htmlFor="cta-email" className="sr-only">Email address</label>
                    <input id="cta-email" required type="email" name="email" placeholder="Email address" className={inputClass} />
                  </div>
                  <div>
                    <label htmlFor="cta-phone" className="sr-only">Phone number</label>
                    <input id="cta-phone" required type="tel" name="phone" placeholder="Phone number" className={inputClass} />
                  </div>
                </div>
                <div>
                  <label htmlFor="cta-type" className="sr-only">Property type</label>
                  <select id="cta-type" required name="propertyType" defaultValue="" className={`${inputClass} cursor-pointer`}>
                    <option value="" disabled className="text-charcoal">Property type</option>
                    <option className="text-charcoal">Residential</option>
                    <option className="text-charcoal">Commercial</option>
                    <option className="text-charcoal">Hospitality</option>
                    <option className="text-charcoal">Other</option>
                  </select>
                </div>

                {status === "error" && (
                  <p className="text-sm text-terracotta">
                    Something went wrong. Please{" "}
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
                  className="mt-2 inline-flex min-h-12 cursor-pointer items-center justify-center bg-terracotta px-7 py-3 text-sm font-semibold uppercase tracking-[0.15em] text-charcoal transition-colors duration-300 hover:bg-gold disabled:cursor-not-allowed disabled:opacity-60"
                >
                  {status === "loading" ? "Sending…" : "Send the Guide & Book Consultation"}
                </button>
                <p className="text-center text-xs text-ivory/45">
                  No obligation. Your details are kept strictly private.
                </p>
              </form>
            )}
          </motion.div>
        </div>
      </Container>
    </section>
  );
}
