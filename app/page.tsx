import { Container } from "@/components/ui";
import ScrollProgress from "@/components/ScrollProgress";
import SectionReveal from "@/components/SectionReveal";
import TestimonialCarousel from "@/components/TestimonialCarousel";
import StoryHero from "@/components/home/StoryHero";
import CredentialMarquee from "@/components/home/CredentialMarquee";
import Ethos from "@/components/home/Ethos";
import MethodJourney from "@/components/home/MethodJourney";
import WhyLedger from "@/components/home/WhyLedger";
import FeaturedWork from "@/components/home/FeaturedWork";
import StatsBand from "@/components/home/StatsBand";
import FounderNote from "@/components/home/FounderNote";
import ServicesPreview from "@/components/home/ServicesPreview";
import JournalPreview from "@/components/home/JournalPreview";
import ClosingCTA from "@/components/home/ClosingCTA";
import AnimatedHeading from "@/components/home/AnimatedHeading";

const credentials = [
  "Residential",
  "Commercial",
  "Hospitality",
  "Custom Furniture",
  "Lighting Design",
  "Project Management",
  "Global Sourcing",
  "Turnkey Delivery",
];

export default function Home() {
  return (
    <>
      <ScrollProgress />

      <StoryHero />

      {/* Credential bridge between the hero and the story */}
      <div className="grain relative bg-onyx py-6">
        <CredentialMarquee items={credentials} />
      </div>

      <Ethos />

      {/* The Interior Specifics Method™ — scrollytelling centerpiece */}
      <MethodJourney />

      <WhyLedger />

      <FeaturedWork />

      <StatsBand />

      <FounderNote />

      {/* Testimonials */}
      <section className="grain relative bg-charcoal py-24 md:py-32">
        <Container>
          <div className="mx-auto max-w-2xl text-center">
            <span className="eyebrow eyebrow--center justify-center text-gold-soft">
              In Their Words
            </span>
            <AnimatedHeading
              text="What our clients say"
              className="mt-6 text-3xl text-ivory sm:text-4xl lg:text-[2.75rem]"
            />
          </div>
          <SectionReveal className="mt-14">
            <TestimonialCarousel />
          </SectionReveal>
        </Container>
      </section>

      <ServicesPreview />

      <JournalPreview />

      <ClosingCTA />
    </>
  );
}
