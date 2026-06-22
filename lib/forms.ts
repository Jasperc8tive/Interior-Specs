// Shared client helper + form status type for all site forms.

export type FormType = "contact" | "booking" | "lead-magnet" | "newsletter";
export type FormStatus = "idle" | "loading" | "success" | "error";

// POSTs a form submission to /api/lead. Throws on failure so callers can
// surface an error state with a fallback (call / WhatsApp).
export async function submitLead(
  formType: FormType,
  form: HTMLFormElement,
): Promise<void> {
  const entries = Object.fromEntries(new FormData(form).entries());
  // The honeypot field travels at the top level, not inside `data`.
  const { company, ...data } = entries as Record<string, string>;

  const res = await fetch("/api/lead", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ formType, data, company }),
  });

  if (!res.ok) {
    const body = await res.json().catch(() => null);
    throw new Error(body?.error ?? "Request failed");
  }
}

// Hidden anti-spam field shared by every form.
export const honeypotProps = {
  type: "text" as const,
  name: "company",
  tabIndex: -1,
  autoComplete: "off",
  "aria-hidden": true,
  className: "hidden",
};
