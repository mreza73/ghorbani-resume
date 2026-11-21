/*
 * assets/js/main.js
 * ─────────────────────────────────────────────────────────
 * All core interactions (typing animation, scroll reveal,
 * skill bars, counters, navbar) are bootstrapped inline in
 * index.php so the page works without this file.
 *
 * Add extra functionality here; it loads after index.php's
 * inline <script> block.
 * ─────────────────────────────────────────────────────────
 */

/* Active nav-link highlight on scroll */
(function () {
  const sections = document.querySelectorAll('section[id], div[id="contact"]');
  const links = document.querySelectorAll(".nav-links a");
  if (!sections.length || !links.length) return;

  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          links.forEach((a) => a.classList.remove("active"));
          const active = document.querySelector(
            `.nav-links a[href="#${entry.target.id}"]`,
          );
          if (active) active.classList.add("active");
        }
      });
    },
    { threshold: 0.35 },
  );

  sections.forEach((s) => io.observe(s));

  /* Add active-link style dynamically */
  const style = document.createElement("style");
  style.textContent =
    ".nav-links a.active { color: var(--accent) !important; }";
  document.head.appendChild(style);
})();
