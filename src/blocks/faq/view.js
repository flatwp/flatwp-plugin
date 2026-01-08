/**
 * FAQ Block - Frontend Interactivity
 * Pure CSS animation with JS for accordion behavior
 */

document.addEventListener('DOMContentLoaded', () => {
  const faqBlocks = document.querySelectorAll('.flatwp-faq');

  faqBlocks.forEach((faq) => {
    const allowMultiple = faq.dataset.allowMultiple === 'true';
    const items = faq.querySelectorAll('.flatwp-faq-item');

    items.forEach((item) => {
      const trigger = item.querySelector('.flatwp-faq-item__trigger');
      const content = item.querySelector('.flatwp-faq-item__content');
      const inner = item.querySelector('.flatwp-faq-item__answer');

      if (!trigger || !content || !inner) return;

      // Set initial height for closed items
      if (!item.classList.contains('is-open')) {
        content.style.height = '0px';
      }

      trigger.addEventListener('click', () => {
        const isOpen = item.classList.contains('is-open');

        // Close other items if not allowing multiple
        if (!allowMultiple && !isOpen) {
          items.forEach((otherItem) => {
            if (otherItem !== item && otherItem.classList.contains('is-open')) {
              const otherContent = otherItem.querySelector('.flatwp-faq-item__content');
              otherItem.classList.remove('is-open');
              otherContent.style.height = '0px';
            }
          });
        }

        // Toggle current item
        if (isOpen) {
          content.style.height = '0px';
          item.classList.remove('is-open');
        } else {
          content.style.height = inner.offsetHeight + 'px';
          item.classList.add('is-open');
        }
      });

      // Handle resize
      const resizeObserver = new ResizeObserver(() => {
        if (item.classList.contains('is-open')) {
          content.style.height = inner.offsetHeight + 'px';
        }
      });
      resizeObserver.observe(inner);
    });
  });
});
