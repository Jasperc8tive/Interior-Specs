import Link from "next/link";
import type { ReactNode } from "react";

// Shared presentational primitives kept in one place for consistency.

export function Container({
  children,
  className = "",
}: {
  children: ReactNode;
  className?: string;
}) {
  return (
    <div className={`mx-auto w-full max-w-7xl px-6 md:px-10 ${className}`}>
      {children}
    </div>
  );
}

type ButtonVariant = "primary" | "secondary" | "outline" | "dark";

const variants: Record<ButtonVariant, string> = {
  primary:
    "bg-terracotta text-charcoal hover:bg-gold focus-visible:ring-gold",
  secondary:
    "bg-burgundy text-white hover:bg-burgundy/90 focus-visible:ring-burgundy",
  outline:
    "border border-gold text-gold hover:bg-gold hover:text-charcoal focus-visible:ring-gold",
  dark: "bg-charcoal text-ivory hover:bg-charcoal-light focus-visible:ring-charcoal",
};

export function Button({
  href,
  children,
  variant = "primary",
  className = "",
  type,
}: {
  href?: string;
  children: ReactNode;
  variant?: ButtonVariant;
  className?: string;
  type?: "button" | "submit";
}) {
  const base =
    "inline-flex items-center justify-center min-h-12 px-7 py-3 text-sm font-medium uppercase tracking-[0.15em] transition-colors duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2";
  const classes = `${base} ${variants[variant]} ${className}`;

  if (href) {
    const external = href.startsWith("http");
    if (external) {
      return (
        <a href={href} target="_blank" rel="noopener noreferrer" className={classes}>
          {children}
        </a>
      );
    }
    return (
      <Link href={href} className={classes}>
        {children}
      </Link>
    );
  }
  return (
    <button type={type ?? "button"} className={classes}>
      {children}
    </button>
  );
}

// Small eyebrow label + heading block used atop most sections.
export function SectionHeading({
  eyebrow,
  title,
  intro,
  align = "center",
  light = false,
}: {
  eyebrow?: string;
  title: string;
  intro?: string;
  align?: "center" | "left";
  light?: boolean;
}) {
  const alignment = align === "center" ? "text-center items-center" : "text-left items-start";
  return (
    <div className={`flex flex-col ${alignment} gap-4`}>
      {eyebrow && (
        <span
          className={`text-xs font-semibold uppercase tracking-[0.25em] ${
            light ? "text-gold-soft" : "text-gold"
          }`}
        >
          {eyebrow}
        </span>
      )}
      <h2
        className={`text-3xl md:text-4xl lg:text-[2.75rem] max-w-3xl ${
          light ? "text-ivory" : "text-charcoal"
        }`}
      >
        {title}
      </h2>
      <div className={`gold-rule ${align === "center" ? "mx-auto" : ""}`} />
      {intro && (
        <p
          className={`max-w-2xl text-base md:text-lg ${
            light ? "text-ivory/80" : "text-graysoft"
          }`}
        >
          {intro}
        </p>
      )}
    </div>
  );
}
