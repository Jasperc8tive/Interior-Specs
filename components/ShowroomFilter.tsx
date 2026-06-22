"use client";

import { useEffect, useState } from "react";
import PortfolioGrid from "@/components/PortfolioGrid";
import { projects, projectTypes, type ProjectType } from "@/lib/projects";

type Filter = "All" | ProjectType;
const filters: Filter[] = ["All", ...projectTypes];

// Filterable project archive. Initial filter can be deep-linked via ?type=.
export default function ShowroomFilter() {
  const [active, setActive] = useState<Filter>("All");

  // Read ?type= on mount so the Showroom dropdown can deep-link a category.
  useEffect(() => {
    const param = new URLSearchParams(window.location.search).get("type");
    if (param && (filters as string[]).includes(param)) {
      setActive(param as Filter);
    }
  }, []);

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
            className={`min-h-11 px-5 py-2 text-xs font-semibold uppercase tracking-[0.15em] transition-colors ${
              active === f
                ? "bg-charcoal text-ivory"
                : "border border-charcoal/15 text-graysoft hover:border-gold hover:text-charcoal"
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
