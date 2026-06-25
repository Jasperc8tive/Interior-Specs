"use client";

import { useState } from "react";
import PortfolioGrid from "@/components/PortfolioGrid";
import { projects, projectTypes, type ProjectType } from "@/lib/projects";

type Filter = "All" | ProjectType;
const filters: Filter[] = ["All", ...projectTypes];

// Filterable project archive. Initial filter can be deep-linked via ?type=.
export default function ShowroomFilter() {
  const [active, setActive] = useState<Filter>("All");

  // Apply the ?type= deep-link once, during render rather than in an effect.
  // The first render still returns "All" (matching SSR), so there's no
  // hydration mismatch; the param is applied on the immediate re-render.
  const [appliedParam, setAppliedParam] = useState(false);
  if (!appliedParam && typeof window !== "undefined") {
    setAppliedParam(true);
    const param = new URLSearchParams(window.location.search).get("type");
    if (param && (filters as string[]).includes(param)) {
      setActive(param as Filter);
    }
  }

  const shown =
    active === "All" ? projects : projects.filter((p) => p.type === active);

  return (
    <div>
      <div className="flex flex-wrap justify-center gap-3">
        {filters.map((f) => (
          <button
            key={f}
            type="button"
            onClick={() => setActive(f)}
            className={`min-h-11 cursor-pointer px-6 py-2 text-xs font-semibold uppercase tracking-[0.18em] transition-colors duration-300 ${
              active === f
                ? "bg-charcoal text-ivory"
                : "border border-line text-muted hover:border-gold hover:text-charcoal"
            }`}
          >
            {f}
          </button>
        ))}
      </div>

      <div className="mt-12">
        <PortfolioGrid items={shown} />
      </div>
    </div>
  );
}
