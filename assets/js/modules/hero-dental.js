(() => {
  const blockSelector = '.hj-hd-rating[data-rating-mode="dynamic"]';

  function normalizeText(text) {
    return (text || '').replace(/\s+/g, ' ').trim();
  }

  function extractReviewCount(text) {
    const normalized = normalizeText(text);
    if (!normalized) {
      return null;
    }

    const match = normalized.match(/(\d[\d.,\s]*)\s+reviews?/i);
    return match ? match[1].trim() : null;
  }

  function extractRatingLabel(text) {
    const normalized = normalizeText(text);
    if (!normalized) {
      return '';
    }

    const match = normalized.match(/([0-5](?:[.,]\d+)?)\s+stars?/i);
    return match ? `${match[1].replace(',', '.')} stars` : '';
  }

  function extractSummary(widget) {
    if (!widget) {
      return null;
    }

    const widgetText = normalizeText(widget.textContent);
    const ratingTextEl = widget.querySelector('.ti-header .ti-rating-text, .ti-header-rating-text, .ti-footer .ti-rating-text, .ti-popup-header .ti-rating-text');
    let ratingText = ratingTextEl ? normalizeText(ratingTextEl.textContent) : '';

    const reviewLink = widget.querySelector('.ti-header-rating-reviews a[href], .ti-header .ti-text a[href], .ti-footer a[href], .ti-header a[href], a[href*="review"], a[href*="google"]');
    const reviewTextEl = reviewLink || widget.querySelector('.ti-header-rating-reviews, .ti-header .ti-text span, .ti-header .nowrap strong, .ti-footer strong');
    const reviewsText = reviewTextEl ? normalizeText(reviewTextEl.textContent) : '';
    const reviewsCount = extractReviewCount(reviewsText) || extractReviewCount(widgetText);

    if (!ratingText) {
      ratingText = extractRatingLabel(widgetText);
    }

    let starsText = '';
    const fullStars = widget.querySelectorAll('.ti-stars .ti-star.f').length;
    const halfStars = widget.querySelectorAll('.ti-stars .ti-star.h').length;
    const roundedStars = Math.max(0, Math.min(5, fullStars + (halfStars ? 0.5 : 0)));

    if (roundedStars > 0) {
      starsText = '★'.repeat(Math.round(roundedStars));
    }

    if (!starsText && ratingText) {
      const starsMatch = ratingText.match(/([0-5](?:[.,]5)?)/);
      if (starsMatch) {
        starsText = '★'.repeat(Math.round(parseFloat(starsMatch[1].replace(',', '.'))));
      }
    }

    if (!starsText) {
      const starsMatch = widgetText.match(/([0-5](?:[.,]\d+)?)\s+stars?/i);
      if (starsMatch) {
        starsText = '★'.repeat(Math.round(parseFloat(starsMatch[1].replace(',', '.'))));
      }
    }

    if (!ratingText && !reviewsCount && !starsText) {
      return null;
    }

    return {
      starsText,
      labelText: ratingText,
      reviewsCount,
      reviewsHref: reviewLink ? reviewLink.href : ''
    };
  }

  function applySummary(block, summary) {
    const rowLinkEl = block.querySelector('.hj-hd-rating-link');
    const starsEl = block.querySelector('.stars');
    const labelEl = block.querySelector('.label');
    const metaEl = block.querySelector('.meta');
    const reviewsEl = block.querySelector('.hj-hd-rating-reviews');

    if (starsEl && summary.starsText) {
      starsEl.textContent = summary.starsText;
    }

    if (labelEl) {
      if (summary.labelText) {
        labelEl.textContent = `(${summary.labelText})`;
        labelEl.hidden = false;
      } else {
        labelEl.hidden = true;
      }
    }

    if (metaEl) {
      metaEl.hidden = !summary.reviewsCount;
    }

    if (rowLinkEl && summary.reviewsHref) {
      rowLinkEl.href = summary.reviewsHref;
    }

    if (reviewsEl) {
      if (summary.reviewsCount) {
        const reviewLabel = `${summary.reviewsCount} reviews`;
        reviewsEl.textContent = reviewLabel;

        reviewsEl.hidden = false;
      } else {
        reviewsEl.hidden = true;
      }
    }
  }

  function syncBlock(block) {
    const source = block.querySelector('.hj-hd-rating-source');
    if (!source) {
      return false;
    }

    const widget = source.querySelector('.ti-widget');
    if (!widget) {
      return false;
    }

    const summary = extractSummary(widget);
    if (!summary) {
      return false;
    }

    applySummary(block, summary);
    block.dataset.ratingLoaded = 'true';
    return true;
  }

  function watchBlock(block) {
    if (syncBlock(block)) {
      return;
    }

    const source = block.querySelector('.hj-hd-rating-source');
    if (!source) {
      return;
    }

    const observer = new MutationObserver(() => {
      if (syncBlock(block)) {
        observer.disconnect();
      }
    });

    observer.observe(source, { childList: true, subtree: true });

    let attempts = 0;
    const poll = window.setInterval(() => {
      attempts += 1;

      if (syncBlock(block) || attempts >= 40) {
        observer.disconnect();
        window.clearInterval(poll);
      }
    }, 250);
  }

  function init() {
    document.querySelectorAll(blockSelector).forEach(watchBlock);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }

  window.addEventListener('widget-ready', init);
})();