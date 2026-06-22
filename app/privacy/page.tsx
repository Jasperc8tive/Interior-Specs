import type { Metadata } from "next";
import PageHero from "@/components/PageHero";
import { Container } from "@/components/ui";

export const metadata: Metadata = {
  title: "Privacy Policy",
  description: "How Interior Specifics collects, uses and protects your information.",
};

// Placeholder legal copy — replace with reviewed policy before launch.
export default function PrivacyPage() {
  return (
    <>
      <PageHero
        title="Privacy Policy"
        image="https://images.unsplash.com/photo-1600210492493-0946911123ea?auto=format&fit=crop&w=2000&q=80"
        imageAlt="Interior detail"
      />
      <section className="bg-white py-20 md:py-28">
        <Container>
          <div className="mx-auto max-w-3xl space-y-6 text-graysoft">
            <p>
              This is placeholder privacy content. Interior Specifics respects your
              privacy and is committed to protecting the personal information you share
              with us. We collect information you provide through our contact and
              newsletter forms solely to respond to your inquiries and, with your
              consent, to send relevant updates.
            </p>
            <p>
              We do not sell your data. You may request access to, correction of, or
              deletion of your information at any time by contacting us. A complete,
              legally reviewed policy will be published before launch.
            </p>
          </div>
        </Container>
      </section>
    </>
  );
}
