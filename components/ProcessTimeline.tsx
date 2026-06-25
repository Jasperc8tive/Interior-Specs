import SectionReveal from "@/components/SectionReveal";
import { methodActs } from "@/lib/method";

// The Interior Specifics Method™ — five-step signature process.
export default function ProcessTimeline() {
  return (
    <div className="grid gap-px overflow-hidden border border-gold/20 bg-gold/20 md:grid-cols-5">
      {methodActs.map((s, i) => (
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
