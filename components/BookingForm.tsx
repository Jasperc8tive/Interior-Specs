"use client";

import { useState } from "react";
import { honeypotProps, submitLead, type FormStatus } from "@/lib/forms";
import { site, whatsappLink } from "@/lib/site";

// Consultation request form. Submits to /api/lead (Resend).
const inputClass =
  "min-h-12 w-full border border-charcoal/15 bg-white px-4 py-3 text-sm focus:border-gold focus:outline-none";

export default function BookingForm() {
  const [status, setStatus] = useState<FormStatus>("idle");

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = e.currentTarget;
    setStatus("loading");
    try {
      await submitLead("booking", form);
      setStatus("success");
      form.reset();
    } catch {
      setStatus("error");
    }
  }

  if (status === "success") {
    return (
      <div className="border-t-2 border-gold bg-ivory p-10 text-center">
        <h3 className="text-2xl text-charcoal">Request received</h3>
        <p className="mt-3 text-graysoft">
          Thank you — we&apos;ll confirm your consultation by email or phone within
          24 hours.
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
        <select required name="consultationType" aria-label="Consultation type" defaultValue="" className={`${inputClass} text-charcoal`}>
          <option value="" disabled>Consultation type *</option>
          <option>In-person (Lagos showroom)</option>
          <option>Virtual (video call)</option>
          <option>Phone call</option>
        </select>
        <input
          required
          name="preferredDate"
          type="date"
          aria-label="Preferred date"
          className={`${inputClass} text-charcoal`}
        />
        <select required name="preferredTime" aria-label="Preferred time" defaultValue="" className={`${inputClass} text-charcoal`}>
          <option value="" disabled>Preferred time *</option>
          <option>Morning (9am–12pm)</option>
          <option>Afternoon (12pm–4pm)</option>
          <option>Evening (4pm–6pm)</option>
        </select>
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
      </div>
      <textarea
        name="message"
        rows={4}
        aria-label="Project details"
        placeholder="Tell us a little about your project (optional)"
        className={`${inputClass} resize-y`}
      />
      <label className="flex items-start gap-3 text-sm text-graysoft">
        <input required type="checkbox" name="consent" className="mt-1 h-4 w-4 accent-gold" />
        <span>I agree to the privacy policy and consent to being contacted.</span>
      </label>

      {status === "error" && (
        <p className="text-sm text-burgundy">
          Sorry, something went wrong. Please call{" "}
          <a href={`tel:${site.phoneHref}`} className="underline">{site.phoneDisplay}</a> or{" "}
          <a href={whatsappLink()} target="_blank" rel="noopener noreferrer" className="underline">
            message us on WhatsApp
          </a>
          .
        </p>
      )}

      <button
        type="submit"
        disabled={status === "loading"}
        className="min-h-12 w-full bg-terracotta px-7 py-3 text-sm font-semibold uppercase tracking-[0.15em] text-charcoal transition-colors hover:bg-gold disabled:opacity-60 sm:w-auto"
      >
        {status === "loading" ? "Sending…" : "Request My Consultation"}
      </button>
    </form>
  );
}
