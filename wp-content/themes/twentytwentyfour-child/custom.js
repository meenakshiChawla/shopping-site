console.log("Custom JS is loaded successfully!");
document.addEventListener("DOMContentLoaded", function () {
  const navItems = document.querySelectorAll('.wp-block-navigation .has-child');

  navItems.forEach(item => {
    const link = item.querySelector('a');
    const submenu = item.querySelector('ul');

    if (submenu && link) {
      link.setAttribute('aria-haspopup', 'true');
      link.setAttribute('aria-expanded', 'false');
      link.setAttribute('aria-controls', submenu.id || 'submenu-' + Math.random().toString(36).substr(2, 5));

      // Toggle submenu on Enter or ArrowDown
      link.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === 'ArrowDown') {
          e.preventDefault();
          const isExpanded = link.getAttribute('aria-expanded') === 'true';
          link.setAttribute('aria-expanded', !isExpanded);
          submenu.style.display = isExpanded ? 'none' : 'block';
          const firstLink = submenu.querySelector('a');
          if (firstLink) firstLink.focus();
        }
      });

      // Close submenu on Escape
      submenu.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          e.preventDefault();
          submenu.style.display = 'none';
          link.setAttribute('aria-expanded', 'false');
          link.focus();
        }
      });
    }
  });
});

