import SectionReveal from "@/components/SectionReveal";

const steps = [
  { n: "01", title: "Discover", detail: "Deep-dive consultation, lifestyle audit and vision mapping." },
  { n: "02", title: "Design", detail: "Concept development, 3D renderings and material selection." },
  { n: "03", title: "Source", detail: "Curated furniture, artisan partnerships and global procurement." },
  { n: "04", title: "Execute", detail: "Project management, quality control and timeline adherence." },
  { n: "05", title: "Reveal", detail: "Final styling, walkthrough and lifetime support." },
];

// The Interior Specifics Method™ — five-step signature process.
export default function ProcessTimeline() {
  return (
    <div className="grid gap-px overflow-hidden border border-gold/20 bg-gold/20 md:grid-cols-5">
      {steps.map((s, i) => (
        <SectionReveal
          key={s.n}
          delay={i * 0.08}
          className="flex h-full flex-col gap-3 bg-ivory p-7"
        >
          <span className="font-serif text-3xl text-gold">{s.n}</span>
          <h3 className="text-lg uppercase tracking-[0.12em] text-charcoal">
            {s.title}
          </h3>
          <p className="text-sm text-graysoft">{s.detail}</p>
        </SectionReveal>
      ))}
    </div>
  );
}
