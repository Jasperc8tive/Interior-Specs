import type { Metadata } from "next";
import PageHero from "@/components/PageHero";
import { Container } from "@/components/ui";

export const metadata: Metadata = {
  title: "Terms of Use",
  description: "The terms governing use of the Interior Specifics website.",
};

// Placeholder legal copy — replace with reviewed terms before launch.
export default function TermsPage() {
  return (
    <>
      <PageHero
        title="Terms of Use"
        image="https://images.unsplash.com/photo-1615875605825-5eb9bb5d52ac?auto=format&fit=crop&w=2000&q=80"
        imageAlt="Interior detail"
      />
      <section className="bg-white py-20 md:py-28">
        <Container>
          <div className="mx-auto max-w-3xl space-y-6 text-graysoft">
            <p>
              This is placeholder terms content. By using the Interior Specifics
              website you agree to use it lawfully and not to misuse the content or
              materials provided. All imagery, copy and designs remain the property of
              Interior Specifics unless otherwise stated.
            </p>
            <p>
              Information on this site is provided for general guidance and does not
              constitute a binding quote. A complete, legally reviewed set of terms
              will be published before launch.
            </p>
          </div>
        </Container>
      </section>
    </>
  );
}
