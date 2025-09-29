(function () {
  const doc = document.documentElement;
  const THEME_KEY = 'origin-theme';
  const toggles = document.querySelectorAll('[data-theme-toggle]');

  doc.classList.add('js-enabled');

  function applyTheme(theme, persist = true) {
    const safeTheme = theme === 'dark' ? 'dark' : 'light';
    doc.classList.toggle('theme-dark', safeTheme === 'dark');
    doc.classList.toggle('theme-light', safeTheme === 'light');
    doc.setAttribute('data-theme', safeTheme);

    toggles.forEach((toggle) => {
      toggle.setAttribute('aria-pressed', safeTheme === 'dark' ? 'true' : 'false');
      toggle.dataset.theme = safeTheme;
    });

    if (persist) {
      try {
        window.localStorage.setItem(THEME_KEY, safeTheme);
      } catch (error) {
        /* Ignore storage errors (Safari private mode, etc.) */
      }
    }
  }

  function getStoredTheme() {
    try {
      return window.localStorage.getItem(THEME_KEY);
    } catch (error) {
      return null;
    }
  }

  const storedTheme = getStoredTheme();
  const initialTheme = storedTheme || doc.getAttribute('data-theme') || 'light';
  applyTheme(initialTheme, Boolean(storedTheme));

  if (toggles.length) {
    toggles.forEach((toggle) => {
      toggle.addEventListener('click', () => {
        const nextTheme = doc.classList.contains('theme-dark') ? 'light' : 'dark';
        applyTheme(nextTheme);
      });
    });
  }

  if (!storedTheme && window.matchMedia) {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    const mediaHandler = (event) => {
      applyTheme(event.matches ? 'dark' : 'light', false);
    };

    if (typeof mediaQuery.addEventListener === 'function') {
      mediaQuery.addEventListener('change', mediaHandler);
    } else if (typeof mediaQuery.addListener === 'function') {
      mediaQuery.addListener(mediaHandler);
    }
  }

  const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const animateNodes = Array.from(document.querySelectorAll('[data-animate]'));

  if (!prefersReducedMotion && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const target = entry.target;
          target.classList.add('is-visible');
          observer.unobserve(target);
        }
      });
    }, {
      threshold: 0.18,
      rootMargin: '0px 0px -10%'
    });

    animateNodes.forEach((node, index) => {
      if (!node.dataset.animate) {
        node.dataset.animate = 'fade';
      }

      if (!node.style.getPropertyValue('--animate-stagger')) {
        const delay = (index % 6) * 0.06;
        node.style.setProperty('--animate-stagger', `${delay.toFixed(2)}s`);
      }

      observer.observe(node);
    });
  } else {
    animateNodes.forEach((node) => {
      if (!node.dataset.animate) {
        node.dataset.animate = 'fade';
      }
      node.classList.add('is-visible');
    });
  }

  const topbar = document.querySelector('.topbar');
  if (topbar) {
    const updateTopbar = () => {
      const isScrolled = window.scrollY > 24;
      topbar.classList.toggle('is-scrolled', isScrolled);
    };

    updateTopbar();
    window.addEventListener('scroll', updateTopbar, { passive: true });
  }
})();
  const toggleBtn = document.getElementById("dark-mode-toggle"); // your 🌙/☀️ button
  toggleBtn?.addEventListener("click", () => {
    document.body.classList.toggle("dark");

    // save preference to localStorage
    if (document.body.classList.contains("dark")) {
      localStorage.setItem("darkMode", "true");
    } else {
      localStorage.setItem("darkMode", "false");
    }
  });

  // apply saved mode on load
  if (localStorage.getItem("darkMode") === "true") {
    document.body.classList.add("dark");
  }

