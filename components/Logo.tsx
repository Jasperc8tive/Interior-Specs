// Interior Specifics roundel — a faithful SVG recreation of the brand mark:
// gold circle, curved "INTERIOR SPECIFICS" wordmark, a tick ring, and the
// serif I | S monogram. Scales crisply at any size.

const serif = { fontFamily: "var(--font-playfair), Georgia, serif" } as const;

// Tick ring: short radial marks around the lower ~300°, leaving a gap at the
// top for the curved wordmark.
const ticks = Array.from({ length: 60 }, (_, i) => i * 6).filter((angle) => {
  const fromTop = Math.min(angle, 360 - angle);
  return fromTop > 52;
});

export default function Logo({
  className = "h-10 w-10",
  title = "Interior Specifics",
}: {
  className?: string;
  title?: string;
}) {
  return (
    <svg viewBox="0 0 100 100" className={className} role="img" aria-label={title}>
      <circle cx="50" cy="50" r="50" fill="#C9A961" />

      {/* Tick ring */}
      <g stroke="#FFFFFF" strokeWidth="1.1" strokeLinecap="round" opacity="0.9">
        {ticks.map((angle) => (
          <line
            key={angle}
            x1="50"
            y1="6"
            x2="50"
            y2="10.5"
            transform={`rotate(${angle} 50 50)`}
          />
        ))}
      </g>

      {/* Curved wordmark along the top */}
      <defs>
        <path id="is-top-arc" d="M 14 50 A 36 36 0 0 1 86 50" fill="none" />
      </defs>
      <text
        fill="#FFFFFF"
        fontSize="7.2"
        letterSpacing="2.4"
        textAnchor="middle"
        style={serif}
      >
        <textPath href="#is-top-arc" startOffset="50%">
          INTERIOR · SPECIFICS
        </textPath>
      </text>

      {/* I | S monogram */}
      <text x="36" y="64" textAnchor="middle" fontSize="40" fill="#FFFFFF" style={serif}>
        I
      </text>
      <line x1="50" y1="40" x2="50" y2="64" stroke="#FFFFFF" strokeWidth="1.6" />
      <text x="63" y="64" textAnchor="middle" fontSize="40" fill="#FFFFFF" style={serif}>
        S
      </text>
    </svg>
  );
}
