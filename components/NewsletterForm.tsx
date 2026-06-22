"use client";

import { useState } from "react";
import { honeypotProps, submitLead, type FormStatus } from "@/lib/forms";

// Footer newsletter signup. Submits to /api/lead (Resend).
export default function NewsletterForm() {
  const [status, setStatus] = useState<FormStatus>("idle");

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = e.currentTarget;
    setStatus("loading");
    try {
      await submitLead("newsletter", form);
      setStatus("success");
      form.reset();
    } catch {
      setStatus("error");
    }
  }

  if (status === "success") {
    return (
      <p className="text-sm text-gold">Thank you — you&apos;re subscribed.</p>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="flex flex-col gap-3" aria-label="Newsletter signup">
      <input {...honeypotProps} />
      <input
        type="email"
        name="email"
        required
        aria-label="Your email"
        placeholder="Your email"
        className="min-h-12 w-full border border-ivory/20 bg-transparent px-4 py-3 text-sm text-ivory placeholder:text-ivory/40 focus:border-gold focus:outline-none"
      />
      <button
        type="submit"
        disabled={status === "loading"}
        className="min-h-12 bg-gold px-5 py-3 text-xs font-semibold uppercase tracking-[0.15em] text-charcoal transition-colors hover:bg-gold-soft disabled:opacity-60"
      >
        {status === "loading" ? "Subscribing…" : "Subscribe"}
      </button>
      {status === "error" && (
        <p className="text-xs text-gold-soft">Couldn&apos;t subscribe. Please try again later.</p>
      )}
    </form>
  );
}
