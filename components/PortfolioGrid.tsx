import Image from "next/image";
import Link from "next/link";
import type { Project } from "@/lib/projects";

// Grid of project cards sharing the homepage FeaturedWork hover choreography:
// a slow image push, an animated gold underline, and a budget reveal on hover
// to help pre-qualify enquiries. Pure CSS hover, so it stays a server component.
export default function PortfolioGrid({ items }: { items: Project[] }) {
  return (
    <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
      {items.map((p) => (
        <Link
          key={p.slug}
          href={`/showroom/${p.slug}`}
          className="group relative block aspect-[4/5] overflow-hidden bg-charcoal"
        >
          <Image
            src={p.image}
            alt={`${p.title} — ${p.type} interior design in ${p.location}`}
            fill
            sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
            className="object-cover transition-transform duration-[1.1s] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.06]"
          />
          <div className="absolute inset-0 bg-gradient-to-t from-onyx/90 via-onyx/25 to-transparent" />

          <div className="absolute inset-x-0 bottom-0 p-6">
            <span className="block text-xs font-semibold uppercase tracking-[0.2em] text-gold">
              {p.type} · {p.location}
            </span>
            <h3 className="mt-2 text-xl text-ivory">
              <span className="inline-block">
                {p.title}
                <span className="mt-1 block h-px w-0 bg-gold transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:w-full" />
              </span>
            </h3>
            <div className="grid grid-rows-[0fr] opacity-0 transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:grid-rows-[1fr] group-hover:opacity-100">
              <div className="overflow-hidden">
                <p className="pt-3 text-sm text-ivory/80">{p.blurb}</p>
                <p className="mt-2 text-sm font-semibold text-gold-soft">
                  Investment: {p.budget}
                </p>
                <span className="mt-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.15em] text-gold">
                  View Case Study
                  <svg viewBox="0 0 24 24" className="h-3.5 w-3.5 fill-none stroke-current stroke-2" aria-hidden>
                    <path d="M5 12h14M13 6l6 6-6 6" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                </span>
              </div>
            </div>
          </div>
        </Link>
      ))}
    </div>
  );
}
