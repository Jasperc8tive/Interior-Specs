import { Button, Container, SectionHeading } from "@/components/ui";
import SectionReveal from "@/components/SectionReveal";
import { site, whatsappLink } from "@/lib/site";

// Reusable closing call-to-action band used at the foot of most pages.
export default function CTASection({
  eyebrow = "Begin",
  title = "Ready to Transform Your Space?",
  intro = "Every project is unique. Let's discuss yours over a private consultation.",
  primaryLabel = "Book Your Private Consultation",
}: {
  eyebrow?: string;
  title?: string;
  intro?: string;
  primaryLabel?: string;
}) {
  return (
    <section className="bg-charcoal py-20 md:py-28">
      <Container>
        <SectionReveal className="flex flex-col items-center gap-8 text-center">
          <SectionHeading eyebrow={eyebrow} title={title} intro={intro} light />
          <div className="flex flex-col gap-4 sm:flex-row">
            <Button href={site.bookingUrl} variant="primary">
              {primaryLabel}
            </Button>
            <Button href={whatsappLink()} variant="outline">
              Chat on WhatsApp
            </Button>
          </div>
        </SectionReveal>
      </Container>
    </section>
  );
}
