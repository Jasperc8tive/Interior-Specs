import { Button, Container } from "@/components/ui";

export default function NotFound() {
  return (
    <section className="flex min-h-[70vh] items-center bg-charcoal">
      <Container className="text-center">
        <span className="font-serif text-6xl text-gold">404</span>
        <h1 className="mt-6 text-3xl text-ivory">This page could not be found</h1>
        <p className="mx-auto mt-4 max-w-md text-ivory/70">
          The page you&apos;re looking for may have moved. Let&apos;s get you back to
          something beautiful.
        </p>
        <div className="mt-8 flex justify-center gap-4">
          <Button href="/" variant="primary">
            Return Home
          </Button>
          <Button href="/showroom" variant="outline">
            View Our Work
          </Button>
        </div>
      </Container>
    </section>
  );
}
