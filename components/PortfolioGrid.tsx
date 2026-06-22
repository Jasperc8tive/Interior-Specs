import Image from "next/image";
import Link from "next/link";
import type { Project } from "@/lib/projects";

// Grid of project cards. Hover reveals the budget range to help qualify leads.
export default function PortfolioGrid({ items }: { items: Project[] }) {
  return (
    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
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
            className="object-cover transition-transform duration-700 group-hover:scale-105"
          />
          <div className="absolute inset-0 bg-gradient-to-t from-charcoal/90 via-charcoal/20 to-transparent" />
          <div className="absolute inset-x-0 bottom-0 p-6">
            <span className="text-xs font-semibold uppercase tracking-[0.2em] text-gold">
              {p.type} · {p.location}
            </span>
            <h3 className="mt-2 text-xl text-ivory">{p.title}</h3>
            <div className="mt-3 max-h-0 overflow-hidden opacity-0 transition-all duration-500 group-hover:max-h-24 group-hover:opacity-100">
              <p className="text-sm text-ivory/80">{p.blurb}</p>
              <p className="mt-2 text-sm font-semibold text-gold-soft">
                Investment: {p.budget}
              </p>
              <p className="mt-2 text-xs font-semibold uppercase tracking-[0.15em] text-gold">
                View Case Study →
              </p>
            </div>
          </div>
        </Link>
      ))}
    </div>
  );
}
