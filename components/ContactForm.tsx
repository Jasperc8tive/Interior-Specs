"use client";

import { useState } from "react";
import { honeypotProps, submitLead, type FormStatus } from "@/lib/forms";
import { site, whatsappLink } from "@/lib/site";

// Qualifying contact form. Submits to /api/lead (Resend) with a honeypot guard.
// TODO(integrations): optionally add reCAPTCHA for extra spam protection.
const inputClass =
  "min-h-12 w-full border border-line bg-white px-4 py-3 text-sm text-charcoal transition-colors duration-200 placeholder:text-graysoft focus:border-gold focus:outline-none focus:ring-1 focus:ring-gold/40";

export default function ContactForm() {
  const [status, setStatus] = useState<FormStatus>("idle");

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = e.currentTarget;
    setStatus("loading");
    try {
      await submitLead("contact", form);
      setStatus("success");
      form.reset();
    } catch {
      setStatus("error");
    }
  }

  if (status === "success") {
    return (
      <div className="border-t-2 border-gold bg-ivory p-10 text-center">
        <h3 className="text-2xl text-charcoal">Thank you</h3>
        <p className="mt-3 text-muted">
          We&apos;ve received your inquiry and will respond within 24 hours.
        </p>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <input {...honeypotProps} />
      <div className="grid gap-4 sm:grid-cols-2">
        <input required name="name" aria-label="Full name" placeholder="Full name *" className={inputClass} />
        <input required name="email" type="email" aria-label="Email" placeholder="Email *" className={inputClass} />
        <input required name="phone" type="tel" aria-label="Phone" placeholder="Phone *" className={inputClass} />
        <input name="location" aria-label="Project location" placeholder="Project location" className={inputClass} />
        <select required name="propertyType" aria-label="Property type" defaultValue="" className={`${inputClass} text-charcoal`}>
          <option value="" disabled>Property type *</option>
          <option>Residential</option>
          <option>Commercial</option>
          <option>Hospitality</option>
          <option>Other</option>
        </select>
        <select required name="budget" aria-label="Estimated budget" defaultValue="" className={`${inputClass} text-charcoal`}>
          <option value="" disabled>Estimated budget *</option>
          <option>₦10M – ₦50M</option>
          <option>₦50M – ₦100M</option>
          <option>₦100M+</option>
        </select>
        <select required name="timeline" aria-label="Project timeline" defaultValue="" className={`${inputClass} text-charcoal`}>
          <option value="" disabled>Project timeline *</option>
          <option>ASAP</option>
          <option>1–3 months</option>
          <option>3–6 months</option>
          <option>6+ months</option>
        </select>
        <select name="source" aria-label="How did you hear about us?" defaultValue="" className={`${inputClass} text-charcoal`}>
          <option value="" disabled>How did you hear about us?</option>
          <option>Instagram</option>
          <option>Referral</option>
          <option>Google</option>
          <option>Other</option>
        </select>
      </div>
      <textarea
        required
        name="message"
        rows={5}
        aria-label="Project details"
        placeholder="Tell us about your project *"
        className={`${inputClass} resize-y`}
      />
      <label className="flex items-start gap-3 text-sm text-muted">
        <input required type="checkbox" name="consent" className="mt-1 h-4 w-4 accent-gold" />
        <span>I agree to the privacy policy and consent to being contacted.</span>
      </label>

      {status === "error" && (
        <p className="text-sm text-burgundy">
          Sorry, something went wrong. Please email{" "}
          <a href={`mailto:${site.email}`} className="underline">{site.email}</a> or{" "}
          <a href={whatsappLink()} target="_blank" rel="noopener noreferrer" className="underline">
            message us on WhatsApp
          </a>
          .
        </p>
      )}

      <button
        type="submit"
        disabled={status === "loading"}
        className="min-h-12 w-full cursor-pointer bg-terracotta px-7 py-3 text-sm font-semibold uppercase tracking-[0.15em] text-charcoal transition-colors duration-300 hover:bg-gold disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
      >
        {status === "loading" ? "Sending…" : "Send Inquiry"}
      </button>
    </form>
  );
}
