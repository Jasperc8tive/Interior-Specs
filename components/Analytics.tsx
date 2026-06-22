import Script from "next/script";
import { site } from "@/lib/site";

// Google Analytics 4. Renders nothing until site.gaMeasurementId is set,
// so the site is production-ready the moment a real ID is added.
// TODO(integrations): set gaMeasurementId in lib/site.ts (e.g. "G-XXXXXXX").
export default function Analytics() {
  const id = site.gaMeasurementId;
  if (!id) return null;

  return (
    <>
      <Script
        src={`https://www.googletagmanager.com/gtag/js?id=${id}`}
        strategy="afterInteractive"
      />
      <Script id="ga4-init" strategy="afterInteractive">
        {`
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', '${id}');
        `}
      </Script>
    </>
  );
}
