"use client";

import { useState } from "react";

// Qualifying contact form. Front-end validation only — no backend yet.
// TODO(integrations): POST to Formspree/Resend/your CRM and add reCAPTCHA.
const inputClass =
  "min-h-12 w-full border border-charcoal/15 bg-white px-4 py-3 text-sm focus:border-gold focus:outline-none";

export default function ContactForm() {
  const [submitted, setSubmitted] = useState(false);

  if (submitted) {
    return (
      <div className="border-t-2 border-gold bg-ivory p-10 text-center">
        <h3 className="text-2xl text-charcoal">Thank you</h3>
        <p className="mt-3 text-graysoft">
          We&apos;ve received your inquiry and will respond within 24 hours.
        </p>
      </div>
    );
  }

  return (
    <form
      onSubmit={(e) => {
        e.preventDefault();
        setSubmitted(true);
      }}
      className="space-y-4"
    >
      <div className="grid gap-4 sm:grid-cols-2">
        <input required placeholder="Full name *" className={inputClass} />
        <input required type="email" placeholder="Email *" className={inputClass} />
        <input required type="tel" placeholder="Phone *" className={inputClass} />
        <input placeholder="Project location" className={inputClass} />
        <select required defaultValue="" className={`${inputClass} text-charcoal`}>
          <option value="" disabled>Property type *</option>
          <option>Residential</option>
          <option>Commercial</option>
          <option>Hospitality</option>
          <option>Other</option>
        </select>
        <select required defaultValue="" className={`${inputClass} text-charcoal`}>
          <option value="" disabled>Estimated budget *</option>
          <option>₦10M – ₦50M</option>
          <option>₦50M – ₦100M</option>
          <option>₦100M+</option>
        </select>
        <select required defaultValue="" className={`${inputClass} text-charcoal`}>
          <option value="" disabled>Project timeline *</option>
          <option>ASAP</option>
          <option>1–3 months</option>
          <option>3–6 months</option>
          <option>6+ months</option>
        </select>
        <select defaultValue="" className={`${inputClass} text-charcoal`}>
          <option value="" disabled>How did you hear about us?</option>
          <option>Instagram</option>
          <option>Referral</option>
          <option>Google</option>
          <option>Other</option>
        </select>
      </div>
      <textarea
        required
        rows={5}
        placeholder="Tell us about your project *"
        className={`${inputClass} resize-y`}
      />
      <label className="flex items-start gap-3 text-sm text-graysoft">
        <input required type="checkbox" className="mt-1 h-4 w-4 accent-gold" />
        <span>I agree to the privacy policy and consent to being contacted.</span>
      </label>
      <button
        type="submit"
        className="min-h-12 w-full bg-terracotta px-7 py-3 text-sm font-semibold uppercase tracking-[0.15em] text-charcoal transition-colors hover:bg-gold sm:w-auto"
      >
        Send Inquiry
      </button>
    </form>
  );
}
