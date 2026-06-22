"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { nav, site } from "@/lib/site";
import { services } from "@/lib/services";

// Build the Services dropdown from the services catalogue so it never drifts.
const servicesChildren = services.map((s) => ({
  label: s.title,
  href: `/services/${s.slug}`,
}));

export default function Header() {
  const pathname = usePathname();
  const [scrolled, setScrolled] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [openSub, setOpenSub] = useState<string | null>(null);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 24);
    onScroll();
    window.addEventListener("scroll", onScroll);
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  // Close the mobile menu whenever the route changes.
  useEffect(() => {
    setMobileOpen(false);
    setOpenSub(null);
  }, [pathname]);

  const childrenFor = (label: string, base?: { label: string; href: string }[]) =>
    label === "Services" ? servicesChildren : base;

  return (
    <header
      className={`sticky top-0 z-40 transition-colors duration-300 ${
        scrolled || mobileOpen
          ? "bg-charcoal/95 backdrop-blur supports-[backdrop-filter]:bg-charcoal/80"
          : "bg-transparent"
      }`}
    >
      <div className="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-4 md:px-10">
        <Link href="/" className="flex flex-col leading-none">
          <span className="font-serif text-xl tracking-wide text-ivory md:text-2xl">
            Interior <span className="text-gold">Specifics</span>
          </span>
          <span className="mt-1 text-[10px] uppercase tracking-[0.3em] text-ivory/60">
            Lagos · Nigeria
          </span>
        </Link>

        {/* Desktop nav */}
        <nav className="hidden items-center gap-8 lg:flex">
          {nav.map((item) => {
            const kids = childrenFor(item.label, item.children);
            return (
              <div key={item.label} className="group relative">
                <Link
                  href={item.href}
                  className="flex items-center gap-1 py-2 text-sm font-medium uppercase tracking-[0.12em] text-ivory/90 transition-colors hover:text-gold"
                >
                  {item.label}
                  {kids && <Chevron />}
                </Link>
                {kids && (
                  <div className="invisible absolute left-1/2 top-full z-50 w-64 -translate-x-1/2 pt-2 opacity-0 transition-all duration-200 group-hover:visible group-hover:opacity-100">
                    <div className="max-h-[70vh] overflow-auto border-t-2 border-gold bg-charcoal py-2 shadow-2xl">
                      {kids.map((c) => (
                        <Link
                          key={c.href}
                          href={c.href}
                          className="block px-5 py-2.5 text-sm text-ivory/80 transition-colors hover:bg-charcoal-light hover:text-gold"
                        >
                          {c.label}
                        </Link>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            );
          })}
          <a
            href={site.calendlyUrl}
            className="ml-2 inline-flex min-h-11 items-center bg-terracotta px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.15em] text-charcoal transition-colors hover:bg-gold"
          >
            Book Consultation
          </a>
        </nav>

        {/* Mobile toggle */}
        <button
          type="button"
          aria-label="Toggle menu"
          aria-expanded={mobileOpen}
          onClick={() => setMobileOpen((v) => !v)}
          className="flex h-11 w-11 items-center justify-center text-ivory lg:hidden"
        >
          <div className="space-y-1.5">
            <span
              className={`block h-0.5 w-6 bg-current transition-transform ${
                mobileOpen ? "translate-y-2 rotate-45" : ""
              }`}
            />
            <span
              className={`block h-0.5 w-6 bg-current transition-opacity ${
                mobileOpen ? "opacity-0" : ""
              }`}
            />
            <span
              className={`block h-0.5 w-6 bg-current transition-transform ${
                mobileOpen ? "-translate-y-2 -rotate-45" : ""
              }`}
            />
          </div>
        </button>
      </div>

      {/* Mobile menu */}
      {mobileOpen && (
        <div className="max-h-[calc(100vh-72px)] overflow-auto border-t border-ivory/10 bg-charcoal px-6 pb-8 pt-2 lg:hidden">
          {nav.map((item) => {
            const kids = childrenFor(item.label, item.children);
            const isOpen = openSub === item.label;
            return (
              <div key={item.label} className="border-b border-ivory/10">
                <div className="flex items-center justify-between">
                  <Link
                    href={item.href}
                    className="block py-4 text-sm font-medium uppercase tracking-[0.12em] text-ivory"
                  >
                    {item.label}
                  </Link>
                  {kids && (
                    <button
                      type="button"
                      aria-label={`Expand ${item.label}`}
                      onClick={() => setOpenSub(isOpen ? null : item.label)}
                      className="flex h-11 w-11 items-center justify-center text-gold"
                    >
                      <Chevron rotated={isOpen} />
                    </button>
                  )}
                </div>
                {kids && isOpen && (
                  <div className="pb-2">
                    {kids.map((c) => (
                      <Link
                        key={c.href}
                        href={c.href}
                        className="block py-2.5 pl-4 text-sm text-ivory/70"
                      >
                        {c.label}
                      </Link>
                    ))}
                  </div>
                )}
              </div>
            );
          })}
          <a
            href={site.calendlyUrl}
            className="mt-6 inline-flex w-full min-h-12 items-center justify-center bg-terracotta px-5 py-3 text-xs font-semibold uppercase tracking-[0.15em] text-charcoal"
          >
            Book Your Private Consultation
          </a>
        </div>
      )}
    </header>
  );
}

function Chevron({ rotated }: { rotated?: boolean }) {
  return (
    <svg
      viewBox="0 0 24 24"
      className={`h-4 w-4 fill-none stroke-current stroke-2 transition-transform ${
        rotated ? "rotate-180" : ""
      }`}
      aria-hidden="true"
    >
      <path d="M6 9l6 6 6-6" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
}
