import { NextResponse } from "next/server";
import { Resend } from "resend";
import { site } from "@/lib/site";

export const runtime = "nodejs";

// Single endpoint for every site form (contact, booking, lead magnet, newsletter).
// Sends the submission to the studio inbox via Resend.
//
// Required env vars (see .env.example):
//   RESEND_API_KEY   — your Resend API key
//   LEAD_TO_EMAIL    — where leads are delivered (defaults to site.email)
//   LEAD_FROM_EMAIL  — verified sender (defaults to Resend's sandbox address)

const formLabels: Record<string, string> = {
  contact: "Contact Inquiry",
  booking: "Consultation Request",
  "lead-magnet": "Guide Download",
  newsletter: "Newsletter Signup",
};

function escapeHtml(value: string) {
  return value
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

export async function POST(req: Request) {
  let payload: { formType?: string; data?: Record<string, unknown>; company?: string };
  try {
    payload = await req.json();
  } catch {
    return NextResponse.json({ ok: false, error: "Invalid request." }, { status: 400 });
  }

  const { formType, data } = payload;

  // Honeypot: bots fill hidden "company" field; humans never see it.
  if (payload.company) {
    return NextResponse.json({ ok: true });
  }

  if (!formType || !formLabels[formType] || typeof data !== "object" || data === null) {
    return NextResponse.json({ ok: false, error: "Invalid form data." }, { status: 400 });
  }

  const apiKey = process.env.RESEND_API_KEY;
  if (!apiKey) {
    // Fail loudly so leads are never silently lost in production.
    return NextResponse.json(
      { ok: false, error: "Email service is not configured yet." },
      { status: 503 },
    );
  }

  const resend = new Resend(apiKey);
  const to = process.env.LEAD_TO_EMAIL || site.email;
  const from = process.env.LEAD_FROM_EMAIL || "Interior Specifics <onboarding@resend.dev>";
  const label = formLabels[formType];
  const fields = data as Record<string, unknown>;
  const name = typeof fields.name === "string" ? fields.name : "";
  const email = typeof fields.email === "string" ? fields.email : undefined;

  const rows = Object.entries(fields)
    .filter(([, v]) => v !== "" && v != null)
    .map(
      ([k, v]) =>
        `<tr><td style="padding:6px 12px;font-weight:600;text-transform:capitalize;color:#1A1A1A">${escapeHtml(
          k,
        )}</td><td style="padding:6px 12px;color:#444">${escapeHtml(String(v))}</td></tr>`,
    )
    .join("");

  try {
    const { error } = await resend.emails.send({
      from,
      to,
      replyTo: email,
      subject: `${label}${name ? ` — ${name}` : ""}`,
      html: `
        <div style="font-family:Arial,sans-serif">
          <h2 style="color:#1A1A1A">New ${label}</h2>
          <table style="border-collapse:collapse;border:1px solid #eee">${rows}</table>
          <p style="color:#999;font-size:12px;margin-top:16px">Sent from interiorspecifics.com</p>
        </div>`,
    });

    if (error) {
      return NextResponse.json({ ok: false, error: "Could not send." }, { status: 502 });
    }
    return NextResponse.json({ ok: true });
  } catch {
    return NextResponse.json({ ok: false, error: "Something went wrong." }, { status: 500 });
  }
}
