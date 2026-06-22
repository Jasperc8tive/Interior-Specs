import Link from "next/link";
import { nav, site, whatsappLink } from "@/lib/site";

export default function Footer() {
  return (
    <footer className="bg-charcoal text-ivory/80">
      <div className="mx-auto w-full max-w-7xl px-6 py-16 md:px-10">
        <div className="grid gap-12 md:grid-cols-2 lg:grid-cols-4">
          {/* Brand */}
          <div className="lg:col-span-1">
            <span className="font-serif text-2xl text-ivory">
              Interior <span className="text-gold">Specifics</span>
            </span>
            <p className="mt-4 max-w-xs font-accent text-lg italic text-ivory/70">
              {site.tagline}
            </p>
          </div>

          {/* Explore */}
          <div>
            <h4 className="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-gold">
              Explore
            </h4>
            <ul className="space-y-2.5 text-sm">
              {nav.map((item) => (
                <li key={item.label}>
                  <Link href={item.href} className="transition-colors hover:text-gold">
                    {item.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h4 className="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-gold">
              Contact
            </h4>
            <ul className="space-y-2.5 text-sm">
              <li>
                {site.address.street}, {site.address.city}, {site.address.country}
              </li>
              <li>
                <a href={`tel:${site.phoneHref}`} className="hover:text-gold">
                  {site.phoneDisplay}
                </a>
              </li>
              <li>
                <a href={`mailto:${site.email}`} className="hover:text-gold">
                  {site.email}
                </a>
              </li>
              <li>
                <a href={whatsappLink()} target="_blank" rel="noopener noreferrer" className="hover:text-gold">
                  WhatsApp Us
                </a>
              </li>
              <li className="text-ivory/60">{site.hours}</li>
            </ul>
          </div>

          {/* Newsletter */}
          <div>
            <h4 className="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-gold">
              Newsletter
            </h4>
            <p className="mb-4 text-sm text-ivory/70">
              Design insights for the discerning, straight to your inbox.
            </p>
            {/* TODO(integrations): connect to Mailchimp/ConvertKit. */}
            <form className="flex flex-col gap-3" aria-label="Newsletter signup">
              <input
                type="email"
                required
                placeholder="Your email"
                className="min-h-12 w-full border border-ivory/20 bg-transparent px-4 py-3 text-sm text-ivory placeholder:text-ivory/40 focus:border-gold focus:outline-none"
              />
              <button
                type="submit"
                className="min-h-12 bg-gold px-5 py-3 text-xs font-semibold uppercase tracking-[0.15em] text-charcoal transition-colors hover:bg-gold-soft"
              >
                Subscribe
              </button>
            </form>
            <div className="mt-6 flex gap-4">
              {Object.entries(site.socials).map(([name, url]) => (
                <a
                  key={name}
                  href={url}
                  target="_blank"
                  rel="noopener noreferrer"
                  aria-label={name}
                  className="text-xs uppercase tracking-[0.15em] text-ivory/60 transition-colors hover:text-gold"
                >
                  {name.slice(0, 2)}
                </a>
              ))}
            </div>
          </div>
        </div>

        <div className="mt-14 flex flex-col items-center justify-between gap-4 border-t border-ivory/10 pt-6 text-xs text-ivory/50 md:flex-row">
          <p>
            © {new Date().getFullYear()} {site.legalName}. All rights reserved.
          </p>
          <div className="flex gap-6">
            <Link href="/privacy" className="hover:text-gold">
              Privacy Policy
            </Link>
            <Link href="/terms" className="hover:text-gold">
              Terms
            </Link>
          </div>
        </div>
      </div>
    </footer>
  );
}
